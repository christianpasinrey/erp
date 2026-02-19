<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentSequence;
use Illuminate\Support\Facades\DB;

class SequenceService
{
    /**
     * Get the next document number for the given company and type.
     * Uses database locking for atomic increment.
     */
    public function next(int $companyId, string $type, ?int $year = null): string
    {
        $year ??= now()->year;

        return DB::transaction(function () use ($companyId, $type, $year) {
            $sequence = DocumentSequence::query()
                ->where('company_id', $companyId)
                ->where('type', $type)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = DocumentSequence::create([
                    'company_id' => $companyId,
                    'type' => $type,
                    'prefix' => strtoupper(substr($type, 0, 3)),
                    'year' => $year,
                    'current' => 0,
                ]);
            }

            return $sequence->nextNumber();
        });
    }

    /**
     * Preview what the next number would look like without incrementing.
     */
    public function preview(int $companyId, string $type, ?int $year = null): string
    {
        $year ??= now()->year;

        $sequence = DocumentSequence::query()
            ->where('company_id', $companyId)
            ->where('type', $type)
            ->where('year', $year)
            ->first();

        if (! $sequence) {
            return strtoupper(substr($type, 0, 3)) . $year . '-00001';
        }

        return $sequence->formatNumber($sequence->current + 1);
    }
}
