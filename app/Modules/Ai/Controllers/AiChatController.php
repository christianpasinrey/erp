<?php

declare(strict_types=1);

namespace App\Modules\Ai\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ai\Agents\AiCoach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AiChatController extends Controller
{
    /**
     * Synchronous chat response.
     */
    public function chat(Request $request, AiCoach $agent): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'conversation_id' => ['nullable', 'string'],
        ]);

        $this->enforceRateLimit($request);

        $user = $request->user();
        $agent->forErpUser($user);

        if ($conversationId = $request->input('conversation_id')) {
            $agent->continue($conversationId, $user);
        } else {
            $agent->forUser($user);
        }

        $response = $agent->prompt($request->input('message'));

        $this->incrementUsage($request);

        return response()->json([
            'text' => $response->text,
            'conversation_id' => $response->conversationId,
            'usage' => [
                'prompt_tokens' => $response->usage->promptTokens,
                'completion_tokens' => $response->usage->completionTokens,
            ],
        ]);
    }

    /**
     * Streaming SSE chat response.
     */
    public function stream(Request $request, AiCoach $agent): mixed
    {
        $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'conversation_id' => ['nullable', 'string'],
        ]);

        $this->enforceRateLimit($request);

        $user = $request->user();
        $agent->forErpUser($user);

        if ($conversationId = $request->input('conversation_id')) {
            $agent->continue($conversationId, $user);
        } else {
            $agent->forUser($user);
        }

        $stream = $agent->stream($request->input('message'));

        $this->incrementUsage($request);

        return $stream->toResponse($request);
    }

    /**
     * Rate limit based on daily message count.
     */
    private function enforceRateLimit(Request $request): void
    {
        $userId = $request->user()->id;
        $key = "ai_messages:{$userId}:".now()->toDateString();
        $count = (int) Cache::get($key, 0);

        $dailyLimit = config('modules.ai.daily_message_limit', 100);

        if ($count >= $dailyLimit) {
            abort(429, 'Daily AI message limit reached. Please try again tomorrow.');
        }
    }

    private function incrementUsage(Request $request): void
    {
        $userId = $request->user()->id;
        $key = "ai_messages:{$userId}:".now()->toDateString();

        Cache::increment($key);
        Cache::put($key, Cache::get($key, 1), now()->endOfDay());
    }
}
