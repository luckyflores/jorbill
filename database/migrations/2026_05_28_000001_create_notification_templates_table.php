<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();      // slug-ish: payment_received, subscription_suspended
            $t->string('label')->nullable();   // human-readable: "Payment received"
            $t->text('description')->nullable();
            $t->string('channel')->default('sms')->index();  // sms / email / whatsapp
            $t->string('subject')->nullable();               // for email
            $t->text('body');
            $t->boolean('is_active')->default(true)->index();
            $t->unsignedBigInteger('use_count')->default(0);
            $t->timestamp('last_used_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('notification_templates'); }
};
