<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\CustomerDiagnostic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiagnosticsController
{
    public function store(Request $request)
    {
        $actor = $request->user();

        $data = $request->validate([
            'ran_at'        => ['nullable', 'date'],
            'customer_id'   => ['nullable', 'integer', 'exists:customers,id'],
            'public_ip'     => ['nullable', 'string', 'max:45'],
            'wifi'          => ['nullable', 'array'],
            'ping_results'  => ['nullable', 'array'],
            'speedtest'     => ['nullable', 'array'],
            'notes'         => ['nullable', 'string', 'max:5000'],
            'gps_lat'       => ['nullable', 'numeric'],
            'gps_lng'       => ['nullable', 'numeric'],
            'photo_path'    => ['nullable', 'string', 'max:255'],
            'app_version'   => ['nullable', 'string', 'max:50'],
            'device_info'   => ['nullable', 'array'],
        ]);

        // Determine customer + tech attribution
        if ($actor instanceof User) {
            if (empty($data['customer_id'])) {
                return response()->json(['error' => 'customer_id required when tech posts a diagnostic'], 422);
            }
            $customerId = $data['customer_id'];
            $techId     = $actor->id;
        } else {
            // actor is a Customer
            $customerId = $actor->getKey();
            $techId     = null;
        }

        $diagnostic = CustomerDiagnostic::create([
            'customer_id'   => $customerId,
            'tech_user_id'  => $techId,
            'ran_at'        => $data['ran_at'] ?? now(),
            'public_ip'     => $data['public_ip']     ?? $request->ip(),
            'wifi'          => $data['wifi']          ?? null,
            'ping_results'  => $data['ping_results']  ?? null,
            'speedtest'     => $data['speedtest']     ?? null,
            'notes'         => $data['notes']         ?? null,
            'gps_lat'       => $data['gps_lat']       ?? null,
            'gps_lng'       => $data['gps_lng']       ?? null,
            'photo_path'    => $data['photo_path']    ?? null,
            'app_version'   => $data['app_version']   ?? null,
            'device_info'   => $data['device_info']   ?? null,
        ]);

        return response()->json([
            'data' => $diagnostic,
            'id'   => $diagnostic->id,
        ], 201);
    }

    public function mine(Request $request)
    {
        $actor = $request->user();
        $q = CustomerDiagnostic::query()->orderByDesc('ran_at')->limit(50);

        if ($actor instanceof User) {
            $q->where('tech_user_id', $actor->id);
        } else {
            $q->where('customer_id', $actor->getKey());
        }
        return response()->json(['data' => $q->get()]);
    }

    public function forCustomer(Customer $customer)
    {
        return response()->json([
            'data' => CustomerDiagnostic::where('customer_id', $customer->id)
                ->orderByDesc('ran_at')->limit(100)->get(),
        ]);
    }
}
