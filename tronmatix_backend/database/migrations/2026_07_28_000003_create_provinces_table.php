<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name_en', 100);
            $table->string('name_kh', 100);
            $table->unsignedBigInteger('delivery_zone_id')->nullable();
            $table->timestamps();
            $table->index('delivery_zone_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
