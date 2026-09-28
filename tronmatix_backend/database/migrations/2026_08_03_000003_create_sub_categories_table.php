<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('main_category_id')->constrained('main_categories')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->unsignedSmallInteger('order')->default(0)
                ->comment('Sort order — lower = displayed first');
            $table->boolean('is_active')->default(true)
                ->comment('false = hidden from storefront / API');
            $table->timestamps();

            $table->index('main_category_id');
            $table->index('is_active');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
    }
};
