<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user->hasAnyRole([
            'admin',
            'super admin'
        ])) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('home');
    }
}