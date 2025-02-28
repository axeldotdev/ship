<?php

namespace App\Models;

use App\Support\Agent;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    /** @return Attribute<mixed, mixed> */
    public function agent(): Attribute
    {
        return new Attribute(fn ($value) => tap(new Agent, fn ($agent): string => $agent->setUserAgent($this->user_agent ?? '')));
    }

    /** @return Attribute<mixed, mixed> */
    public function isCurrentDevice(): Attribute
    {
        return new Attribute(fn (): bool => (string) $this->id === (string) request()->session()->getId());
    }

    /** @return Attribute<mixed, mixed> */
    public function lastActive(): Attribute
    {
        return new Attribute(fn (): string => $this->last_activity->diffForHumans());
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'last_activity' => 'datetime',
        ];
    }
}
