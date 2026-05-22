<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name'        => 'payment_received',
                'label'       => 'Payment received',
                'description' => 'Sent to the customer immediately after a payment is recorded.',
                'channel'     => 'sms',
                'body'        => 'Hi {{customer.name}}, payment received. Your internet service has been restored. Thank you for settling your account.',
            ],
            [
                'name'        => 'subscription_activated',
                'label'       => 'Service activated (welcome)',
                'description' => 'Sent when a subscription is activated for the first time.',
                'channel'     => 'sms',
                'body'        => 'Hi {{customer.name}}, your service is now ACTIVE. PPPoE username: {{subscription.username}}. Welcome to JorBill!',
            ],
            [
                'name'        => 'subscription_suspended',
                'label'       => 'Service suspended',
                'description' => 'Sent when a subscription is suspended (manually or by automation).',
                'channel'     => 'sms',
                'body'        => 'Hi {{customer.name}}, your internet has been suspended. Please settle your account to restore service.',
            ],
            [
                'name'        => 'subscription_cancelled',
                'label'       => 'Service cancelled',
                'description' => 'Sent when a subscription is cancelled.',
                'channel'     => 'sms',
                'body'        => 'Hi {{customer.name}}, your service has been cancelled. We are sad to see you go.',
            ],
            [
                'name'        => 'invoice_due_reminder',
                'label'       => 'Invoice due reminder',
                'description' => 'Sent X days before an invoice is due (configure schedule on the rule).',
                'channel'     => 'sms',
                'body'        => 'Hi {{customer.name}}, your invoice {{invoice.invoice_number}} is due in {{invoice.days_until_due}} day(s). Total: PHP {{invoice.total_centavos}}.',
            ],
            [
                'name'        => 'job_order_created',
                'label'       => 'Job order created (customer notice)',
                'description' => 'Sent to the customer when a Job Order is scheduled for them.',
                'channel'     => 'sms',
                'body'        => 'Hi {{customer.name}}, a job order has been created. Our team will arrive on {{joborder.scheduled_at}}. Job #: {{joborder.job_number}}.',
            ],
        ];

        foreach ($templates as $row) {
            NotificationTemplate::updateOrCreate(['name' => $row['name']], $row);
        }

        $this->command->info('  ✓ 6 default notification templates seeded');
    }
}
