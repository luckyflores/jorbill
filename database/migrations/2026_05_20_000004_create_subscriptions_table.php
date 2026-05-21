<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('service_id')->index();
            $table->unsignedBigInteger('router_id')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('mac_address')->nullable()->index();
            $table->string('ip_address')->nullable();
            $table->bigInteger('price_centavos_override')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->date('next_billing_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
