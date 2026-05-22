<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('automation_rule_executions', function (Blueprint $t) {
            $t->string('batch_id', 32)->nullable()->after('rule_id')->index();
        });
    }
    public function down(): void
    {
        Schema::table('automation_rule_executions', function (Blueprint $t) {
            $t->dropColumn('batch_id');
        });
    }
};
