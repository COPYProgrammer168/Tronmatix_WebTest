<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_invite_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_invite_id')->constrained('staff_invites')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['staff_invite_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_invite_links');
    }
};
