<?php

namespace App\Console\Commands;

use App\Models\FollowUp;
use App\Notifications\FollowUpReminder;
use Illuminate\Console\Command;

class RemindFollowUps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'followups:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify assignees about follow-ups due within the next 30 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $followUps = FollowUp::with('lead', 'assignee', 'lead.creator')
            ->where('status', 'pending')
            ->whereNull('reminded_at')
            ->whereBetween('follow_up_at', [now(), now()->addMinutes(30)])
            ->get();

        foreach ($followUps as $followUp) {
            $notifiable = $followUp->assignee ?: $followUp->lead?->creator;
            if ($notifiable) {
                $notifiable->notify(new FollowUpReminder($followUp));
            }
            $followUp->update(['reminded_at' => now()]);
        }

        $this->info($followUps->count() . ' follow-up reminder(s) sent.');
        return self::SUCCESS;
    }
}
