<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;

class RecalculateLeadScores extends Command
{
    protected $signature = 'leads:recalculate-scores';

    protected $description = 'Recalculate the score field for all existing leads';

    public function handle()
    {
        $count = 0;
        Lead::withTrashed()->chunkById(200, function ($leads) use (&$count) {
            foreach ($leads as $lead) {
                $lead->recalculateScore();
                $count++;
            }
        });

        $this->info("Recalculated scores for {$count} lead(s).");
        return self::SUCCESS;
    }
}
