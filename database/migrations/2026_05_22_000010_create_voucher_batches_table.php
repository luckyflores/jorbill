<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('voucher_batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code_prefix', 8)->nullable();
            $table->integer('count');
            $table->bigInteger('value_centavos')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('service_id')->nullable()->index();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('voucher_batches'); }
};
