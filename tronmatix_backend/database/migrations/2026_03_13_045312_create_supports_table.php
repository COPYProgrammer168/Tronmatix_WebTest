<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── delivery_schedules ────────────────────────────────────────────────
        Schema::create('delivery_schedules', function (Blueprint $table) {
            $table->id();

            $table->tinyInteger('day_of_week')
                ->comment('0 = Sunday, 1 = Monday … 6 = Saturday');
            $table->time('time_start')
                ->comment('Slot opens e.g. "08:00:00"');
            $table->time('time_end')
                ->comment('Slot closes e.g. "12:00:00"');
            $table->boolean('is_available')->default(true)
                ->comment('false = blocked (holiday, fully booked, etc.)');

            $table->timestamps();

            $table->index(['day_of_week', 'is_available']);
        });

        // ── chat_sessions ─────────────────────────────────────────────────────
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()
                ->comment('NULL = guest/unauthenticated visitor');
            $table->enum('status', ['open', 'closed'])->default('open')
                ->comment('"open" = active conversation, "closed" = resolved');

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });

        // ── chat_messages ─────────────────────────────────────────────────────
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')
                ->constrained('chat_sessions')->cascadeOnDelete();
            $table->enum('sender_type', ['user', 'agent', 'bot'])
                ->comment('"user" = customer, "agent" = staff, "bot" = auto-reply');
            $table->text('message');
            $table->timestamp('sent_at')->useCurrent()
                ->comment('Message send timestamp (defaults to now)');

            $table->timestamps();

            $table->index(['session_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_sessions');
        Schema::dropIfExists('delivery_schedules');
    }
};
