<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Concerns\EvaluatesClosures;

class BusinessSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static \UnitEnum|string|null $navigationGroup = 'System';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $title = 'Business Settings';
    protected static ?int $navigationSort = 80;

    protected string $view = 'filament.pages.business-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [
            'company_name'   => Setting::get('business.company_name', ''),
            'tin'            => Setting::get('business.tin', ''),
            'address'        => Setting::get('business.address', ''),
            'vat_registered' => (bool) Setting::get('business.vat_registered', true),
            'vat_rate'       => (float) Setting::get('business.vat_rate', 12.0),
            'email'          => Setting::get('business.email', ''),
            'phone'          => Setting::get('business.phone', ''),
        ];
        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Company identity')
                    ->columns(2)
                    ->components([
                        TextInput::make('company_name')->required()->maxLength(255)->columnSpan(1),
                        TextInput::make('tin')->label('Tax Identification Number (TIN)')->maxLength(64)->columnSpan(1),
                        Textarea::make('address')->columnSpanFull(),
                        TextInput::make('email')->email()->columnSpan(1),
                        TextInput::make('phone')->columnSpan(1),
                    ]),

                Section::make('VAT')
                    ->columns(2)
                    ->components([
                        Toggle::make('vat_registered')
                            ->label('Company is VAT-registered')
                            ->helperText('When ON: invoices break out subtotal + 12% VAT. When OFF: invoices show total only, no VAT line.')
                            ->live()
                            ->columnSpan(1),
                        TextInput::make('vat_rate')
                            ->label('VAT rate (%)')
                            ->numeric()->step(0.01)->default(12.0)
                            ->visible(fn ($get) => $get('vat_registered'))
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        Setting::put('business.company_name',   (string) ($data['company_name'] ?? ''));
        Setting::put('business.tin',            (string) ($data['tin'] ?? ''));
        Setting::put('business.address',        (string) ($data['address'] ?? ''));
        Setting::put('business.vat_registered', $data['vat_registered'] ? '1' : '0');
        Setting::put('business.vat_rate',       (string) ($data['vat_rate'] ?? 12.0));
        Setting::put('business.email',          (string) ($data['email'] ?? ''));
        Setting::put('business.phone',          (string) ($data['phone'] ?? ''));

        Notification::make()->title('Settings saved')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')->label('Save changes')->icon('heroicon-o-check')->action('save'),
        ];
    }
}
