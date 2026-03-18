<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Make product_id nullable (promotion items won't have a product).
            $table->unsignedBigInteger('product_id')->nullable()->change();
            // Add promotion_id for items that come from promotions.
            $table->foreignId('promotion_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropColumn('promotion_id');
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
        });
    }
};
