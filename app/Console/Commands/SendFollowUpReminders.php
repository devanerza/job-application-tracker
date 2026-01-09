<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\Application;
use Illuminate\Support\Facades\Mail;
use App\Mail\FollowUpReminder;

class SendFollowUpReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applications:send-followups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send follow-up reminder emails for applications due today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $applications = Application::query()
            ->whereDate('follow_up_at', $today)
            ->whereNotIn('status', ['rejected', 'offer'])
            ->get();

        if ($applications->isEmpty()) {
            $this->info('No follow-up reminders to send today.');
            return Command::SUCCESS;
        }

        foreach ($applications as $application) {
            // 1. Send reminder email
            Mail::to($application->user->email)
                ->send(new FollowUpReminder($application));

            // 2. Update state (DO NOT skip this later)
            $application->update([
                'last_activity_at' => now(),
            ]);
        }

        $this->info("Sent {$applications->count()} follow-up reminder(s).");

        return Command::SUCCESS;
    }
}
