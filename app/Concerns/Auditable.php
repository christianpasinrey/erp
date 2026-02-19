<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\AuditLog;
use App\Services\CompanyContext;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Trait for models that should log changes to the audit_logs table.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logAudit('created', [], $model->getAttributes());
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            if (empty($dirty)) {
                return;
            }

            $old = collect($dirty)->mapWithKeys(fn ($value, $key) => [
                $key => $model->getOriginal($key),
            ])->toArray();

            $model->logAudit('updated', $old, $dirty);
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted', $model->getOriginal(), []);
        });
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    protected function logAudit(string $event, array $oldValues, array $newValues): void
    {
        $excluded = $this->getAuditExclude();

        $oldValues = array_diff_key($oldValues, array_flip($excluded));
        $newValues = array_diff_key($newValues, array_flip($excluded));

        AuditLog::create([
            'company_id' => $this->company_id ?? app(CompanyContext::class)->id(),
            'user_id' => Auth::id(),
            'auditable_type' => $this->getMorphClass(),
            'auditable_id' => $this->getKey(),
            'event' => $event,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
        ]);
    }

    /**
     * Columns to exclude from auditing (passwords, tokens, etc.).
     *
     * @return list<string>
     */
    protected function getAuditExclude(): array
    {
        return property_exists($this, 'auditExclude')
            ? $this->auditExclude
            : ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];
    }
}
