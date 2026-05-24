<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_diagnostics', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('customer_id')->nullable()->index();
            $t->unsignedBigInteger('tech_user_id')->nullable()->index();
            $t->timestamp('ran_at')->index();
            $t->string('public_ip', 45)->nullable();
            $t->json('wifi')->nullable();
            $t->json('ping_results')->nullable();
            $t->json('speedtest')->nullable();
            $t->text('notes')->nullable();
            $t->decimal('gps_lat', 10, 7)->nullable();
            $t->decimal('gps_lng', 10, 7)->nullable();
            $t->string('photo_path', 255)->nullable();
            $t->string('app_version', 50)->nullable();
            $t->json('device_info')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('customer_diagnostics'); }
};
