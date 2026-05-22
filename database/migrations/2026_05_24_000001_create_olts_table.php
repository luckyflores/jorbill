<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('olts', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('vendor')->default('zte_cli')->index();  // zte_cli / huawei / vsol / bdcom
            $t->string('model')->nullable();                    // 'ZXA10 C300', 'C600', etc.
            $t->string('location')->nullable();
            $t->string('ip_address')->index();
            $t->integer('ssh_port')->default(22);
            $t->string('ssh_user');
            $t->string('ssh_password');                         // encrypted
            $t->string('enable_password')->nullable();          // encrypted
            $t->string('prompt_pattern')->default('[#>]');      // regex tail of prompt (varies on OEM)
            $t->string('save_command')->default('write');       // 'write' / 'save' / 'copy running-config startup-config'
            $t->boolean('is_active')->default(true)->index();
            $t->timestamp('last_seen_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olts');
    }
};
