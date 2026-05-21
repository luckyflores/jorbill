<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Nap;
use Filament\Pages\Page;

class NetworkMap extends Page
{
    protected static \UnitEnum|string|null $navigationGroup = 'Network';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-map';
    protected static ?string $title = 'Map';
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.network-map';

    public function getViewData(): array
    {
        return [
            'customers' => Customer::query()
                ->whereNotNull('latitude')->whereNotNull('longitude')
                ->select('id','customer_code','name','status','phone','city','latitude','longitude')
                ->get()
                ->map(fn ($c) => [
                    'id'    => $c->id,
                    'code'  => $c->customer_code,
                    'name'  => $c->name,
                    'status'=> $c->status,
                    'phone' => $c->phone,
                    'city'  => $c->city,
                    'lat'   => (float) $c->latitude,
                    'lng'   => (float) $c->longitude,
                ])->values(),

            'naps' => Nap::query()
                ->whereNotNull('latitude')->whereNotNull('longitude')
                ->select('id','code','name','type','capacity','ports_used','latitude','longitude')
                ->get()
                ->map(fn ($n) => [
                    'id'         => $n->id,
                    'code'       => $n->code,
                    'name'       => $n->name,
                    'type'       => $n->type,
                    'capacity'   => $n->capacity,
                    'ports_used' => $n->ports_used,
                    'lat'        => (float) $n->latitude,
                    'lng'        => (float) $n->longitude,
                ])->values(),
        ];
    }
}
