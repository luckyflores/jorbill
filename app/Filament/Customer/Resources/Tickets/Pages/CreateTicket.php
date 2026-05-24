<?php
namespace App\Filament\Customer\Resources\Tickets\Pages;
use App\Filament\Customer\Resources\Tickets\TicketResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['customer_id']   = auth('customer')->id();
        $data['ticket_number'] = 'TKT-PORTAL-' . strtoupper(Str::random(6));
        $data['status']        = 'open';
        $data['channel']       = 'portal';
        return $data;
    }
}
