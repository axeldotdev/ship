<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Sessions extends Component
{
    public string $password = '';

    public function logoutSessions(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        if (config('session.driver') !== 'database') {
            return;
        }

        Auth::logoutOtherDevices($this->password);

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $this->user->id)
            ->where('id', '!=', request()->session()->getId())
            ->delete();

        request()->session()->put([
            'password_hash_'.Auth::getDefaultDriver() => $this->user->getAuthPassword(),
        ]);

        $this->dispatch('logged-out');
    }

    public function getUserProperty()
    {
        return auth()->user();
    }
}
