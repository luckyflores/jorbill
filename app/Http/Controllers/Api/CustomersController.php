<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomersController
{
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['data' => []]);
        }
        $rows = Customer::query()
            ->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('customer_code', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            })
            ->limit(25)
            ->get(['id', 'customer_code', 'name', 'phone', 'email', 'status', 'address_line1', 'city']);

        return response()->json(['data' => $rows]);
    }

    public function show(Customer $customer)
    {
        return response()->json([
            'data' => $customer->only([
                'id','customer_code','name','phone','email','status',
                'address_line1','barangay','city','province',
                'latitude','longitude','activated_at',
            ]),
        ]);
    }
}
