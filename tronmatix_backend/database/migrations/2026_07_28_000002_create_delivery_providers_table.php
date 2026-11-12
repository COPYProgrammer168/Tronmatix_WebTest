<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_zone_id')->constrained('delivery_zones')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('logo')->nullable()->comment('Path: /storage/delivery-providers/xxx.jpg or full URL');
            $table->decimal('fee', 10, 2)->nullable()->comment('NULL = negotiable / varies');
            $table->string('estimated_time', 100)->nullable()->comment('e.g. "30–60 min" or "1–2 days"');
            $table->boolean('is_active')->default(true)->comment('false = hidden from storefront / API');
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Lower = displayed first');
            $table->timestamps();
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_providers');
    }
};
