<?php

namespace App\Filament\Resources\AutomationRules\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                ->description('What event fires this rule?')
                ->columns(3)
                ->components([
                    Select::make('trigger_config.model')
                        ->label('Model')
                        ->required()
                        ->options(self::modelOptions())
                        ->native(false),
                    Select::make('trigger_config.when')
                        ->label('When')
                        ->required()
                        ->options([
                            'created' => 'is created',
                            'updated' => 'is updated',
                            'deleted' => 'is deleted',
                        ])
                        ->default('updated')
                        ->native(false),
                    TextInput::make('trigger_config.if_changed')
                        ->label('Only if field changed (optional)')
                        ->placeholder('status')
                        ->helperText('Field name (e.g. status). Applies only when "is updated" is the trigger.'),
                ]),

            Section::make('Conditions')
                ->description('All conditions must match (AND). Leave empty to fire on every trigger.')
                ->components([
                    Repeater::make('conditions')
                        ->schema([
                            TextInput::make('field')
                                ->required()
                                ->placeholder('subscription.status')
                                ->helperText('Dot-notation: subscription.status / customer.phone / payment.amount_centavos')
                                ->columnSpan(2),
                            Select::make('operator')
                                ->required()
                                ->options([
                                    'eq'          => 'equals',
                                    'ne'          => 'does not equal',
                                    'in'          => 'is in (comma-separated)',
                                    'not_in'      => 'is not in',
                                    'gt'          => 'greater than',
                                    'lt'          => 'less than',
                                    'contains'    => 'contains',
                                    'is_null'     => 'is empty',
                                    'is_not_null' => 'is not empty',
                                ])
                                ->default('eq')
                                ->native(false),
                            TextInput::make('value')
                                ->placeholder('active')
                                ->columnSpan(2),
                        ])
                        ->columns(5)
                        ->orderable(false)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => isset($state['field']) ? "{$state['field']} {$state['operator']} " . ($state['value'] ?? '') : null)
                        ->addActionLabel('+ Condition'),
                ]),

            Section::make('Actions')
                ->description('Actions run in order. Strings can use {{customer.name}}, {{subscription.username}}, etc.')
                ->components([
                    Repeater::make('actions')
                        ->required()
                        ->schema([
                            Select::make('type')
                                ->required()
                                ->live()
                                ->options([
                                    'send_sms'         => 'Send SMS',
                                    'update_field'     => 'Update a field',
                                    'create_job_order' => 'Create a Job Order',
                                    'log_activity'     => 'Log an activity entry',
                                ])
                                ->columnSpan(2)
                                ->native(false),

                            // send_sms
                            TextInput::make('to')->label('To')->default('{{customer.phone}}')
                                ->visible(fn ($get) => $get('type') === 'send_sms')->columnSpan(2),
                            Textarea::make('body')->label('SMS body')
                                ->visible(fn ($get) => $get('type') === 'send_sms')->columnSpanFull(),

                            // update_field
                            TextInput::make('target')->label('Target field')->placeholder('status')
                                ->visible(fn ($get) => $get('type') === 'update_field')->columnSpan(2),
                            TextInput::make('value')->label('Value')->placeholder('active')
                                ->visible(fn ($get) => $get('type') === 'update_field')->columnSpan(2),

                            // create_job_order
                            Select::make('job_type')
                                ->label('JO type')
                                ->options(['install'=>'Install','repair'=>'Repair','disconnect'=>'Disconnect','site_survey'=>'Site survey'])
                                ->default('repair')
                                ->visible(fn ($get) => $get('type') === 'create_job_order')
                                ->native(false),
                            Select::make('priority')
                                ->options(['low'=>'Low','normal'=>'Normal','high'=>'High','urgent'=>'Urgent'])
                                ->default('normal')
                                ->visible(fn ($get) => $get('type') === 'create_job_order')
                                ->native(false),
                            TextInput::make('customer_id')->default('{{customer.id}}')
                                ->visible(fn ($get) => $get('type') === 'create_job_order')->columnSpan(2),
                            Textarea::make('description')
                                ->visible(fn ($get) => $get('type') === 'create_job_order')->columnSpanFull(),

                            // log_activity (uses 'description' too — visible block is separate to keep schema simple)
                            Textarea::make('description')
                                ->label('Activity description')
                                ->visible(fn ($get) => $get('type') === 'log_activity')->columnSpanFull(),
                        ])
                        ->columns(4)
                        ->orderable(true)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['type'] ?? null)
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
