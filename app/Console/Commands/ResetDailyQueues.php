<?php

namespace App\Console\Commands;

use App\Models\OrganizationSetting;
use App\Models\Queue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Resets queue sequences and cleans up stale queues for all (or a specific) organization.
 *
 * Runs automatically at midnight via the scheduler (see bootstrap/app.php).
 * Can also be run manually:
 *   php artisan queue:reset-daily              — resets all organizations
 *   php artisan queue:reset-daily --org=5      — resets org with ID 5 only
 */
class ResetDailyQueues extends Command
{
    protected $signature = 'queue:reset-daily
                            {--org= : Organization ID to reset (omit for all organizations)}
                            {--dry-run : Show what would be done without making any changes}';

    protected $description = 'Reset daily queue sequences and complete stale queues from the previous day';

    public function handle(): int
    {
        $dryRun   = (bool) $this->option('dry-run');
        $orgId    = $this->option('org') ? (int) $this->option('org') : null;
        $yesterday = now()->subDay()->toDateString();

        // --- 1. Expire stale active queues from previous days ---
        $staleStatuses = ['waiting', 'called', 'serving'];

        $staleQuery = Queue::whereIn('status', $staleStatuses)
            ->whereDate('created_at', '<=', $yesterday);

        if ($orgId !== null) {
            $staleQuery->where('organization_id', $orgId);
        }

        $staleCount = $staleQuery->count();

        if ($staleCount > 0) {
            $this->info("[Stale queues] Found {$staleCount} queue(s) from previous day(s) still active.");

            if (!$dryRun) {
                $staleQuery->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);
                $this->info("[Stale queues] Marked {$staleCount} queue(s) as completed.");
            } else {
                $this->warn("[DRY RUN] Would mark {$staleCount} queue(s) as completed.");
            }
        } else {
            $this->info('[Stale queues] No stale queues found.');
        }

        // --- 2. Reset sequence counters ---
        $settingsQuery = OrganizationSetting::query();
        if ($orgId !== null) {
            $settingsQuery->where('organization_id', $orgId);
        }

        $settings = $settingsQuery->get();

        if ($settings->isEmpty()) {
            $this->warn('No organization settings found to reset.');
            return self::SUCCESS;
        }

        foreach ($settings as $setting) {
            $label = $setting->organization_id
                ? "Org #{$setting->organization_id}"
                : 'Global';

            if (!$dryRun) {
                DB::table('organization_settings')
                    ->where('id', $setting->id)
                    ->update([
                        'last_queue_sequence' => 0,
                        'last_queue_date'     => null,
                    ]);
                $this->info("[{$label}] Sequence reset to 0. First ticket tomorrow will be 0001.");
            } else {
                $this->warn("[DRY RUN] [{$label}] Would reset sequence from {$setting->last_queue_sequence} to 0.");
            }
        }

        $this->info('Daily queue reset complete.');

        return self::SUCCESS;
    }
}
