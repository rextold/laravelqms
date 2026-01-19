<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastQueueSequenceDateToOrganizationSettings extends Migration
{
    public function up()
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('organization_settings', 'last_queue_sequence_date')) {
                $table->date('last_queue_sequence_date')->nullable()->after('last_queue_sequence');
            }
        });
    }

    public function down()
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            if (Schema::hasColumn('organization_settings', 'last_queue_sequence_date')) {
                $table->dropColumn('last_queue_sequence_date');
            }
        });
    }
}
