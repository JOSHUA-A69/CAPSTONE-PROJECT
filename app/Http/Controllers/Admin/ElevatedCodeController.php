<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ElevatedCodeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin']);
    }

    /**
     * Show the elevated code management page.
     */
    public function index()
    {
        $currentCode = SystemSetting::getElevatedCode();
        return view('admin.elevated-code.index', compact('currentCode'));
    }

    /**
     * Update the elevated registration code.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'elevated_code' => ['required', 'string', 'min:6', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'],
        ], [
            'elevated_code.required' => 'The elevated code is required.',
            'elevated_code.min' => 'The elevated code must be at least 6 characters.',
            'elevated_code.max' => 'The elevated code must not exceed 50 characters.',
            'elevated_code.regex' => 'The elevated code can only contain letters, numbers, underscores, and hyphens.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        SystemSetting::setElevatedCode($request->elevated_code);

        return back()->with('success', 'Elevated registration code updated successfully!');
    }
}
