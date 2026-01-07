<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::where('user_id', auth()->id())->latest()->get();
        return view('applications.index', compact('applications'));
    }

    public function create()
    {
        return view('applications.create');
    }

    public function edit($id)
    {
        $application = Application::findOrFail($id);
        return view('applications.edit', compact('application'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'role_title' => 'required|string|max:255',
            'job_url' => 'nullable|url|max:255',
            'status' => 'required|in:applied,screening,interviewing,offer,rejected,ghosted',
            'applied_at' => 'required|date',
        ]);

        $application = new Application($validated);
        $application->user_id = auth()->id();
        $application->last_activity_at = $validated['applied_at'];
        $application->save();

        return redirect()->route('applications.index')->with('success', 'Application created successfully.');
    }

    public function destroy(Application $application)
    {
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        $application->delete();

        return redirect()->route('applications.index')->with('success', 'Application deleted successfully.');
    }

    public function update(Request $request, Application $application)
    {
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'role_title' => 'required|string|max:255',
            'job_url' => 'nullable|url|max:255',
            'status' => 'required|in:applied,screening,interviewing,offer,rejected,ghosted',
            'applied_at' => 'required|date',
        ]);

        $application->update($validated);
        $application->last_activity_at = $validated['applied_at'];
        $application->save();

        return redirect()->route('applications.index')->with('success', 'Application updated successfully.');
    }
    
    public function statusUpdate(Request $request, Application $application)
    {
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:applied,screening,interviewing,offer,rejected,ghosted',
        ]);

        $application->status = $validated['status'];
        $application->last_activity_at = now();
        $application->save();

        return redirect()->route('applications.index')->with('success', 'Application status updated successfully.');
    }
}
