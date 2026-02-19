<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSequence extends Model
{
    protected $fillable = [
        'company_id',
        'type',
        'prefix',
        'year',
        'current',
        'pattern',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'current' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Generate the next number in the sequence (atomically).
     */
    public function nextNumber(): string
    {
        $this->increment('current');
        $this->refresh();

        return $this->formatNumber($this->current);
    }

    /**
     * Format a number using the configured pattern.
     */
    public function formatNumber(int $number): string
    {
        $pattern = $this->pattern;

        // Parse the number format from pattern (e.g., {number:05} means 5 digits)
        $result = preg_replace_callback('/\{number:(\d+)\}/', function ($matches) use ($number) {
            return str_pad((string) $number, (int) $matches[1], '0', STR_PAD_LEFT);
        }, $pattern);

        $result = str_replace('{prefix}', $this->prefix ?? '', $result);
        $result = str_replace('{year}', (string) $this->year, $result);

        return $result;
    }
}
