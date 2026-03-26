<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateReportJob;
use App\Policies\ReportPolicy;
use App\Services\Reports\Filters\ReportFilter;
use App\Services\Reports\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $types = config('reports.types');
        $formats = config('reports.export_formats');

        // Role-specific accessible types from config (role → types mapping)
        $access = (array) config('reports.access');
        $role = app(\App\Policies\ReportPolicy::class)->resolveRole($user);
        $allowedKeys = (array) ($access[$role] ?? []);
        $allowed = collect($types)
            ->filter(fn ($info, $key) => in_array($key, $allowedKeys, true))
            ->all();

        // Suggest default date range (last 30 days)
        $defaultFrom = now()->subDays(30)->toDateString();
        $defaultTo = now()->toDateString();

        // Adviser-friendly: preselect their organizations
        $myOrganizations = [];
        $allOrganizations = collect();
        $advisers = collect();
        if ($role === 'adviser') {
            $myOrganizations = \App\Models\Organization::where('adviser_id', $user->id)
                ->orderBy('org_name')
                ->get(['org_id', 'org_name']);
        } else {
            // Admin/Staff: need dropdown with all organizations and advisers
            $allOrganizations = \App\Models\Organization::orderBy('org_name')
                ->get(['org_id', 'org_name']);
            $advisers = \App\Models\User::where('role', 'adviser')
                ->where('status', 'active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name']);
        }

        // Common lists for simple selects
        $services = \App\Models\Service::withTrashed()->orderBy('service_name')->get(['service_id', 'service_name']);

        return view('reports.index', [
            'types' => $allowed,
            'formats' => $formats,
            'defaultFrom' => $defaultFrom,
            'defaultTo' => $defaultTo,
            'role' => $role,
            'myOrganizations' => $myOrganizations,
            'allOrganizations' => $allOrganizations,
            'advisers' => $advisers,
            'services' => $services,
        ]);
    }

    public function generate(Request $request, ReportService $service)
    {
        $user = Auth::user();
        $type = (string) $request->input('type');
        $format = (string) $request->input('format', 'csv');
        $async = (bool) $request->boolean('async', false);

        // Authorization based on policy and config (direct invocation to avoid registration requirements)
        $policy = new ReportPolicy();
        if (! $policy->generate($user, $type)) {
            abort(403, 'You are not allowed to generate this report type.');
        }

        // Simple validation
        $request->validate([
            'type' => 'required|string',
            'format' => 'required|string|in:' . implode(',', config('reports.export_formats')),
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'organizations' => 'nullable',
            'services' => 'nullable',
            'adviser_id' => 'nullable|integer',
            'status' => 'nullable|string',
        ]);

        $filter = ReportFilter::fromRequest($request);

        // Decide sync vs async
        $estimate = $service->estimateRowCount($type, $filter);
        $threshold = (int) config('reports.async_threshold_rows');
        if ($async || $estimate >= $threshold) {
            // Record audit
            \App\Models\ReportRequest::create([
                'user_id' => $user->id,
                'type' => $type,
                'format' => $format,
                'filters' => $filter->toArray(),
                'status' => 'queued',
            ]);

            GenerateReportJob::dispatch($user->id, $type, $format, $filter->toArray());
            return back()->with('status', 'Generating report… You will be notified when it is ready.');
        }

        // Synchronous small report generation
        $result = $service->generate($type, $filter, $format, $user->id);
        \App\Models\ReportRequest::create([
            'user_id' => $user->id,
            'type' => $type,
            'format' => $format,
            'filters' => $filter->toArray(),
            'status' => $result->status,
            'file_path' => $result->path,
            'rows_count' => $result->rowsCount,
            'error' => $result->status === 'failed' ? ($result->message ?? 'failed') : null,
        ]);
        if ($result->status === 'empty') {
            return back()->with('status', 'No Data Found for the selected filters.');
        }
        if ($result->status === 'failed') {
            return back()->with('status', 'Failed: ' . ($result->message ?? 'Unknown error'));
        }

        // Stream or link to download
        return redirect()->route('reports.download', ['path' => $result->path]);
    }

    public function download(Request $request)
    {
        $path = (string) $request->query('path');
        $this->authorizeReportPath($path);
        abort_unless(Storage::exists($path), 404);
        return Storage::download($path);
    }

    public function view(Request $request)
    {
        $path = (string) $request->query('path');
        $this->authorizeReportPath($path);
        abort_unless(Storage::exists($path), 404);
        $content = Storage::get($path);
        $mime = Storage::mimeType($path) ?: 'application/octet-stream';
        return response($content)->header('Content-Type', $mime);
    }

    private function authorizeReportPath(string $path): void
    {
        $normalized = str_replace('\\', '/', trim($path));
        $userId = (int) Auth::id();
        $prefix = "reports/{$userId}/";

        abort_if($userId <= 0, 403);
        abort_if($normalized === '' || str_contains($normalized, '..'), 403);
        abort_unless(str_starts_with($normalized, $prefix), 403);
    }
}
