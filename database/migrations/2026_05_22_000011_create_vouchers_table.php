<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->unsignedBigInteger('batch_id')->nullable()->index();
            $table->bigInteger('value_centavos')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('unused')->index();
            $table->unsignedBigInteger('used_by_customer_id')->nullable()->index();
            $table->unsignedBigInteger('used_by_subscription_id')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('vouchers'); }
};
