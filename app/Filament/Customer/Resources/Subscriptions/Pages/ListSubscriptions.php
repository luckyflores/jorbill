<?php

namespace App\Filament\Customer\Resources\Subscriptions\Pages;

use App\Filament\Customer\Resources\Subscriptions\SubscriptionResource;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;
}
