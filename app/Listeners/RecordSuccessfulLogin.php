<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Auth\Events\Login;

class RecordSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $userId = $event->user->getAuthIdentifier();

        User::whereKey($userId)->update(['last_login_at' => now()]);

        UserLogin::create([
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'logged_in_at' => now(),
        ]);
    }
}
