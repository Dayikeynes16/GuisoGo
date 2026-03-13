<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancellationTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminWithRestaurant(): array
    {
        $restaurant = Restaurant::factory()->create(['orders_limit' => 500]);
        $user = User::factory()->create(['restaurant_id' => $restaurant->id]);

        return [$user, $restaurant];
    }

    private function createOrder(int $restaurantId, ?int $branchId = null, array $overrides = []): Order
    {
        $branch = $branchId
            ? Branch::find($branchId)
            : Branch::factory()->create(['restaurant_id' => $restaurantId]);
        $customer = Customer::factory()->create();

        return Order::factory()->create(array_merge([
            'restaurant_id' => $restaurantId,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
        ], $overrides));
    }

    // ─── Auth ────────────────────────────────────────────────────────────────────

    public function test_unauthenticated_user_redirected_from_cancellations(): void
    {
        $response = $this->get(route('cancellations.index'));

        $response->assertRedirect(route('login'));
    }

    // ─── Page renders ────────────────────────────────────────────────────────────

    public function test_admin_can_view_cancellations_page(): void
    {
        [$user] = $this->createAdminWithRestaurant();

        $response = $this->withoutVite()->actingAs($user)->get(route('cancellations.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Cancellations/Index'));
    }

    public function test_cancellations_page_has_expected_props(): void
    {
        [$user] = $this->createAdminWithRestaurant();

        $response = $this->withoutVite()->actingAs($user)->get(route('cancellations.index'));

        $response->assertInertia(fn ($page) => $page
            ->component('Cancellations/Index')
            ->has('cancelled_count')
            ->has('total_orders_count')
            ->has('cancellation_rate')
            ->has('top_reason')
            ->has('reasons_breakdown')
            ->has('by_branch')
            ->has('by_day')
            ->has('cancelled_orders')
            ->has('branches')
            ->has('filters')
        );
    }

    // ─── KPI counts ──────────────────────────────────────────────────────────────

    public function test_cancelled_count_and_rate_are_correct(): void
    {
        [$user, $restaurant] = $this->createAdminWithRestaurant();

        $this->createOrder($restaurant->id, overrides: ['status' => 'cancelled', 'cancellation_reason' => 'Motivo', 'cancelled_at' => now()]);
        $this->createOrder($restaurant->id, overrides: ['status' => 'cancelled', 'cancellation_reason' => 'Motivo', 'cancelled_at' => now()]);
        $this->createOrder($restaurant->id, overrides: ['status' => 'cancelled', 'cancellation_reason' => 'Motivo', 'cancelled_at' => now()]);
        $this->createOrder($restaurant->id, overrides: ['status' => 'delivered']);
        $this->createOrder($restaurant->id, overrides: ['status' => 'received']);

        $response = $this->withoutVite()->actingAs($user)->get(route('cancellations.index'));

        $response->assertInertia(fn ($page) => $page
            ->where('cancelled_count', 3)
            ->where('total_orders_count', 5)
            ->where('cancellation_rate', 60)
        );
    }

    // ─── Multitenancy ────────────────────────────────────────────────────────────

    public function test_cancellation_data_scoped_to_restaurant(): void
    {
        [$user, $restaurant] = $this->createAdminWithRestaurant();

        $this->createOrder($restaurant->id, overrides: ['status' => 'cancelled', 'cancellation_reason' => 'Motivo', 'cancelled_at' => now()]);

        $other = Restaurant::factory()->create();
        $this->createOrder($other->id, overrides: ['status' => 'cancelled', 'cancellation_reason' => 'Otro motivo', 'cancelled_at' => now()]);

        $response = $this->withoutVite()->actingAs($user)->get(route('cancellations.index'));

        $response->assertInertia(fn ($page) => $page
            ->where('cancelled_count', 1)
            ->where('total_orders_count', 1)
        );
    }

    // ─── Date filter ─────────────────────────────────────────────────────────────

    public function test_cancellations_filter_by_date_range(): void
    {
        [$user, $restaurant] = $this->createAdminWithRestaurant();

        $this->createOrder($restaurant->id, overrides: [
            'status' => 'cancelled',
            'cancellation_reason' => 'Motivo',
            'cancelled_at' => now(),
            'created_at' => now()->subDays(3),
        ]);
        $this->createOrder($restaurant->id, overrides: [
            'status' => 'cancelled',
            'cancellation_reason' => 'Motivo',
            'cancelled_at' => now(),
        ]);

        $response = $this->withoutVite()->actingAs($user)->get(route('cancellations.index', [
            'from' => now()->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $response->assertInertia(fn ($page) => $page->where('cancelled_count', 1));
    }

    // ─── Branch filter ───────────────────────────────────────────────────────────

    public function test_cancellations_filter_by_branch(): void
    {
        [$user, $restaurant] = $this->createAdminWithRestaurant();

        $branchA = Branch::factory()->create(['restaurant_id' => $restaurant->id]);
        $branchB = Branch::factory()->create(['restaurant_id' => $restaurant->id]);

        $this->createOrder($restaurant->id, $branchA->id, ['status' => 'cancelled', 'cancellation_reason' => 'Motivo', 'cancelled_at' => now()]);
        $this->createOrder($restaurant->id, $branchA->id, ['status' => 'cancelled', 'cancellation_reason' => 'Motivo', 'cancelled_at' => now()]);
        $this->createOrder($restaurant->id, $branchB->id, ['status' => 'cancelled', 'cancellation_reason' => 'Motivo', 'cancelled_at' => now()]);

        $response = $this->withoutVite()->actingAs($user)->get(route('cancellations.index', [
            'branch_id' => $branchA->id,
        ]));

        $response->assertInertia(fn ($page) => $page->where('cancelled_count', 2));
    }

    // ─── Reasons ─────────────────────────────────────────────────────────────────

    public function test_reasons_breakdown_groups_correctly(): void
    {
        [$user, $restaurant] = $this->createAdminWithRestaurant();

        $this->createOrder($restaurant->id, overrides: ['status' => 'cancelled', 'cancellation_reason' => 'Sin stock', 'cancelled_at' => now()]);
        $this->createOrder($restaurant->id, overrides: ['status' => 'cancelled', 'cancellation_reason' => 'Sin stock', 'cancelled_at' => now()]);
        $this->createOrder($restaurant->id, overrides: ['status' => 'cancelled', 'cancellation_reason' => 'Cliente no contesta', 'cancelled_at' => now()]);

        $response = $this->withoutVite()->actingAs($user)->get(route('cancellations.index'));

        $response->assertInertia(fn ($page) => $page
            ->has('reasons_breakdown', 2)
            ->where('reasons_breakdown.0.reason', 'Sin stock')
            ->where('reasons_breakdown.0.count', 2)
            ->where('reasons_breakdown.1.reason', 'Cliente no contesta')
            ->where('reasons_breakdown.1.count', 1)
        );
    }

    public function test_top_reason_is_most_frequent(): void
    {
        [$user, $restaurant] = $this->createAdminWithRestaurant();

        $this->createOrder($restaurant->id, overrides: ['status' => 'cancelled', 'cancellation_reason' => 'Poco frecuente', 'cancelled_at' => now()]);
        $this->createOrder($restaurant->id, overrides: ['status' => 'cancelled', 'cancellation_reason' => 'Mas comun', 'cancelled_at' => now()]);
        $this->createOrder($restaurant->id, overrides: ['status' => 'cancelled', 'cancellation_reason' => 'Mas comun', 'cancelled_at' => now()]);

        $response = $this->withoutVite()->actingAs($user)->get(route('cancellations.index'));

        $response->assertInertia(fn ($page) => $page->where('top_reason', 'Mas comun'));
    }

    // ─── By branch ───────────────────────────────────────────────────────────────

    public function test_cancellations_by_branch_includes_all_branches(): void
    {
        [$user, $restaurant] = $this->createAdminWithRestaurant();

        $branchA = Branch::factory()->create(['restaurant_id' => $restaurant->id, 'name' => 'Centro']);
        $branchB = Branch::factory()->create(['restaurant_id' => $restaurant->id, 'name' => 'Norte']);

        $this->createOrder($restaurant->id, $branchA->id, ['status' => 'cancelled', 'cancellation_reason' => 'Motivo', 'cancelled_at' => now()]);
        $this->createOrder($restaurant->id, $branchA->id, ['status' => 'cancelled', 'cancellation_reason' => 'Motivo', 'cancelled_at' => now()]);

        $response = $this->withoutVite()->actingAs($user)->get(route('cancellations.index'));

        $response->assertInertia(fn ($page) => $page
            ->has('by_branch', 2)
            ->where('by_branch.0.name', 'Centro')
            ->where('by_branch.0.count', 2)
            ->where('by_branch.1.name', 'Norte')
            ->where('by_branch.1.count', 0)
        );
    }

    // ─── Orders list ─────────────────────────────────────────────────────────────

    public function test_cancelled_orders_list_includes_relations(): void
    {
        [$user, $restaurant] = $this->createAdminWithRestaurant();

        $this->createOrder($restaurant->id, overrides: ['status' => 'cancelled', 'cancellation_reason' => 'Motivo', 'cancelled_at' => now()]);

        $response = $this->withoutVite()->actingAs($user)->get(route('cancellations.index'));

        $response->assertInertia(fn ($page) => $page
            ->has('cancelled_orders', 1)
            ->has('cancelled_orders.0.customer')
            ->has('cancelled_orders.0.branch')
            ->has('cancelled_orders.0.cancellation_reason')
            ->has('cancelled_orders.0.cancelled_at')
        );
    }

    // ─── Empty state ─────────────────────────────────────────────────────────────

    public function test_no_cancellations_returns_zero_values(): void
    {
        [$user, $restaurant] = $this->createAdminWithRestaurant();

        $this->createOrder($restaurant->id, overrides: ['status' => 'delivered']);

        $response = $this->withoutVite()->actingAs($user)->get(route('cancellations.index'));

        $response->assertInertia(fn ($page) => $page
            ->where('cancelled_count', 0)
            ->where('total_orders_count', 1)
            ->where('cancellation_rate', 0)
            ->where('top_reason', null)
            ->has('reasons_breakdown', 0)
            ->has('cancelled_orders', 0)
        );
    }
}
