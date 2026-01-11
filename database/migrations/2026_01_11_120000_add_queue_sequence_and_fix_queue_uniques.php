<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Ensure queues has organization_id
        if (Schema::hasTable('queues') && !Schema::hasColumn('queues', 'organization_id')) {
            Schema::table('queues', function (Blueprint $table) {
                $table->foreignId('organization_id')->nullable()->after('counter_id');
            });

            // Backfill organization_id from counter user
            DB::statement('UPDATE queues q JOIN users u ON u.id = q.counter_id SET q.organization_id = u.organization_id WHERE q.organization_id IS NULL');

            // Add FK if organizations exists
            if (Schema::hasTable('organizations')) {
                Schema::table('queues', function (Blueprint $table) {
                    try {
                        $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
                    } catch (Throwable $e) {
                        // ignore if already exists or unsupported
                    }
                });
            }
        }

        // 2) Add per-organization last_queue_sequence
        if (Schema::hasTable('organization_settings') && !Schema::hasColumn('organization_settings', 'last_queue_sequence')) {
            Schema::table('organization_settings', function (Blueprint $table) {
                $table->unsignedBigInteger('last_queue_sequence')->default(0)->after('queue_number_digits');
            });
        }

        // 3) Fix uniqueness: drop global unique(queue_number), add unique(organization_id, queue_number)
        if (Schema::hasTable('queues') && Schema::hasColumn('queues', 'queue_number')) {
            // Drop common Laravel-generated unique index name if present
            try {
                Schema::table('queues', function (Blueprint $table) {
                    $table->dropUnique('queues_queue_number_unique');
                });
            } catch (Throwable $e) {
                // ignore if not present
            }

            if (Schema::hasColumn('queues', 'organization_id')) {
                try {
                    Schema::table('queues', function (Blueprint $table) {
                        $table->unique(['organization_id', 'queue_number'], 'queues_org_queue_number_unique');
                    });
                } catch (Throwable $e) {
                    // ignore if already exists
                }
            }
        }
    }

    public function down(): void
    {
        // Best-effort rollback.
        if (Schema::hasTable('queues')) {
            try {
                Schema::table('queues', function (Blueprint $table) {
                    $table->dropUnique('queues_org_queue_number_unique');
                });
            } catch (Throwable $e) {
                // ignore
            }

            try {
                Schema::table('queues', function (Blueprint $table) {
                    $table->unique('queue_number');
                });
            } catch (Throwable $e) {
                // ignore
            }

            // Keep organization_id column to avoid destructive rollback.
        }

        if (Schema::hasTable('organization_settings') && Schema::hasColumn('organization_settings', 'last_queue_sequence')) {
            Schema::table('organization_settings', function (Blueprint $table) {
                $table->dropColumn('last_queue_sequence');
            });
        }
    }
};
