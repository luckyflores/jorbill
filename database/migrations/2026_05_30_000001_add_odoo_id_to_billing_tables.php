<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $t) {
            $t->unsignedInteger('odoo_id')->nullable()->unique()->after('agent_id');
            $t->timestamp('odoo_synced_at')->nullable()->after('odoo_id');
        });
        Schema::table('invoices', function (Blueprint $t) {
            $t->unsignedInteger('odoo_id')->nullable()->unique();
            $t->timestamp('odoo_synced_at')->nullable();
        });
        Schema::table('payments', function (Blueprint $t) {
            $t->unsignedInteger('odoo_id')->nullable()->unique();
            $t->timestamp('odoo_synced_at')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $t) { $t->dropColumn(['odoo_id', 'odoo_synced_at']); });
        Schema::table('invoices',  function (Blueprint $t) { $t->dropColumn(['odoo_id', 'odoo_synced_at']); });
        Schema::table('payments',  function (Blueprint $t) { $t->dropColumn(['odoo_id', 'odoo_synced_at']); });
    }
};
