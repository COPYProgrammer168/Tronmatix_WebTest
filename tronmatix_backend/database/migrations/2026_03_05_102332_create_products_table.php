<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // ── Core info ─────────────────────────────────────────────────────
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('category', 100);
            $table->string('brand', 100)->nullable();

            // ── Images ────────────────────────────────────────────────────────
            $table->string('image')->nullable()
                ->comment('Primary image — path under storage/ or full URL');
            $table->string('image_disk', 20)->nullable()
                ->comment('"local" = stored in /storage, "url" = external URL');
            $table->json('images')->nullable()
                ->comment('Array of additional image paths/URLs for gallery');

            // ── Specs & inventory ─────────────────────────────────────────────
            $table->json('specs')->nullable()
                ->comment('Key-value pairs: {"RAM":"16GB","CPU":"i7"}');
            $table->integer('stock')->default(0);

            // ── Merchandising flags ───────────────────────────────────────────
            $table->decimal('rating', 3, 1)->default(0.0)
                ->comment('Average star rating 0.0–5.0');
            $table->boolean('is_featured')->default(false)
                ->comment('Show in featured section on homepage');
            $table->boolean('is_hot')->default(false)
                ->comment('Show "HOT" badge on product card');

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index('category');
            $table->index('brand');
            $table->index('stock');
            $table->index('is_featured');
            $table->index('is_hot');
        });

        // Backfill images[] from existing image (safe on fresh install — no rows)
        DB::table('products')
            ->whereNotNull('image')
            ->whereNull('images')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('products')->where('id', $row->id)
                        ->update(['images' => json_encode([$row->image])]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
