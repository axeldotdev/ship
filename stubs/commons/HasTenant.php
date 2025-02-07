<?php

namespace App\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasTenant
{
    public function belongsToTenant(Tenant $tenant): bool
    {
        return $this->tenants->contains($tenant);
    }

    /** @return BelongsTo<Tenant, $this> */
    public function currentTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'current_tenant_id');
    }

    public function switchTenant(Tenant $tenant): bool
    {
        $this->forceFill([
            'current_tenant_id' => $tenant->id,
        ])->save();

        $this->setRelation('currentTenant', $tenant);

        return true;
    }

    /** @return BelongsToMany<Tenant, $this> */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class)->withTimestamps();
    }
}
