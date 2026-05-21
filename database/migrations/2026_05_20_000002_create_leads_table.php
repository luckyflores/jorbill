<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->index();
            $table->text('address')->nullable();
            $table->string('source')->default('other')->index();
            $table->string('status')->default('new')->index();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('converted_customer_id')->nullable()->index();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
