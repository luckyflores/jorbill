<x-filament-panels::page>
    <div class="space-y-4 text-sm">
        <div class="rounded-lg border border-gray-200 dark:border-white/10 p-4">
            <h3 class="font-semibold mb-2">Current configuration (from .env)</h3>
            <dl class="grid grid-cols-2 gap-y-1">
                <dt class="text-gray-500">Driver</dt>            <dd class="font-mono">{{ config('odoo.driver') }}</dd>
                <dt class="text-gray-500">Base URL</dt>          <dd class="font-mono">{{ config('odoo.base_url') }}</dd>
                <dt class="text-gray-500">Database</dt>          <dd class="font-mono">{{ config('odoo.db') }}</dd>
                <dt class="text-gray-500">Login</dt>             <dd class="font-mono">{{ config('odoo.login') }}</dd>
                <dt class="text-gray-500">Password</dt>          <dd class="font-mono">{{ config('odoo.password') ? str_repeat('•', strlen(config('odoo.password'))) : '(empty)' }}</dd>
            </dl>
        </div>

        <div class="rounded-lg border border-amber-300/50 bg-amber-50/30 dark:bg-amber-900/10 p-4">
            <h3 class="font-semibold mb-2">To enable Odoo push:</h3>
            <ol class="list-decimal list-inside space-y-1 text-gray-700 dark:text-gray-300">
                <li>SSH into the VM: <code>ssh -L 8069:127.0.0.1:8069 jorbill</code></li>
                <li>Open <a href="http://127.0.0.1:8069/web/login" target="_blank" class="text-blue-600">http://127.0.0.1:8069/web/login</a> in your browser</li>
                <li>Log in: <code>admin</code> / <code>admin</code> · <strong>change this password immediately</strong></li>
                <li>(Optional) Install the PH localization: <em>Apps → search "philippines" → install l10n_ph</em></li>
                <li>Add to <code>~/jorbill/.env</code> on the VM:
                    <pre class="bg-black/40 text-white p-2 mt-1 rounded text-xs">ODOO_DRIVER=live
ODOO_URL=http://127.0.0.1:8069
ODOO_DB=jorbill_accounting
ODOO_USER=admin
ODOO_PASSWORD=&lt;your new password&gt;</pre>
                </li>
                <li>Restart <code>php artisan serve</code> and come back here — click <strong>Test connection</strong>.</li>
            </ol>
        </div>

        <div class="text-gray-500">
            Once connected, the <strong>Customer</strong> table gains a "Push to Odoo" action that creates/updates the matching <code>res.partner</code> record by <code>ref = customer_code</code>.
        </div>
    </div>
</x-filament-panels::page>
