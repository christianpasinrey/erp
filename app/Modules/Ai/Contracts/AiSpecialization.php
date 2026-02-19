<?php

declare(strict_types=1);

namespace App\Modules\Ai\Contracts;

use App\Models\User;

/**
 * Contract for module-specific AI specializations.
 * Each ERP module can register an AiSpecialization to contribute
 * instructions and tools to the central AI Coach agent.
 */
interface AiSpecialization
{
    /**
     * Module slug this specialization belongs to.
     */
    public function moduleSlug(): string;

    /**
     * System prompt fragment for this specialization.
     */
    public function instructions(): string;

    /**
     * Tools this specialization provides for the given user.
     *
     * @return list<\Laravel\Ai\Contracts\Tool>
     */
    public function tools(User $user): array;
}
