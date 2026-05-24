<?php

namespace App\Filament\Customer\Pages;

use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Support\Facades\Hash;

class Account extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $title = 'My Account';
    protected static ?int $navigationSort = 40;

    protected string $view = 'filament.customer.pages.account';

    public ?array $data = [];

    public function mount(): void
    {
        $customer = auth('customer')->user();
        $this->data = [
            'name'  => $customer?->name,
            'email' => $customer?->email,
            'phone' => $customer?->phone,
            'current_password' => '',
            'new_password' => '',
            'new_password_confirmation' => '',
        ];
        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('Profile')
                ->columns(2)
                ->components([
                    TextInput::make('name')->disabled(),
                    TextInput::make('email')->disabled(),
                    TextInput::make('phone')->disabled()->columnSpan(1)->helperText('Contact support to change these.'),
                ]),
            Section::make('Change password')
                ->columns(2)
                ->components([
                    TextInput::make('current_password')->password()->revealable()->required()->columnSpanFull(),
                    TextInput::make('new_password')->password()->revealable()->required()->minLength(8)->columnSpan(1),
                    TextInput::make('new_password_confirmation')->label('Confirm new password')
                        ->password()->revealable()->required()->same('new_password')->columnSpan(1),
                ]),
        ]);
    }

    public function changePassword(): void
    {
        $data = $this->form->getState();
        $customer = auth('customer')->user();
        if (! Hash::check($data['current_password'], $customer->password)) {
            Notification::make()->title('Current password is incorrect')->danger()->send();
            return;
        }
        $customer->forceFill(['password' => Hash::make($data['new_password'])])->save();
        $this->data['current_password'] = '';
        $this->data['new_password'] = '';
        $this->data['new_password_confirmation'] = '';
        $this->form->fill($this->data);
        Notification::make()->title('Password changed')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changePassword')->label('Change password')->icon('heroicon-o-key')->action('changePassword'),
        ];
    }
}
