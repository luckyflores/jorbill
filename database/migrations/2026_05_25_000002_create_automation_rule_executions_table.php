<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('automation_rule_executions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('rule_id')->index();
            $t->timestamp('fired_at')->index();
            $t->string('trigger_summary')->nullable();      // human-readable "Subscription #17 updated"
            $t->json('trigger_payload')->nullable();
            $t->boolean('conditions_matched')->default(false)->index();
            $t->json('actions_executed')->nullable();        // [{type, ok, error?}]
            $t->unsignedInteger('duration_ms')->default(0);
            $t->text('error')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('automation_rule_executions'); }
};
