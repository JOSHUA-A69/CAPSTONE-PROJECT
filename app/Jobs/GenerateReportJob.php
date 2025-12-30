<?php

namespace App\Jobs;

use App\Notifications\ReportEmpty;
use App\Notifications\ReportFailed;
use App\Notifications\ReportReady;
use App\Services\Reports\Filters\ReportFilter;
use App\Services\Reports\ReportService;
use App\Models\ReportRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $userId,
        public string $type,
        public string $format,
        public array $filters,
    ) {
        $this->onQueue('reports');
    }

    public function handle(ReportService $service): void
    {
        $user = User::find($this->userId);
        if (!$user) {
            Log::warning('GenerateReportJob: user not found', ['userId' => $this->userId]);
            return;
        }

        $result = $service->generate($this->type, new ReportFilter(
            $this->filters['date_from'] ?? null,
            $this->filters['date_to'] ?? null,
            $this->filters['organizations'] ?? null,
            $this->filters['services'] ?? null,
            $this->filters['adviser_id'] ?? null,
            $this->filters['status'] ?? null,
        ), $this->format, $this->userId);

        // Update audit record if exists
        $audit = ReportRequest::where('user_id', $this->userId)
            ->where('type', $this->type)
            ->where('format', $this->format)
            ->where('status', 'queued')
            ->latest('id')
            ->first();
        if ($audit) {
            $audit->status = $result->status;
            $audit->file_path = $result->path;
            $audit->rows_count = $result->rowsCount;
            $audit->error = $result->status === 'failed' ? ($result->message ?? 'failed') : null;
            $audit->save();
        }

        match ($result->status) {
            'ready' => $user->notify(new ReportReady($result)),
            'empty' => $user->notify(new ReportEmpty($result)),
            'failed' => $user->notify(new ReportFailed($result->message ?? 'Report generation failed')),
            default => null,
        };
    }
}
