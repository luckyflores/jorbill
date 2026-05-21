<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\JobOrder;
use App\Models\Nap;
use App\Models\Onu;
use App\Models\Payment;
use App\Models\Router;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Ticket;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        config(['activitylog.enabled' => false]);
        $faker = Faker::create();

        foreach ([
            'payments', 'invoice_line_items', 'invoices', 'subscriptions',
            'onus', 'naps', 'routers', 'customers', 'services',
            'agents', 'tickets', 'job_orders', 'inventory_items', 'bir_counters',
        ] as $t) {
            DB::statement("TRUNCATE TABLE {$t} RESTART IDENTITY CASCADE");
        }

        echo "  → 5 services\n";
        $services = collect([
            ['name'=>'Fiber 25M','code'=>'F25','type'=>'pppoe','bandwidth_down_kbps'=>25000,'bandwidth_up_kbps'=>10000,'price_centavos'=>69900,'mikrotik_profile_name'=>'fiber-25m'],
            ['name'=>'Fiber 50M','code'=>'F50','type'=>'pppoe','bandwidth_down_kbps'=>50000,'bandwidth_up_kbps'=>25000,'price_centavos'=>99900,'mikrotik_profile_name'=>'fiber-50m'],
            ['name'=>'Fiber 100M','code'=>'F100','type'=>'pppoe','bandwidth_down_kbps'=>100000,'bandwidth_up_kbps'=>50000,'price_centavos'=>149900,'mikrotik_profile_name'=>'fiber-100m'],
            ['name'=>'Fiber 200M','code'=>'F200','type'=>'pppoe','bandwidth_down_kbps'=>200000,'bandwidth_up_kbps'=>100000,'price_centavos'=>249900,'mikrotik_profile_name'=>'fiber-200m'],
            ['name'=>'Hotspot 1day','code'=>'HOT1D','type'=>'hotspot','bandwidth_down_kbps'=>5000,'bandwidth_up_kbps'=>2000,'price_centavos'=>2500,'billing_cycle'=>'prepaid_days','prepaid_days'=>1],
        ])->map(fn($d) => Service::create(array_merge($d, [
            'slug' => Str::slug($d['name']),
            'vat_inclusive' => true,
            'billing_cycle' => $d['billing_cycle'] ?? 'monthly',
            'is_active' => true,
        ])));

        echo "  → 5 routers\n";
        $routers = collect(range(1,5))->map(fn($i) => Router::create([
            'name' => "Mikrotik-PoP-{$i}",
            'location' => $faker->randomElement(['HQ','Branch North','Branch South','Branch East','Branch West']),
            'vendor' => 'mikrotik',
            'model' => $faker->randomElement(['CCR1009','CCR2004','RB4011','hAP ac²']),
            'ip_address' => "10.10.{$i}.1",
            'api_user' => 'admin',
            'api_password' => 'placeholder',
            'is_active' => true,
            'last_seen_at' => now()->subMinutes(mt_rand(1,60)),
        ]));

        echo "  → 12 NAPs\n";
        $naps = collect(range(1,12))->map(fn($i) => Nap::create([
            'name' => "NAP-{$i}",
            'code' => sprintf('NAP-%03d', $i),
            'type' => $faker->randomElement(['splitter','splitter','splitter','cabinet','pole']),
            'capacity' => $faker->randomElement([8,16,32]),
            'ports_used' => 0,
            'latitude'  => 14.5 + (mt_rand(-2000,2000)/10000),
            'longitude' => 121.0 + (mt_rand(-2000,2000)/10000),
            'address' => $faker->streetAddress() . ', ' . $faker->city(),
        ]));

        echo "  → 50 ONUs\n";
        collect(range(1,50))->each(fn($i) => Onu::create([
            'serial_number' => 'SN-' . strtoupper(Str::random(8)),
            'vendor' => $faker->randomElement(['huawei','vsol','bdcom','zte']),
            'model_name' => $faker->randomElement(['EG8145V5','V2802R','XPON-ONU-103','F660']),
            'mac_address' => $faker->macAddress(),
            'nap_id' => $naps->random()->id,
            'nap_port' => mt_rand(1,16),
            'status' => $faker->randomElement(['in_stock','installed','installed','installed','installed','faulty']),
            'rx_power_dbm' => -1 * (mt_rand(150, 270) / 10),
            'tx_power_dbm' => mt_rand(20, 35) / 10,
            'last_seen_at' => now()->subMinutes(mt_rand(0, 1440)),
        ]));

        echo "  → 30 customers\n";
        $cities = ['Quezon City','Makati','Manila','Pasig','Taguig','Mandaluyong','Cebu City','Davao'];
        $statuses = ['active','active','active','active','active','active','prospect','prospect','suspended'];
        $customers = collect(range(1,30))->map(fn($i) => Customer::create([
            'customer_code' => sprintf('C-%05d', $i),
            'name' => $faker->name(),
            'email' => $faker->safeEmail(),
            'phone' => '09' . $faker->numerify('#########'),
            'address_line1' => $faker->streetAddress(),
            'barangay' => $faker->randomElement(['Brgy. 1','Brgy. San Antonio','Brgy. Bel-Air','Brgy. Poblacion']),
            'city' => $faker->randomElement($cities),
            'province' => 'Metro Manila',
            'status' => $faker->randomElement($statuses),
            'latitude'  => 14.5 + (mt_rand(-3000,3000)/10000),
            'longitude' => 121.0 + (mt_rand(-3000,3000)/10000),
            'activated_at' => now()->subDays(mt_rand(7,365)),
        ]));

        echo "  → ~50 subscriptions\n";
        $subs = collect();
        foreach ($customers as $customer) {
            if ($customer->status === 'prospect') continue;
            $count = $customer->status === 'suspended' ? 1 : mt_rand(1,2);
            for ($j = 0; $j < $count; $j++) {
                $service = $services->where('type','pppoe')->random();
                $subs->push(Subscription::create([
                    'customer_id' => $customer->id,
                    'service_id' => $service->id,
                    'router_id' => $routers->random()->id,
                    'status' => $customer->status === 'suspended' ? 'suspended' : 'active',
                    'username' => strtolower($faker->userName()) . mt_rand(10,99),
                    'password' => Str::random(12),
                    'ip_address' => '10.20.' . mt_rand(1,20) . '.' . mt_rand(2,254),
                    'activated_at' => now()->subDays(mt_rand(30,365)),
                    'next_billing_date' => now()->addDays(mt_rand(-5,30)),
                ]));
            }
        }
        echo "    actually " . $subs->count() . " subscriptions\n";

        echo "  → invoices + line items + payments (4 months)\n";
        $invN = 1; $payN = 1;
        foreach ($subs as $sub) {
            $service = $services->firstWhere('id', $sub->service_id);
            for ($m = 0; $m < 4; $m++) {
                $issued = now()->subMonths($m)->startOfMonth();
                $due = $issued->copy()->addDays(15);
                $total = $service->price_centavos;
                $subtotal = (int) round($total / 1.12);
                $vat = $total - $subtotal;
                $status = $m === 0
                    ? (mt_rand(0,10) > 6 ? 'issued' : 'paid')
                    : ($m === 1 && mt_rand(0,10) > 7 ? 'overdue' : 'paid');
                $invoice = Invoice::create([
                    'invoice_number' => sprintf('SI-2026-%05d', $invN++),
                    'series_code' => 'SI',
                    'customer_id' => $sub->customer_id,
                    'subscription_id' => $sub->id,
                    'issued_at' => $issued,
                    'due_at' => $due,
                    'subtotal_centavos' => $subtotal,
                    'vat_centavos' => $vat,
                    'total_centavos' => $total,
                    'amount_paid_centavos' => $status === 'paid' ? $total : 0,
                    'status' => $status,
                ]);
                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $service->name . ' — ' . $issued->format('M Y'),
                    'quantity' => 1,
                    'unit_price_centavos' => $total,
                    'amount_centavos' => $total,
                    'subscription_id' => $sub->id,
                    'service_id' => $sub->service_id,
                ]);
                if ($status === 'paid') {
                    Payment::create([
                        'payment_number' => sprintf('PMT-2026-%05d', $payN++),
                        'customer_id' => $invoice->customer_id,
                        'invoice_id' => $invoice->id,
                        'amount_centavos' => $total,
                        'gateway' => $faker->randomElement(['gcash','paymaya','bank_transfer','cash','xendit']),
                        'gateway_reference' => 'REF-' . strtoupper(Str::random(10)),
                        'received_at' => $due->copy()->subDays(mt_rand(0,10)),
                        'status' => 'completed',
                    ]);
                }
            }
        }

        echo "  → 5 agents\n";
        foreach (range(1,5) as $i) {
            Agent::create([
                'agent_code' => sprintf('AGT-%03d', $i),
                'name' => $faker->name(),
                'email' => $faker->safeEmail(),
                'phone' => '09' . $faker->numerify('#########'),
                'commission_type' => 'percentage',
                'commission_percentage' => $faker->randomElement([5.00, 7.50, 10.00]),
                'gcash_number' => '09' . $faker->numerify('#########'),
                'is_active' => true,
            ]);
        }

        echo "  → 20 tickets\n";
        foreach (range(1,20) as $i) {
            Ticket::create([
                'ticket_number' => sprintf('TKT-2026-%05d', $i),
                'customer_id' => $customers->random()->id,
                'subject' => $faker->randomElement([
                    'No internet connection', 'Slow speeds at night',
                    'Billing dispute — duplicate charge', 'Request to upgrade plan to 100M',
                    'Router not responding', 'ONU light blinking red',
                    'Disconnect request', 'WiFi password reset',
                ]),
                'body' => $faker->paragraph(),
                'status' => $faker->randomElement(['open','open','open','pending','resolved','closed']),
                'category' => $faker->randomElement(['billing','connectivity','equipment','other']),
                'priority' => $faker->randomElement(['low','normal','normal','high','urgent']),
                'channel' => $faker->randomElement(['portal','phone','email','walkin','social']),
            ]);
        }

        echo "  → 15 job orders\n";
        foreach (range(1,15) as $i) {
            $sched = $faker->dateTimeBetween('-7 days', '+14 days');
            JobOrder::create([
                'job_number' => sprintf('JO-2026-%05d', $i),
                'type' => $faker->randomElement(['install','repair','disconnect','site_survey','relocation']),
                'status' => $faker->randomElement(['pending','pending','dispatched','in_progress','completed','completed']),
                'priority' => $faker->randomElement(['low','normal','normal','high','urgent']),
                'customer_id' => $customers->random()->id,
                'address' => $faker->streetAddress() . ', ' . $faker->randomElement($cities),
                'description' => $faker->sentence(),
                'scheduled_at' => $sched,
            ]);
        }

        echo "  → 30 inventory items\n";
        $skus = ['MK-HEX-S','MK-HAP-AC2','MK-CCR-1009','VSOL-V2802R','HUAWEI-EG8145V5','CABLE-DROP-100M','SC-APC-CONN','SPLITTER-1x8','SPLITTER-1x16'];
        foreach (range(1,30) as $i) {
            InventoryItem::create([
                'sku' => $faker->randomElement($skus),
                'name' => $faker->randomElement($skus) . ' #' . $i,
                'category' => $faker->randomElement(['router','onu','cable','connector','splitter']),
                'serial_number' => 'SN-' . strtoupper(Str::random(10)),
                'quantity' => $faker->randomElement([1,1,1,1,50,100]),
                'unit_cost_centavos' => $faker->randomElement([50000, 120000, 350000, 850000, 1500]),
                'location' => $faker->randomElement(['warehouse','truck-1','truck-2','customer-site']),
                'status' => $faker->randomElement(['in_stock','in_stock','in_stock','installed','checked_out']),
            ]);
        }

        config(['activitylog.enabled' => true]);
        echo "  ✓ demo data seeded\n";
    }
}
