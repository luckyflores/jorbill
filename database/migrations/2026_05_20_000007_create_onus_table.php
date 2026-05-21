<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('onus', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique();
            $table->string('vendor')->index();
            $table->string('model_name')->nullable();
            $table->string('mac_address')->nullable()->index();
            $table->unsignedBigInteger('subscription_id')->nullable()->index();
            $table->unsignedBigInteger('nap_id')->nullable()->index();
            $table->integer('nap_port')->nullable();
            $table->decimal('rx_power_dbm', 5, 2)->nullable();
            $table->decimal('tx_power_dbm', 5, 2)->nullable();
            $table->string('status')->default('in_stock')->index();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onus');
    }
};
