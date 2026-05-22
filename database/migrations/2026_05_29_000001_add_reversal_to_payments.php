<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $t) {
            $t->unsignedBigInteger('reverses_payment_id')->nullable()->after('gateway_reference')->index();
            $t->timestamp('reversed_at')->nullable()->after('reverses_payment_id');
            $t->string('reversed_reason')->nullable()->after('reversed_at');
        });
    }
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $t) {
            $t->dropColumn(['reverses_payment_id', 'reversed_at', 'reversed_reason']);
        });
    }
};
