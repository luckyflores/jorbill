<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notifications_log', function (Blueprint $t) {
            $t->timestamp('delivered_at')->nullable()->after('sent_at');
            $t->string('provider_status')->nullable()->after('delivered_at')->index();   // provider's own status string
            $t->string('provider_error_code')->nullable()->after('provider_status');
        });
    }
    public function down(): void
    {
        Schema::table('notifications_log', function (Blueprint $t) {
            $t->dropColumn(['delivered_at', 'provider_status', 'provider_error_code']);
        });
    }
};
