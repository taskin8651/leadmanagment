<?php

namespace App\Console\Commands;

use App\Models\FollowUp;
use App\Models\User;
use App\Notifications\DailyCallSummary;
use Illuminate\Console\Command;

class SendDailyCallSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'followups:daily-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Notify each assignee with pending follow-ups due today (their day's call list)";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $counts = FollowUp::query()
            ->whereDate('follow_up_at', today())
            ->where('status', 'pending')
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, COUNT(*) as cnt')
            ->groupBy('assigned_to')
            ->pluck('cnt', 'assigned_to');

        $users = User::where('is_active', true)->whereIn('id', $counts->keys())->get();

        foreach ($users as $user) {
            $user->notify(new DailyCallSummary((int) $counts[$user->id]));
        }

        $this->info($users->count() . ' daily call summary notification(s) sent.');
        return self::SUCCESS;
    }
}
