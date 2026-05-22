<?php

namespace App\Filament\Resources\Olts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OltForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('vendor')
                ->required()
                ->options([
                    'zte_cli' => 'ZTE-CLI (real ZTE)',
                    'oem_zte' => 'OEM ZTE-clone (Lambda / C-Data / others)',
                    'huawei'  => 'Huawei (TODO)',
                    'vsol'    => 'VSOL (TODO)',
                    'bdcom'   => 'BDCOM (TODO)',
                ])
                ->default('oem_zte')
                ->native(false),
            TextInput::make('model')->maxLength(255)->placeholder('ZXA10 C300 / C600'),
            TextInput::make('location')->maxLength(255),
            TextInput::make('ip_address')->required()->maxLength(45),
            TextInput::make('ssh_port')->numeric()->default(22),
            TextInput::make('ssh_user')->required()->maxLength(255),
            TextInput::make('ssh_password')->password()->revealable()->required()->maxLength(255),
            TextInput::make('enable_password')->password()->revealable()->maxLength(255)
                ->helperText('Optional. Required if your OLT prompts for an enable password after `en`.'),
            TextInput::make('prompt_pattern')->default('[#>]')->maxLength(255)
                ->helperText('Regex tail of CLI prompt. Default works for most ZTE/ZTE-clone OLTs.'),
            Select::make('save_command')
                ->options([
                    'write' => 'write',
                    'save'  => 'save',
                    'copy running-config startup-config' => 'copy running-config startup-config',
                ])
                ->default('write')
                ->native(false)
                ->helperText('Command used to persist running config to startup.'),
            Toggle::make('is_active')->default(true),
            Textarea::make('notes')->nullable()->columnSpanFull(),
        ]);
    }
}
