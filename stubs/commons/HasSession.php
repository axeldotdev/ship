<?php

namespace App\Concerns;

use App\Models\Session;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSession
{
    /** @return HasMany<Session, $this> */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
