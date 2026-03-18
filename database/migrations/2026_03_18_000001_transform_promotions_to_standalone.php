<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
            $table->decimal('price', 10, 2)->after('description');
            $table->decimal('production_cost', 10, 2)->default(0)->after('price');
            $table->string('image_path')->nullable()->after('production_cost');
        });

        Schema::dropIfExists('promotion_product');
    }

    public function down(): void
    {
        Schema::create('promotion_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unique(['promotion_id', 'product_id']);
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn(['price', 'production_cost', 'image_path']);
            $table->string('discount_type')->after('name');
            $table->decimal('discount_value', 10, 2)->after('discount_type');
        });
    }
};
