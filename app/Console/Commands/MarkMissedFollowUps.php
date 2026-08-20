<?php

namespace App\Console\Commands;

use App\Models\FollowUp;
use App\Notifications\FollowUpMissed;
use Illuminate\Console\Command;

class MarkMissedFollowUps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'followups:mark-missed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark pending follow-ups whose scheduled day has fully passed as missed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $followUps = FollowUp::with('lead', 'assignee')
            ->where('status', 'pending')
            ->where('follow_up_at', '<', now()->startOfDay())
            ->get();

        foreach ($followUps as $followUp) {
            $followUp->update(['status' => 'missed']);
            $followUp->lead?->logActivity(
                'follow_up_missed',
                'Follow-up missed — was due ' . $followUp->follow_up_at->format('d M Y, h:i A'),
                ['follow_up_id' => $followUp->id]
            );
            $followUp->assignee?->notify(new FollowUpMissed($followUp));
        }

        $this->info($followUps->count() . ' follow-up(s) marked as missed.');
        return self::SUCCESS;
    }
}
