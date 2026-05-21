<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->bigInteger('amount_centavos');
            $table->string('gateway')->index();
            $table->string('gateway_reference')->nullable()->index();
            $table->timestamp('received_at');
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
