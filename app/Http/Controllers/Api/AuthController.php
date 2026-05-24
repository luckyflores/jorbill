<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController
{
    public function token(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
            'actor'    => ['required', Rule::in(['tech', 'customer'])],
            'device'   => ['nullable', 'string', 'max:100'],
        ]);

        $user = $data['actor'] === 'tech'
            ? User::where('email', $data['email'])->first()
            : Customer::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['error' => 'invalid credentials'], 401);
        }

        if ($data['actor'] === 'tech' && ! ($user->isTech() ?? false)) {
            return response()->json(['error' => 'this account is not a tech account'], 403);
        }
        if ($data['actor'] === 'customer' && ! ($user->portal_enabled ?? false)) {
            return response()->json(['error' => 'portal not enabled for this customer'], 403);
        }

        if ($user instanceof User) {
            $user->update(['last_login_at' => now()]);
        }

        $token = $user->createToken($data['device'] ?? 'diagnose-app')->plainTextToken;

        return response()->json([
            'token'   => $token,
            'actor'   => $data['actor'],
            'user_id' => $user->getKey(),
            'name'    => $user->name,
            'role'    => $user->role ?? 'customer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['ok' => true]);
    }

    public function me(Request $request)
    {
        $u = $request->user();
        return response()->json([
            'id'    => $u->getKey(),
            'name'  => $u->name,
            'email' => $u->email,
            'role'  => $u->role ?? 'customer',
            'actor' => $u instanceof User ? 'tech' : 'customer',
        ]);
    }
}
