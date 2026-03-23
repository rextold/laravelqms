<?php

namespace App\Console\Commands;

use App\Models\Queue;
use Illuminate\Console\Command;

/**
 * Deletes old completed/skipped queue records beyond the retention window.
 *
 * Runs automatically every Sunday at 02:00 via the scheduler (see bootstrap/app.php).
 * Can also be run manually:
 *   php artisan queue:prune                    — delete records older than 90 days (default)
 *   php artisan queue:prune --days=30          — delete records older than 30 days
 *   php artisan queue:prune --dry-run          — preview without deleting
 */
class PruneOldQueues extends Command
{
    protected $signature = 'queue:prune
                            {--days=90 : Delete completed/skipped records older than this many days}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Delete old completed and skipped queue records to keep the database lean';

    public function handle(): int
    {
        $days   = max(1, (int) $this->option('days'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days)->toDateString();

        $query = Queue::whereIn('status', ['completed', 'skipped'])
                      ->whereDate('created_at', '<=', $cutoff);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No old queue records to prune.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("[DRY RUN] Would delete {$count} queue record(s) older than {$days} days (before {$cutoff}).");
            return self::SUCCESS;
        }

        // Delete in chunks to avoid locking the table for too long
        $deleted = 0;
        do {
            $chunk = $query->limit(500)->delete();
            $deleted += $chunk;
        } while ($chunk > 0);

        $this->info("Pruned {$deleted} queue record(s) older than {$days} days (before {$cutoff}).");

        return self::SUCCESS;
    }
}
