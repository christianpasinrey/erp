<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Modules\Contacts\Models\Address;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAddresses
{
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function primaryAddress(): ?Address
    {
        return $this->addresses()->where('is_primary', true)->first();
    }
}
