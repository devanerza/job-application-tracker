<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Inertia\Inertia;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $applications = Application::where('user_id', auth()->id())
        ->filterSort($request->all()) 
        ->paginate(10)
        ->withQueryString();

        $currentTime = Carbon::now();
        $dateOnly = $currentTime->toDateString();
        foreach ($applications as $application) {
            if ($dateOnly > $application->follow_up_at) {
                $application->follow_up_at = null;
                $application->reminder_sent = false;
            }
            $application->save();
        };

        return Inertia::render('Applications/Index', [
            'applications' => $applications,
            'filters' => $request->only(['search', 'status', 'sort', 'direction']) // Pass filters back to keep UI state
        ]);
    }

    public function create()
    {
        return view('applications.create'); //CHANGE TO INERTIA
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
            'job_url' => 'nullable|active_url|max:255',
            'status' => 'required|in:applied,screening,interviewing,offer,rejected,ghosted',
            'applied_at' => 'required|date',
        ], [
            'job_url.active_url' => 'invalid or inactive URL'
        ]);

        $application = new Application($validated);
        $application->user_id = auth()->id();
        $application->last_activity_at = now();
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
            'job_url' => 'nullable|active_url|max:255',
            'status' => 'required|in:applied,screening,interviewing,offer,rejected,ghosted',
            'applied_at' => 'required|date',
        ],[
            'job_url.active_url' => 'invalid or inactive URL'
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

        if ($application->status == 'offer' || $application->status == 'rejected' || $application->status == 'ghosted') {
            $application->follow_up_at = null;
        }

        $application->save();

        return redirect()->route('applications.index')->with('success', 'Application status updated successfully.');
    }
    
    public function followUp(Request $request, Application $application)
    {
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }


        $validated = $request->validate([
            'follow_up_at' => 'nullable|date'
        ]);

        $followUpAt = Carbon::parse($validated['follow_up_at']);
        $present = Carbon::now();
        $dateOnly = $present->toDateString();

        if ($followUpAt < $dateOnly) {
            throw ValidationException::withMessages([
                'invalid_follow_up_date' => 'follow up must be in the future!'
            ])->errorBag("application_{$application->id}");
        }

        $application->follow_up_at = $validated['follow_up_at'];
        $application->last_activity_at = now();

        if ($application->status == 'offer' || $application->status == 'rejected' || $application->status == 'ghosted') {
            $application->follow_up_at = null;
        }
        

        $application->save();

        // dd($application->status);

        return redirect()->route('applications.index')->with('success', 'Application follow up updated successfully.');
    }
}
