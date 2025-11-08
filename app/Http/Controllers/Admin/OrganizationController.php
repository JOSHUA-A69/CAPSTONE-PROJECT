<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Support\Facades\Schema;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin']);
    }

    // Read-only list of organizations with adviser
    public function index()
    {
        if (Schema::hasColumn('organizations', 'deleted_at')) {
            $organizations = Organization::with('adviser')
                ->whereNull('deleted_at')
                ->orderBy('org_name')
                ->paginate(20);
        } else {
            $organizations = Organization::with('adviser')
                ->orderBy('org_name')
                ->paginate(20);
        }

        return view('admin.organizations.index', compact('organizations'));
    }
}
