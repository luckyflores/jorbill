<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $t) {
            $t->string('password')->nullable();
            $t->string('remember_token', 100)->nullable();
            $t->timestamp('email_verified_at')->nullable();
            $t->boolean('portal_enabled')->default(false)->index();
            $t->timestamp('last_login_at')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $t) {
            $t->dropColumn(['password', 'remember_token', 'email_verified_at', 'portal_enabled', 'last_login_at']);
        });
    }
};
