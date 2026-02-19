<?php

declare(strict_types=1);

namespace App\Modules\Ai\Agents;

use App\Models\User;
use App\Modules\Ai\AiCoachRegistry;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

#[MaxTokens(4096)]
#[Timeout(180)]
class AiCoach implements Agent, Conversational, HasTools
{
    use Promptable;
    use RemembersConversations;

    private ?User $currentUser = null;

    public function __construct(
        private readonly AiCoachRegistry $registry,
    ) {}

    public function forErpUser(User $user): static
    {
        $this->currentUser = $user;

        return $this->forUser($user);
    }

    public function instructions(): string
    {
        $base = <<<'PROMPT'
You are the ERP AI Assistant, an intelligent helper integrated into a modular enterprise resource planning platform.

Core capabilities:
- You can assist with company operations, data lookup, and general business questions.
- You have access to tools that let you query company data, user information, and active modules.
- Always respond in the same language the user writes in.
- Be concise and professional. Focus on actionable information.
- When you don't have enough data, use your available tools to gather it.
- Never fabricate data — if you can't find something, say so.

Context:
- This is a multi-tenant, multi-company ERP system.
- Each user may have access to multiple companies within their tenant.
- Modules can be activated/deactivated per tenant.
PROMPT;

        if ($this->currentUser) {
            $composed = $this->registry->composeInstructions($this->currentUser);
            if ($composed !== '') {
                $base .= "\n\n--- Module-specific instructions ---\n\n".$composed;
            }
        }

        return $base;
    }

    public function tools(): iterable
    {
        if (! $this->currentUser) {
            return [];
        }

        return $this->registry->composeTools($this->currentUser);
    }
}
