<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conversations table (used by laravel/ai RemembersConversations)
        if (! Schema::hasTable('agent_conversations')) {
            Schema::create('agent_conversations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('title')->nullable();
                $table->timestamps();

                $table->index('user_id');
            });
        }

        // Conversation messages
        if (! Schema::hasTable('agent_conversation_messages')) {
            Schema::create('agent_conversation_messages', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('conversation_id');
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('role', 20); // user, assistant, tool_result
                $table->longText('content')->nullable();
                $table->json('attachments')->nullable();
                $table->json('tool_calls')->nullable();
                $table->json('tool_results')->nullable();
                $table->timestamps();

                $table->foreign('conversation_id')
                    ->references('id')
                    ->on('agent_conversations')
                    ->cascadeOnDelete();

                $table->index('conversation_id');
            });
        }

        // AI usage tracking
        if (! Schema::hasTable('ai_usage_logs')) {
            Schema::create('ai_usage_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->uuid('conversation_id')->nullable();
                $table->string('provider', 50)->nullable();
                $table->string('model', 100)->nullable();
                $table->unsignedInteger('input_tokens')->default(0);
                $table->unsignedInteger('output_tokens')->default(0);
                $table->unsignedInteger('cost_cents')->default(0);
                $table->timestamp('created_at')->useCurrent();

                $table->index(['user_id', 'created_at']);
                $table->index(['company_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('agent_conversation_messages');
        Schema::dropIfExists('agent_conversations');
    }
};
