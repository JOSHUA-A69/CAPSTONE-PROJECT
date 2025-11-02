<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class VenueController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin']);
    }

    public function index()
    {
        $venues = Venue::orderBy('name')->get();
        return view('admin.venues.index', compact('venues'));
    }

    public function create()
    {
        return view('admin.venues.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:venues,name',
        ]);
        Venue::create(['name' => $request->name]);
        return Redirect::route('admin.venues.index')->with('success', 'Venue added successfully!');
    }

    public function edit($venue_id)
    {
        $venue = Venue::findOrFail($venue_id);
        return view('admin.venues.edit', compact('venue'));
    }

    public function update(Request $request, $venue_id)
    {
        $venue = Venue::findOrFail($venue_id);
        $request->validate([
            'name' => 'required|string|max:255|unique:venues,name,' . $venue_id . ',venue_id',
        ]);
        $venue->update(['name' => $request->name]);
        return Redirect::route('admin.venues.index')->with('success', 'Venue updated successfully!');
    }

    public function destroy($venue_id)
    {
        $venue = Venue::findOrFail($venue_id);
        $venue->delete();
        return Redirect::route('admin.venues.index')->with('success', 'Venue deleted successfully!');
    }
}
