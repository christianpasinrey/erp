<?php

declare(strict_types=1);

namespace App\Modules\Ai;

use App\Models\User;
use App\Modules\Ai\Contracts\AiSpecialization;
use App\Modules\ModuleRegistry;

/**
 * Central registry for AI specializations.
 * Composes instructions and tools from all active module specializations.
 */
class AiCoachRegistry
{
    /** @var list<AiSpecialization> */
    private array $specializations = [];

    public function __construct(
        private readonly ModuleRegistry $moduleRegistry,
    ) {}

    public function register(AiSpecialization $spec): void
    {
        $this->specializations[] = $spec;
    }

    /**
     * Get specializations for modules active on the current tenant.
     *
     * @return list<AiSpecialization>
     */
    public function forUser(User $user): array
    {
        return array_filter(
            $this->specializations,
            fn (AiSpecialization $spec) => $this->moduleRegistry->isActive($spec->moduleSlug())
                || $spec->moduleSlug() === 'ai', // AI base specialization is always available
        );
    }

    /**
     * Compose all system prompt instructions for the user.
     */
    public function composeInstructions(User $user): string
    {
        $parts = [];

        foreach ($this->forUser($user) as $spec) {
            $instructions = trim($spec->instructions());
            if ($instructions !== '') {
                $parts[] = $instructions;
            }
        }

        return implode("\n\n---\n\n", $parts);
    }

    /**
     * Compose all tools from active specializations for the user.
     *
     * @return list<\Laravel\Ai\Contracts\Tool>
     */
    public function composeTools(User $user): array
    {
        $tools = [];

        foreach ($this->forUser($user) as $spec) {
            foreach ($spec->tools($user) as $tool) {
                $tools[] = $tool;
            }
        }

        return $tools;
    }
}
