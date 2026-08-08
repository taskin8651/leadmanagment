<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Notifications\LeadSlaBreached;
use Illuminate\Console\Command;

class AlertSlaBreaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:sla-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify assignees (and the client owner) about leads with no response for over 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $leads = Lead::with('assignee', 'client.user')
            ->whereNull('first_response_at')
            ->whereNull('sla_alerted_at')
            ->where('created_at', '<=', now()->subHours(24))
            ->whereNotIn('status', ['won', 'lost'])
            ->get();

        foreach ($leads as $lead) {
            $notified = [];
            if ($lead->assignee) {
                $lead->assignee->notify(new LeadSlaBreached($lead));
                $notified[] = $lead->assignee->id;
            }
            $owner = $lead->client?->user;
            if ($owner && !in_array($owner->id, $notified, true)) {
                $owner->notify(new LeadSlaBreached($lead));
            }
            $lead->update(['sla_alerted_at' => now()]);
        }

        $this->info($leads->count() . ' SLA breach alert(s) sent.');
        return self::SUCCESS;
    }
}
