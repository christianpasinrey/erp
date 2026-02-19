<script setup lang="ts">
import { ref, nextTick, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import TenantLayout from '@/layouts/tenant/TenantLayout.vue';
import { Bot, Send, Plus, Trash2, MessageSquare, Loader2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

type Message = {
    id: string;
    role: 'user' | 'assistant';
    content: string;
    created_at?: string;
};

type Conversation = {
    id: string;
    title: string | null;
    updated_at: string;
};

const conversations = ref<Conversation[]>([]);
const currentConversationId = ref<string | null>(null);
const messages = ref<Message[]>([]);
const inputText = ref('');
const isLoading = ref(false);
const isStreaming = ref(false);
const messagesContainer = ref<HTMLElement | null>(null);

onMounted(() => {
    loadConversations();
});

async function loadConversations() {
    try {
        const res = await fetch('/ai/conversations');
        conversations.value = await res.json();
    } catch {
        // silently fail
    }
}

async function loadConversation(id: string) {
    currentConversationId.value = id;
    try {
        const res = await fetch(`/ai/conversations/${id}`);
        const data = await res.json();
        messages.value = data.messages.map((m: any) => ({
            id: m.id,
            role: m.role,
            content: m.content,
            created_at: m.created_at,
        }));
        await scrollToBottom();
    } catch {
        // silently fail
    }
}

function startNewConversation() {
    currentConversationId.value = null;
    messages.value = [];
    inputText.value = '';
}

async function deleteConversation(id: string) {
    try {
        await fetch(`/ai/conversations/${id}`, { method: 'DELETE', headers: { 'X-XSRF-TOKEN': getCsrfToken() } });
        conversations.value = conversations.value.filter(c => c.id !== id);
        if (currentConversationId.value === id) {
            startNewConversation();
        }
    } catch {
        // silently fail
    }
}

async function sendMessage() {
    const text = inputText.value.trim();
    if (!text || isLoading.value) return;

    inputText.value = '';

    // Add user message
    messages.value.push({
        id: `temp-${Date.now()}`,
        role: 'user',
        content: text,
    });

    await scrollToBottom();

    // Add placeholder for assistant
    const assistantId = `assistant-${Date.now()}`;
    messages.value.push({
        id: assistantId,
        role: 'assistant',
        content: '',
    });

    isLoading.value = true;
    isStreaming.value = true;

    try {
        const res = await fetch('/ai/chat/stream', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': getCsrfToken(),
                'Accept': 'text/event-stream',
            },
            body: JSON.stringify({
                message: text,
                conversation_id: currentConversationId.value,
            }),
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({ message: 'Request failed' }));
            updateLastAssistantMessage(err.message || 'An error occurred.');
            return;
        }

        const reader = res.body?.getReader();
        const decoder = new TextDecoder();

        if (!reader) {
            updateLastAssistantMessage('Streaming not supported.');
            return;
        }

        let buffer = '';
        let fullText = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });

            const lines = buffer.split('\n');
            buffer = lines.pop() ?? '';

            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    const data = line.slice(6).trim();
                    if (data === '[DONE]') continue;

                    try {
                        const event = JSON.parse(data);
                        if (event.delta) {
                            fullText += event.delta;
                            updateLastAssistantMessage(fullText);
                            await scrollToBottom();
                        }
                        if (event.conversationId && !currentConversationId.value) {
                            currentConversationId.value = event.conversationId;
                        }
                    } catch {
                        // ignore parse errors
                    }
                }
            }
        }

        // Reload conversations to update sidebar
        await loadConversations();
    } catch {
        updateLastAssistantMessage('Failed to connect to AI service.');
    } finally {
        isLoading.value = false;
        isStreaming.value = false;
    }
}

function updateLastAssistantMessage(content: string) {
    const lastMsg = messages.value[messages.value.length - 1];
    if (lastMsg && lastMsg.role === 'assistant') {
        lastMsg.content = content;
    }
}

async function scrollToBottom() {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
}

function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}
</script>

<template>
    <Head title="AI Coach" />

    <TenantLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }, { title: 'AI Coach' }]">
        <div class="flex h-[calc(100vh-3.5rem)] p-4 gap-4">
            <!-- Conversations sidebar -->
            <div class="w-72 flex-shrink-0">
                <div class="liquid-glass liquid-glass-panel flex h-full flex-col rounded-2xl p-3">
                    <Button
                        @click="startNewConversation"
                        class="mb-3 w-full justify-start gap-2"
                        variant="ghost"
                    >
                        <Plus class="size-4" />
                        New Chat
                    </Button>

                    <div class="flex-1 overflow-y-auto glass-scroll space-y-1">
                        <button
                            v-for="conv in conversations"
                            :key="conv.id"
                            @click="loadConversation(conv.id)"
                            class="group flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"
                            :class="currentConversationId === conv.id
                                ? 'bg-white/10 text-white'
                                : 'text-white/60 hover:bg-white/5 hover:text-white/90'"
                        >
                            <MessageSquare class="size-4 flex-shrink-0" />
                            <span class="flex-1 truncate text-left">{{ conv.title || 'Untitled' }}</span>
                            <button
                                @click.stop="deleteConversation(conv.id)"
                                class="opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                                <Trash2 class="size-3.5 text-white/40 hover:text-red-400" />
                            </button>
                        </button>

                        <p v-if="conversations.length === 0" class="px-3 py-4 text-center text-xs text-white/40">
                            No conversations yet
                        </p>
                    </div>
                </div>
            </div>

            <!-- Chat area -->
            <div class="flex flex-1 flex-col">
                <div class="liquid-glass liquid-glass-card flex flex-1 flex-col rounded-2xl overflow-hidden">
                    <!-- Messages -->
                    <div ref="messagesContainer" class="flex-1 overflow-y-auto glass-scroll p-6 space-y-4">
                        <!-- Empty state -->
                        <div v-if="messages.length === 0" class="flex h-full items-center justify-center">
                            <div class="text-center">
                                <Bot class="mx-auto size-12 text-white/20 mb-4" />
                                <h2 class="text-lg font-medium text-white/60 mb-2">ERP AI Coach</h2>
                                <p class="text-sm text-white/40 max-w-md">
                                    Ask me about your company data, modules, or any business question. I have access to your ERP context.
                                </p>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div
                            v-for="msg in messages"
                            :key="msg.id"
                            class="flex gap-3"
                            :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
                        >
                            <!-- Assistant avatar -->
                            <div v-if="msg.role === 'assistant'" class="flex size-8 flex-shrink-0 items-center justify-center rounded-full bg-white/10">
                                <Bot class="size-4 text-white/60" />
                            </div>

                            <!-- Message bubble -->
                            <div
                                class="max-w-[70%] rounded-2xl px-4 py-3 text-sm leading-relaxed"
                                :class="msg.role === 'user'
                                    ? 'bg-primary/20 text-white'
                                    : 'bg-white/5 text-white/90'"
                            >
                                <div v-if="msg.content" class="whitespace-pre-wrap">{{ msg.content }}</div>
                                <div v-else class="flex items-center gap-2 text-white/40">
                                    <Loader2 class="size-4 animate-spin" />
                                    <span>Thinking...</span>
                                </div>
                            </div>

                            <!-- User avatar -->
                            <div v-if="msg.role === 'user'" class="flex size-8 flex-shrink-0 items-center justify-center rounded-full bg-primary/20">
                                <span class="text-xs font-semibold text-white">You</span>
                            </div>
                        </div>
                    </div>

                    <!-- Input area -->
                    <div class="border-t border-white/5 p-4">
                        <div class="flex items-end gap-3">
                            <textarea
                                v-model="inputText"
                                @keydown="handleKeydown"
                                placeholder="Type your message..."
                                rows="1"
                                class="flex-1 resize-none rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-white/30 focus:border-white/20 focus:outline-none focus:ring-0"
                                :disabled="isLoading"
                            />
                            <Button
                                @click="sendMessage"
                                :disabled="!inputText.trim() || isLoading"
                                size="icon"
                                class="size-11 rounded-xl"
                            >
                                <Send class="size-4" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
