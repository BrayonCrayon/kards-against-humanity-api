<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CreatingUser
{
    public function __invoke($name)
    {
        $user = User::create([
            'name' => $name
        ]);
        Auth::loginUsingId($user->id);
        return $user;
    }
}
