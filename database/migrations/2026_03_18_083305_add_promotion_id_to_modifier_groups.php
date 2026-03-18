<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modifier_groups', function (Blueprint $table) {
            // Make product_id nullable (modifier groups can belong to a promotion instead).
            $table->unsignedBigInteger('product_id')->nullable()->change();

            // Add promotion_id FK — a modifier group belongs to a product OR a promotion.
            $table->foreignId('promotion_id')->nullable()->after('product_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('modifier_groups', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropColumn('promotion_id');
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
        });
    }
};
