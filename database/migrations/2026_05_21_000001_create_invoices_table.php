<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('series_code', 8)->default('SI');
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('subscription_id')->nullable()->index();
            $table->date('issued_at');
            $table->date('due_at');
            $table->bigInteger('subtotal_centavos')->default(0);
            $table->bigInteger('vat_centavos')->default(0);
            $table->bigInteger('withholding_centavos')->default(0);
            $table->bigInteger('discount_centavos')->default(0);
            $table->bigInteger('total_centavos')->default(0);
            $table->bigInteger('amount_paid_centavos')->default(0);
            $table->string('status')->default('draft')->index();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('bir_atp_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
