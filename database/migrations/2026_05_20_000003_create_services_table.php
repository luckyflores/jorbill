<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique();
            $table->string('type')->index();
            $table->integer('bandwidth_down_kbps');
            $table->integer('bandwidth_up_kbps');
            $table->bigInteger('price_centavos');
            $table->boolean('vat_inclusive')->default(true);
            $table->string('billing_cycle')->default('monthly');
            $table->integer('prepaid_days')->nullable();
            $table->string('mikrotik_profile_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
