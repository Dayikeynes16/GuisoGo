<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Phase 1 — Data Integrity Constraints
 *
 * 1. Change destructive CASCADE DELETE to SET NULL on FKs that link
 *    live catalog/branch/customer entities to historical order data.
 *    Order snapshots (product_name, unit_price, production_cost) ensure
 *    historical data is preserved even when the FK becomes NULL.
 *
 * 2. Add CHECK constraints for mutual exclusivity on order_items
 *    (product_id XOR promotion_id) and modifier_groups (at most one owner).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. Fix destructive CASCADE DELETE on order history FKs ──────

        // orders.branch_id: CASCADE → SET NULL (branch deletion must not delete orders)
        DB::statement('ALTER TABLE orders DROP CONSTRAINT orders_branch_id_foreign');
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_branch_id_foreign
            FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL');
        // Make column nullable (it was NOT NULL with CASCADE)
        DB::statement('ALTER TABLE orders ALTER COLUMN branch_id DROP NOT NULL');

        // orders.customer_id: CASCADE → SET NULL (customer deletion must not delete orders)
        DB::statement('ALTER TABLE orders DROP CONSTRAINT orders_customer_id_foreign');
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_customer_id_foreign
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL');
        DB::statement('ALTER TABLE orders ALTER COLUMN customer_id DROP NOT NULL');

        // order_items.product_id: CASCADE → SET NULL (product deletion must not delete order items)
        DB::statement('ALTER TABLE order_items DROP CONSTRAINT order_items_product_id_foreign');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_product_id_foreign
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL');

        // order_item_modifiers.modifier_option_id: CASCADE → SET NULL
        DB::statement('ALTER TABLE order_item_modifiers DROP CONSTRAINT order_item_modifiers_modifier_option_id_foreign');
        DB::statement('ALTER TABLE order_item_modifiers ADD CONSTRAINT order_item_modifiers_modifier_option_id_foreign
            FOREIGN KEY (modifier_option_id) REFERENCES modifier_options(id) ON DELETE SET NULL');
        DB::statement('ALTER TABLE order_item_modifiers ALTER COLUMN modifier_option_id DROP NOT NULL');

        // ─── 2. CHECK constraints for mutual exclusivity ─────────────────

        // Clean up orphaned order_items where both FKs are NULL (from deleted promos/products).
        // The snapshot fields (product_name, unit_price, production_cost) preserve the historical data.
        // We set promotion_id = 0 as a sentinel... Actually, better: relax the constraint to allow
        // both NULL for historical rows where the source entity was deleted.

        // order_items: at most one of product_id or promotion_id (both NULL allowed for historical orphans)
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_entity_check
            CHECK (
                NOT (product_id IS NOT NULL AND promotion_id IS NOT NULL)
            )');

        // modifier_groups: at most one owner (product or promotion)
        DB::statement('ALTER TABLE modifier_groups ADD CONSTRAINT modifier_groups_owner_check
            CHECK (
                NOT (product_id IS NOT NULL AND promotion_id IS NOT NULL)
            )');
    }

    public function down(): void
    {
        // Remove CHECK constraints
        DB::statement('ALTER TABLE order_items DROP CONSTRAINT IF EXISTS order_items_entity_check');
        DB::statement('ALTER TABLE modifier_groups DROP CONSTRAINT IF EXISTS modifier_groups_owner_check');

        // Restore CASCADE DELETE (reverse of up)
        DB::statement('ALTER TABLE order_item_modifiers ALTER COLUMN modifier_option_id SET NOT NULL');
        DB::statement('ALTER TABLE order_item_modifiers DROP CONSTRAINT order_item_modifiers_modifier_option_id_foreign');
        DB::statement('ALTER TABLE order_item_modifiers ADD CONSTRAINT order_item_modifiers_modifier_option_id_foreign
            FOREIGN KEY (modifier_option_id) REFERENCES modifier_options(id) ON DELETE CASCADE');

        DB::statement('ALTER TABLE order_items DROP CONSTRAINT order_items_product_id_foreign');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_product_id_foreign
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE');

        DB::statement('ALTER TABLE orders ALTER COLUMN customer_id SET NOT NULL');
        DB::statement('ALTER TABLE orders DROP CONSTRAINT orders_customer_id_foreign');
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_customer_id_foreign
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE');

        DB::statement('ALTER TABLE orders ALTER COLUMN branch_id SET NOT NULL');
        DB::statement('ALTER TABLE orders DROP CONSTRAINT orders_branch_id_foreign');
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_branch_id_foreign
            FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE');
    }
};
