<?php

declare(strict_types=1);

namespace App\Modules\Ai\Specializations;

use App\Models\User;
use App\Modules\Ai\Contracts\AiSpecialization;
use App\Modules\Ai\Tools\GetActiveModules;
use App\Modules\Ai\Tools\GetCompanyInfo;
use App\Modules\Ai\Tools\GetCurrentUser;

/**
 * Base AI specialization that provides core ERP tools.
 * Always active when the AI module is enabled.
 */
class BaseAiSpecialization implements AiSpecialization
{
    public function moduleSlug(): string
    {
        return 'ai';
    }

    public function instructions(): string
    {
        return <<<'PROMPT'
You have access to base ERP tools:
- GetCompanyInfo: Retrieve details about the active company.
- GetCurrentUser: Get information about the authenticated user, their roles, and permissions.
- GetActiveModules: List all available modules and their activation status.

Use these tools to provide context-aware responses.
PROMPT;
    }

    public function tools(User $user): array
    {
        return [
            app(GetCompanyInfo::class),
            app(GetActiveModules::class),
            app(GetCurrentUser::class),
        ];
    }
}
