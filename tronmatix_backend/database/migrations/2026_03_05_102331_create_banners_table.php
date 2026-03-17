<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            // ── Content ───────────────────────────────────────────────────────
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('badge', 100)->nullable()
                ->comment('Small pill label e.g. "NEW ARRIVAL"');

            // ── Styling ───────────────────────────────────────────────────────
            $table->string('bg_color', 30)->default('#111111')
                ->comment('CSS color for banner background');
            $table->string('text_color', 30)->default('#F97316')
                ->comment('CSS color for headline text');

            // ── Media ─────────────────────────────────────────────────────────
            $table->string('image')->nullable()
                ->comment('Path: /storage/banners/xxx.jpg or full URL');

            // ── Display ───────────────────────────────────────────────────────
            $table->unsignedSmallInteger('order')->default(0)
                ->comment('Sort order — lower = displayed first');
            $table->boolean('active')->default(true)
                ->comment('false = hidden from storefront');

            $table->string('video')->nullable()->after('image')
                ->comment('Uploaded path /storage/banners/videos/x.mp4 OR YouTube/Vimeo/Facebook embed URL');

            // ── Video source type ─────────────────────────────────────────────
            $table->enum('video_type', ['upload', 'youtube', 'vimeo', 'facebook'])->nullable()->after('video')
                ->comment('"upload" = self-hosted file, "youtube"/"vimeo"/"facebook" = embed URL');

            $table->timestamps();

            $table->index('active');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
