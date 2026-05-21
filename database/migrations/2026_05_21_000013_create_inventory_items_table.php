<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->index();
            $table->string('name');
            $table->string('category')->index();           // router/onu/cable/connector/splitter/other
            $table->string('serial_number')->nullable()->unique();
            $table->integer('quantity')->default(1);
            $table->bigInteger('unit_cost_centavos')->nullable();
            $table->string('location')->nullable();        // warehouse/truck-1/customer-site
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->unsignedBigInteger('subscription_id')->nullable()->index();
            $table->string('status')->default('in_stock')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
