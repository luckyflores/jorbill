<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('piso_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('amount_centavos');
            $table->integer('duration_minutes');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('piso_rates'); }
};
