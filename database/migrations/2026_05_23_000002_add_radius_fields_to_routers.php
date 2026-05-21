<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $t) {
            $t->string('radius_shared_secret')->nullable();
            $t->string('public_ip')->nullable()->index()
              ->comment('IP that FreeRADIUS will see when this router sends auth/accounting');
        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $t) {
            $t->dropColumn(['radius_shared_secret', 'public_ip']);
        });
    }
};
