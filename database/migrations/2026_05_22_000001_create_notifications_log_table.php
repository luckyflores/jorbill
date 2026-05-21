<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications_log', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->index();
            $table->string('driver')->index();
            $table->string('to')->index();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('event')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('status')->default('queued')->index();
            $table->string('gateway_reference')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('notifications_log'); }
};
