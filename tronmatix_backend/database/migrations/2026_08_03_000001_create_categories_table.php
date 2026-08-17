<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('image')->nullable()
                ->comment('Path: /storage/categories/xxx.jpg or full URL');
            $table->unsignedSmallInteger('order')->default(0)
                ->comment('Sort order — lower = displayed first');
            $table->boolean('is_active')->default(true)
                ->comment('false = hidden from storefront / API');
            $table->timestamps();

            $table->index('is_active');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
