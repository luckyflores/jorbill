<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // FreeRADIUS needs plaintext for Cleartext-Password attribute (PAP auth).
        // Decrypt any app-encrypted values in-place.
        $rows = DB::table('subscriptions')->whereNotNull('password')->get(['id', 'password']);
        $decoded = 0;
        foreach ($rows as $row) {
            try {
                $plain = Crypt::decryptString($row->password);
                DB::table('subscriptions')->where('id', $row->id)->update(['password' => $plain]);
                $decoded++;
            } catch (\Throwable $e) {
                // already plaintext — skip
            }
        }
        echo "  decrypted {$decoded} subscription passwords for RADIUS\n";
    }

    public function down(): void
    {
        $rows = DB::table('subscriptions')->whereNotNull('password')->get(['id', 'password']);
        foreach ($rows as $row) {
            DB::table('subscriptions')->where('id', $row->id)->update([
                'password' => Crypt::encryptString($row->password),
            ]);
        }
    }
};
