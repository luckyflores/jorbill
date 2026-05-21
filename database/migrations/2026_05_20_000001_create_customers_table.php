<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->nullable()->unique();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->index();
            $table->string('alt_phone')->nullable();
            $table->string('address_line1');
            $table->string('barangay')->nullable();
            $table->string('city');
            $table->string('province');
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status')->default('prospect')->index();
            $table->string('tax_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
