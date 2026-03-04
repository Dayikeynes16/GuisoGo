<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add restaurant_id to modifier_groups (nullable first for backfill)
        Schema::table('modifier_groups', function (Blueprint $table): void {
            $table->foreignId('restaurant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // 2. Backfill restaurant_id from product → restaurant_id
        $groups = DB::table('modifier_groups')->whereNotNull('product_id')->get();
        foreach ($groups as $group) {
            $restaurantId = DB::table('products')->where('id', $group->product_id)->value('restaurant_id');
            if ($restaurantId) {
                DB::table('modifier_groups')->where('id', $group->id)->update(['restaurant_id' => $restaurantId]);
            }
        }

        // 3. Make restaurant_id not nullable
        Schema::table('modifier_groups', function (Blueprint $table): void {
            $table->foreignId('restaurant_id')->nullable(false)->change();
        });

        // 4. Create pivot table
        Schema::create('modifier_group_product', function (Blueprint $table): void {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('modifier_group_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'modifier_group_id']);
        });

        // 5. Backfill pivot from existing product_id column
        DB::statement('
            INSERT INTO modifier_group_product (product_id, modifier_group_id)
            SELECT product_id, id FROM modifier_groups
            WHERE product_id IS NOT NULL
        ');

        // 6. Drop product_id column from modifier_groups
        Schema::table('modifier_groups', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('product_id');
        });
    }

    public function down(): void
    {
        // 1. Re-add product_id column
        Schema::table('modifier_groups', function (Blueprint $table): void {
            $table->foreignId('product_id')->nullable()->after('restaurant_id')->constrained()->cascadeOnDelete();
        });

        // 2. Restore product_id from pivot (pick first product)
        $pivotRows = DB::table('modifier_group_product')
            ->selectRaw('modifier_group_id, MIN(product_id) as product_id')
            ->groupBy('modifier_group_id')
            ->get();
        foreach ($pivotRows as $row) {
            DB::table('modifier_groups')->where('id', $row->modifier_group_id)->update(['product_id' => $row->product_id]);
        }

        // 3. Drop pivot table
        Schema::dropIfExists('modifier_group_product');

        // 4. Drop restaurant_id
        Schema::table('modifier_groups', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('restaurant_id');
        });
    }
};
