<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('automation_rules', function (Blueprint $t) {
            $t->json('target_filter')->nullable()->after('conditions');
            $t->unsignedBigInteger('batch_id')->nullable()->after('fire_count');
            // batch_id will group AutomationRuleExecution rows produced by a single scheduled run
        });
    }
    public function down(): void
    {
        Schema::table('automation_rules', function (Blueprint $t) {
            $t->dropColumn(['target_filter', 'batch_id']);
        });
    }
};
