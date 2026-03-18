<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('discount_type'); // 'percentage' or 'fixed_price'
            $table->decimal('discount_value', 10, 2);
            $table->boolean('is_active')->default(false);
            $table->json('active_days'); // e.g. [1,3,5] = Mon, Wed, Fri
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['restaurant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
