<?php

namespace App\Console\Commands;

use App\Models\GpsLog;
use Illuminate\Console\Command;

class CleanupOldGpsLogs extends Command
{
    protected $signature = 'gps:cleanup {--days=90 : Number of days to keep}';
    protected $description = 'Clean up old GPS logs older than specified days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = GpsLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} GPS log(s) older than {$days} days.");
        return Command::SUCCESS;
    }
}
