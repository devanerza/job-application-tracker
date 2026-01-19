<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;


// A basic test route that uses no external classes
Route::get('/test-connection', function () {
    return response()->json(['status' => 'Laravel is alive on Vercel']);
});

// Your Cron Route
Route::get('/cron/reminders', function (Request $request) {
    $secret = env('CRON_SECRET');
    
    if (!$request->has('key') || $request->query('key') !== $secret) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    try {
        Artisan::call('reminders:send');
        return response()->json([
            'status' => 'success',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});