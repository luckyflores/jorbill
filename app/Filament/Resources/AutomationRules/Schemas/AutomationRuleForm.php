<?php

namespace App\Filament\Resources\AutomationRules\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AutomationRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identification')
                ->columns(2)
                ->components([
                    TextInput::make('name')->required()->maxLength(255)->columnSpan(1),
                    Toggle::make('is_enabled')->default(true)->columnSpan(1),
                    Textarea::make('description')->columnSpanFull(),
                ]),

            Section::make('Trigger')
                ->description('What kind of event fires this rule?')
                ->components([
                    Select::make('trigger_type')
                        ->required()
                        ->live()
                        ->options([
                            'model'     => 'Model event (created / updated / deleted)',
                            'scheduled' => 'Scheduled (daily / weekly / monthly / cron)',
                        ])
                        ->default('model')
                        ->native(false),

                    // ─── MODEL TRIGGER FIELDS ──────────────────────────
                    Select::make('trigger_config.model')
                        ->label('Model')
                        ->visible(fn ($get) => $get('trigger_type') === 'model')
                        ->options(self::modelOptions())
                        ->native(false),
                    Select::make('trigger_config.when')
                        ->label('When')
                        ->visible(fn ($get) => $get('trigger_type') === 'model')
                        ->options([
                            'created' => 'is created',
                            'updated' => 'is updated',
                            'deleted' => 'is deleted',
                        ])
                        ->default('updated')
                        ->native(false),
                    TextInput::make('trigger_config.if_changed')
                        ->label('Only if field changed (optional)')
                        ->visible(fn ($get) => $get('trigger_type') === 'model')
                        ->placeholder('status'),

                    // ─── SCHEDULED TRIGGER FIELDS ──────────────────────
                    Select::make('trigger_config.schedule_type')
                        ->label('Frequency')
                        ->visible(fn ($get) => $get('trigger_type') === 'scheduled')
                        ->live()
                        ->options([
                            'daily'   => 'Daily',
                            'weekly'  => 'Weekly',
                            'monthly' => 'Monthly',
                            'cron'    => 'Custom cron expression',
                        ])
                        ->default('daily')
                        ->native(false),
                    TimePicker::make('trigger_config.time')
                        ->label('Time of day (Asia/Manila)')
                        ->visible(fn ($get) => $get('trigger_type') === 'scheduled' && ($get('trigger_config.schedule_type') ?? 'daily') !== 'cron')
                        ->seconds(false)
                        ->default('08:00'),
                    TextInput::make('trigger_config.day_of_month')
                        ->label('Day of month')
                        ->visible(fn ($get) => $get('trigger_config.schedule_type') === 'monthly')
                        ->numeric()
                        ->minValue(1)->maxValue(31)
                        ->default(5)
                        ->helperText('1-31. Months without that day will skip.'),
                    Select::make('trigger_config.day_of_week')
                        ->label('Day of week')
                        ->visible(fn ($get) => $get('trigger_config.schedule_type') === 'weekly')
                        ->options([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'])
                        ->default(1)
                        ->native(false),
                    TextInput::make('trigger_config.cron')
                        ->label('Cron expression')
                        ->visible(fn ($get) => $get('trigger_config.schedule_type') === 'cron')
                        ->placeholder('0 8 5 * *')
                        ->helperText('Standard 5-field cron (min hr day month weekday).'),
                    Select::make('trigger_config.target_model')
                        ->label('Apply to records of')
                        ->visible(fn ($get) => $get('trigger_type') === 'scheduled')
                        ->options(self::modelOptions())
                        ->native(false),
                ]),

            Section::make('Target filter (only for scheduled)')
                ->description('Pre-query filter — rows must match all of these to be processed. Supports computed fields: invoice.days_overdue, invoice.days_until_due, subscription.days_since_activated, customer.days_since_activated.')
                ->visible(fn ($get) => $get('trigger_type') === 'scheduled')
                ->components([
                    Repeater::make('target_filter')
                        ->schema([
                            TextInput::make('field')->required()->placeholder('status / days_overdue')->columnSpan(2),
                            Select::make('operator')->required()->options([
                                'eq'=>'equals','ne'=>'does not equal','in'=>'is in (CSV)','not_in'=>'is not in (CSV)',
                                'gt'=>'greater than','lt'=>'less than','contains'=>'contains',
                                'is_null'=>'is empty','is_not_null'=>'is not empty',
                            ])->default('eq')->native(false),
                            TextInput::make('value')->columnSpan(2),
                        ])
                        ->columns(5)
                        ->collapsible()
                        ->itemLabel(fn ($state) => isset($state['field']) ? "{$state['field']} {$state['operator']} " . ($state['value'] ?? '') : null)
                        ->addActionLabel('+ Filter'),
                ]),

            Section::make('Conditions (per record)')
                ->description('Evaluated for each matching record after the target filter. All must match (AND). Empty = always pass.')
                ->components([
                    Repeater::make('conditions')
                        ->schema([
                            TextInput::make('field')->required()->placeholder('customer.status')->columnSpan(2),
                            Select::make('operator')->required()->options([
                                'eq'=>'equals','ne'=>'does not equal','in'=>'is in (CSV)','not_in'=>'is not in',
                                'gt'=>'greater than','lt'=>'less than','contains'=>'contains',
                                'is_null'=>'is empty','is_not_null'=>'is not empty',
                            ])->default('eq')->native(false),
                            TextInput::make('value')->columnSpan(2),
                        ])
                        ->columns(5)
                        ->collapsible()
                        ->itemLabel(fn ($state) => isset($state['field']) ? "{$state['field']} {$state['operator']} " . ($state['value'] ?? '') : null)
                        ->addActionLabel('+ Condition'),
                ]),

            Section::make('Actions')
                ->description('Actions run in order per matching record. Strings support {{customer.name}}, {{invoice.invoice_number}}, etc.')
                ->components([
                    Repeater::make('actions')
                        ->required()
                        ->schema([
                            Select::make('type')->required()->live()->options([
                                'send_sms'         => 'Send SMS',
                                'update_field'     => 'Update a field',
                                'create_job_order' => 'Create a Job Order',
                                'log_activity'     => 'Log an activity entry',
                            ])->columnSpan(2)->native(false),

                            TextInput::make('to')->label('To')->default('{{customer.phone}}')
                                ->visible(fn ($get) => $get('type') === 'send_sms')->columnSpan(2),
                            Textarea::make('body')->label('SMS body')
                                ->visible(fn ($get) => $get('type') === 'send_sms')->columnSpanFull(),

                            TextInput::make('target')->label('Target field')->placeholder('subscription.status')
                                ->visible(fn ($get) => $get('type') === 'update_field')->columnSpan(2),
                            TextInput::make('value')->label('Value')->placeholder('suspended')
                                ->visible(fn ($get) => $get('type') === 'update_field')->columnSpan(2),

                            Select::make('job_type')
                                ->options(['install'=>'Install','repair'=>'Repair','disconnect'=>'Disconnect','site_survey'=>'Site survey'])
                                ->default('repair')
                                ->visible(fn ($get) => $get('type') === 'create_job_order')->native(false),
                            Select::make('priority')
                                ->options(['low'=>'Low','normal'=>'Normal','high'=>'High','urgent'=>'Urgent'])
                                ->default('normal')
                                ->visible(fn ($get) => $get('type') === 'create_job_order')->native(false),
                            TextInput::make('customer_id')->default('{{customer.id}}')
                                ->visible(fn ($get) => $get('type') === 'create_job_order')->columnSpan(2),
                            Textarea::make('description')
                                ->visible(fn ($get) => in_array($get('type'), ['create_job_order', 'log_activity'], true))
                                ->columnSpanFull(),
                        ])
                        ->columns(4)
                        ->orderable(true)
                        ->collapsible()
                        ->itemLabel(fn ($state) => $state['type'] ?? null)
                        ->addActionLabel('+ Action'),
                ]),
        ]);
    }

    private static function modelOptions(): array
    {
        return [
            'App\\Models\\Customer'     => 'Customer',
            'App\\Models\\Lead'         => 'Lead',
            'App\\Models\\Subscription' => 'Subscription',
            'App\\Models\\Invoice'      => 'Invoice',
            'App\\Models\\Payment'      => 'Payment',
            'App\\Models\\JobOrder'     => 'Job Order',
            'App\\Models\\Ticket'       => 'Ticket',
            'App\\Models\\Onu'          => 'ONU',
        ];
    }
}
