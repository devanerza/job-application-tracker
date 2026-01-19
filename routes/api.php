<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

Route::get('/test', function() {
    return "API route Read successfully";
});

Route::get('/cron/reminders', function (Request $request) {
    // 1. Security Check: Compare the 'key' from the URL to your .env secret
    // Usage: /api/cron/reminders?key=YOUR_SECRET_KEY
    if ($request->query('key') !== config('app.cron_secret')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // 2. Trigger the Artisan Command you created
    Artisan::call('reminders:send');

    // 3. Return a success response so cron-job.org knows it worked
    return response()->json([
        'status' => 'success',
        'message' => 'Reminders command executed.'
    ]);
});