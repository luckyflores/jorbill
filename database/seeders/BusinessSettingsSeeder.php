<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class BusinessSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'business.company_name',  'value' => 'JorBill ISP',          'description' => 'Legal/operating name used on invoices'],
            ['key' => 'business.tin',           'value' => '',                     'description' => 'BIR Tax Identification Number'],
            ['key' => 'business.address',       'value' => '',                     'description' => 'Registered business address (appears on OR/SI)'],
            ['key' => 'business.vat_registered','value' => '1',                    'description' => '1 if VAT-registered (12% breakout shown on invoices), 0 if not'],
            ['key' => 'business.vat_rate',      'value' => '12.0',                 'description' => 'VAT % (PH default is 12.0)'],
            ['key' => 'business.currency',      'value' => 'PHP',                  'description' => 'ISO currency code'],
            ['key' => 'business.email',         'value' => '',                     'description' => 'Public contact email shown on invoices'],
            ['key' => 'business.phone',         'value' => '',                     'description' => 'Public contact phone shown on invoices'],
        ];
        foreach ($defaults as $row) {
            Setting::updateOrCreate(['key' => $row['key']],
                ['value' => $row['value'], 'description' => $row['description']]);
        }
        $this->command->info('  ✓ 8 business settings seeded');
    }
}
