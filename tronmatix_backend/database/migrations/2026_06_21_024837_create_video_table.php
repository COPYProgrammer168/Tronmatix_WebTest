<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description')->nullable();

            // 'upload' | 'youtube' | 'facebook' | 'tiktok'
            $table->string('video_type')->default('upload');

            // File path when uploaded, or the full URL when embed (youtube/facebook/tiktok)
            $table->string('video');

            // Optional poster image shown before play (mainly useful for embeds)
            $table->string('thumbnail')->nullable();

            $table->foreignId('product_id')->nullable()
                ->constrained('products')->nullOnDelete();

            $table->unsignedInteger('order')->default(0);
            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
