<?php

declare(strict_types=1);

namespace App\Modules\Ai\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    /**
     * List user's conversations.
     */
    public function index(Request $request): JsonResponse
    {
        $conversations = DB::table('agent_conversations')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'created_at', 'updated_at']);

        return response()->json($conversations);
    }

    /**
     * Show a conversation with its messages.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $conversation = DB::table('agent_conversations')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $conversation) {
            abort(404, 'Conversation not found.');
        }

        $messages = DB::table('agent_conversation_messages')
            ->where('conversation_id', $id)
            ->orderBy('created_at')
            ->get(['id', 'role', 'content', 'created_at']);

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    /**
     * Update conversation title.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $affected = DB::table('agent_conversations')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->update(['title' => $request->input('title')]);

        if (! $affected) {
            abort(404, 'Conversation not found.');
        }

        return response()->json(['message' => 'Conversation updated.']);
    }

    /**
     * Delete a conversation and its messages.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $conversation = DB::table('agent_conversations')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $conversation) {
            abort(404, 'Conversation not found.');
        }

        DB::table('agent_conversation_messages')
            ->where('conversation_id', $id)
            ->delete();

        DB::table('agent_conversations')
            ->where('id', $id)
            ->delete();

        return response()->json(['message' => 'Conversation deleted.']);
    }
}
