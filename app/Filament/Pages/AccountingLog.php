<?php

namespace App\Filament\Pages;

use App\Models\RadiusSession;
use Filament\Pages\Page;

class AccountingLog extends Page
{
    protected static \UnitEnum|string|null $navigationGroup = 'Network';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $title = 'Accounting Log';
    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.accounting-log';

    public function getRecent()
    {
        return RadiusSession::orderBy('acctstarttime', 'desc')->limit(100)->get();
    }
}
