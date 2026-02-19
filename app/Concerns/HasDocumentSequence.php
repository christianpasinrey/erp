<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Services\SequenceService;

/**
 * Trait for models that need auto-generated document numbers (invoices, orders, etc.).
 */
trait HasDocumentSequence
{
    public static function bootHasDocumentSequence(): void
    {
        static::creating(function ($model) {
            $field = $model->getSequenceField();
            if (empty($model->{$field})) {
                $model->{$field} = app(SequenceService::class)->next(
                    companyId: $model->company_id,
                    type: $model->getSequenceType(),
                );
            }
        });
    }

    /**
     * The database column that stores the document number.
     */
    protected function getSequenceField(): string
    {
        return property_exists($this, 'sequenceField')
            ? $this->sequenceField
            : 'number';
    }

    /**
     * The sequence type identifier (e.g., 'invoice', 'order', 'quote').
     */
    protected function getSequenceType(): string
    {
        return property_exists($this, 'sequenceType')
            ? $this->sequenceType
            : strtolower(class_basename($this));
    }
}
