<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('subject');
            $table->text('body');
            $table->string('status')->default('open')->index();
            $table->string('priority')->default('normal');
            $table->string('category')->default('other')->index();   // billing/connectivity/equipment/other
            $table->string('channel')->default('portal');             // portal/email/phone/social/walkin
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->unsignedBigInteger('subscription_id')->nullable()->index();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
