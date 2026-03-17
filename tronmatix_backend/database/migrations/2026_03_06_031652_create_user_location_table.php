<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name', 100)
                ->comment('Address label: "Home", "Office", etc.');
            $table->string('phone', 30)
                ->comment('Contact phone for delivery to this address');
            $table->string('address', 500)
                ->comment('Full street/building address');
            $table->string('city', 100)->default('Phnom Penh');
            $table->string('country', 100)->default('Cambodia')
                ->comment('Country — included in toShippingArray() and order snapshot');
            $table->string('note', 255)->nullable()
                ->comment('Delivery instructions: gate code, floor, landmark');
            $table->boolean('is_default')->default(false)
                ->comment('true = pre-selected in checkout; one per user at a time');

            $table->timestamps();

            $table->index(['user_id', 'is_default']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_locations');
    }
};
