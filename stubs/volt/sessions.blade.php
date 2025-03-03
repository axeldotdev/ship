<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component
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
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout heading="Manage sessions" subheading="Manage and log out your active sessions on other browsers and devices.">
        @if (count($this->user->sessions) > 0)
            <div class="mt-5 space-y-6">
                @foreach ($this->user->sessions as $session)
                    <div class="flex items-center">
                        <div>
                            @if ($session->agent->isDesktop())
                                <flux:icon.computer-desktop class="size-8 text-zinc-500" />
                            @else
                                <flux:icon.device-phone-mobile class="size-8 text-zinc-500" />
                            @endif
                        </div>

                        <div class="ms-3">
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $session->agent->platform() ? $session->agent->platform() : __('Unknown') }} - {{ $session->agent->browser() ? $session->agent->browser() : __('Unknown') }}
                            </div>

                            <div>
                                <div class="text-xs text-zinc-500">
                                    {{ $session->ip_address }},

                                    @if ($session->is_current_device)
                                        <span class="text-green-500 font-semibold">
                                            {{ __('This device') }}
                                        </span>
                                    @else
                                        {{ __('Last active') }} {{ $session->last_active }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex items-center mt-5 gap-4">
            <flux:modal.trigger name="confirm-sessions-logout">
                <flux:button>
                    {{ __('Log Out Other Browser Sessions') }}
                </flux:button>
            </flux:modal.trigger>

            <x-action-message class="me-3" on="logged-out">
                {{ __('Done.') }}
            </x-action-message>
        </div>

        <flux:modal name="confirm-sessions-logout" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
            <form wire:submit="logoutSessions" class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ __('Are you sure you want to logout of other sessions?') }}
                    </flux:heading>

                    <flux:subheading>
                        {{ __('Please enter your password to confirm you would like to log out of your other browser sessions.') }}
                    </flux:subheading>
                </div>

                <flux:input wire:model="password" id="password" label="{{ __('Password') }}" type="password" name="password" />

                <div class="flex justify-end space-x-2">
                    <flux:modal.close>
                        <flux:button variant="filled">
                            {{ __('Cancel') }}
                        </flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" type="submit">
                        {{ __('Logout') }}
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    </x-settings.layout>
</section>
