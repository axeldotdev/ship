<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class ApiTokens extends Component
{
    public string $name = '';

    public string $password = '';

    public $apiTokenIdBeingDeleted;

    public $plainTextToken = '';

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
}
