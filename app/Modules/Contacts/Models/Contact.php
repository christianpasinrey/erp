<?php

declare(strict_types=1);

namespace App\Modules\Contacts\Models;

use App\Concerns\Auditable;
use App\Concerns\BelongsToCompany;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use Auditable, BelongsToCompany, HasFactory, SoftDeletes;

    protected static function newFactory(): ContactFactory
    {
        return ContactFactory::new();
    }

    protected $fillable = [
        'company_id',
        'type',
        'first_name',
        'last_name',
        'job_title',
        'organization_id',
        'name',
        'industry',
        'email',
        'phone',
        'mobile',
        'website',
        'notes',
        'tags',
        'custom_fields',
        'avatar_path',
        'logo_path',
        'is_active',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'custom_fields' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope route model binding by current company (session-based).
     *
     * SubstituteBindings runs before ResolveCompany middleware,
     * so we read company_id from session directly.
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        $companyId = request()->session()->get('current_company_id');

        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->when($companyId, fn ($q) => $q->withoutGlobalScope('company')->where('company_id', $companyId))
            ->firstOrFail();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(self::class, 'organization_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(self::class, 'organization_id');
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'organization') {
            return $this->name ?? '';
        }

        return trim("{$this->first_name} {$this->last_name}");
    }

    public function scopePersons(Builder $query): Builder
    {
        return $query->where('type', 'person');
    }

    public function scopeOrganizations(Builder $query): Builder
    {
        return $query->where('type', 'organization');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }
}
