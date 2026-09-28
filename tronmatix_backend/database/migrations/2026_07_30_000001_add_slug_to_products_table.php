<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        // Backfill slugs for existing products
        $products = DB::table('products')->whereNull('slug')->get();
        $seen = [];
        foreach ($products as $product) {
            $base = Str::slug($product->name);
            $slug = $base;
            $i = 1;
            while (isset($seen[$slug])) {
                $slug = $base . '-' . $i++;
            }
            $seen[$slug] = true;

            // Also check DB for any collisions (edge case)
            while (DB::table('products')->where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $base . '-' . $i++;
            }

            DB::table('products')->where('id', $product->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
