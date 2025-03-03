<?php

use Illuminate\Support\Collection;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';

    public string $password = '';

    public $apiTokenIdBeingDeleted;

    public $plainTextToken = '';

    public Collection $tokens;

    public function mount(): void
    {
        $this->tokens = Auth::user()->tokens;
    }

    public function confirmApiTokenDeletion(int $apiTokenId): void
    {
        $this->apiTokenIdBeingDeleted = $apiTokenId;

        $this->modal('confirm-api-token-deletion')->show();
    }

    public function createApiToken(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $token = $this->user->createToken($this->name);
        $this->plainTextToken = explode('|', $token->plainTextToken, 2)[1];

        $this->modal('display-api-token')->show();

        $this->reset('name');

        $this->dispatch('created');
    }

    public function deleteApiToken(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $this->user->tokens()
            ->where('id', $this->apiTokenIdBeingDeleted)
            ->delete();

        $this->reset('password', 'apiTokenIdBeingDeleted');

        $this->user->load('tokens');

        $this->modal('confirm-api-token-deletion')->close();
    }

    public function getUserProperty()
    {
        return auth()->user();
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout heading="Create API tokens" subheading="API tokens allow third-party services to authenticate with our application on your behalf.">
        <form wire:submit="createApiToken" class="mt-6 space-y-6">
            <flux:input wire:model="name" id="api_token_name" label="{{ __('Token name') }}" name="name" required />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">
                        {{ __('Create') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="api-token-created">
                    {{ __('Created.') }}
                </x-action-message>
            </div>
        </form>

        <flux:modal name="display-api-token" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ __('API token') }}
                    </flux:heading>

                    <flux:subheading>
                        {{ __("Please copy your new API token. For your security, it won't be shown again.") }}
                    </flux:subheading>
                </div>

                <flux:input wire:model="plainTextToken" id="api_token_plaintext_token" label="{{ __('API token') }}" name="plaintext_token" copyable readonly autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" />
            </div>
        </flux:modal>

        @if ($this->user->tokens->isNotEmpty())
            <section class="mt-10 space-y-6">
                <div class="relative mb-5">
                    <flux:heading>
                        {{ __('Manage API Tokens') }}
                    </flux:heading>

                    <flux:subheading>
                        {{ __('You may delete any of your existing tokens if they are no longer needed.') }}
                    </flux:subheading>
                </div>

                <div class="space-y-6">
                    @foreach ($this->user->tokens->sortBy('name') as $token)
                        <div class="flex items-center justify-between">
                            <div class="break-all dark:text-white">
                                {{ $token->name }}
                            </div>

                            <div class="flex items-center ms-2">
                                @if ($token->last_used_at)
                                    <div class="text-sm text-zinc-400">
                                        {{ __('Last used') }} {{ $token->last_used_at->diffForHumans() }}
                                    </div>
                                @endif

                                <flux:button wire:click="confirmApiTokenDeletion({{ $token->id }})" variant="danger" size="sm">
                                    {{ __('Delete') }}
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <flux:modal name="confirm-api-token-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
                <form wire:submit="deleteApiToken" class="space-y-6">
                    <div>
                        <flux:heading size="lg">
                            {{ __('Are you sure you want to delete this API token?') }}
                        </flux:heading>

                        <flux:subheading>
                            {{ __('Please enter your password to confirm you would like to delete this API token.') }}
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
                            {{ __('Delete') }}
                        </flux:button>
                    </div>
                </form>
            </flux:modal>
        @endif
    </x-settings.layout>
</section>
