<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DebugController extends Controller
{
    /**
     * Show notification debug page
     */
    public function notifications()
    {
        return view('debug.notifications');
    }
}
