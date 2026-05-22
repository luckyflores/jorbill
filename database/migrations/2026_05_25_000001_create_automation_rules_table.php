<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->text('description')->nullable();
            $t->boolean('is_enabled')->default(true)->index();
            $t->string('trigger_type')->default('model')->index();    // 'model' for Phase A
            $t->json('trigger_config');                                // {model, when, if_changed}
            $t->json('conditions')->nullable();                        // [{field, operator, value}]
            $t->json('actions');                                       // [{type, params...}]
            $t->timestamp('last_fired_at')->nullable();
            $t->unsignedBigInteger('fire_count')->default(0);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('automation_rules'); }
};
