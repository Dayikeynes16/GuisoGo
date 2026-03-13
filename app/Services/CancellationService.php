<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CancellationService
{
    /** @return array<string, mixed> */
    public function getData(Restaurant $restaurant, Carbon $from, Carbon $to, ?int $branchId = null): array
    {
        $restaurantId = $restaurant->id;

        $cancelledCount = $this->cancelledCount($restaurantId, $from, $to, $branchId);
        $totalCount = $this->totalOrdersCount($restaurantId, $from, $to, $branchId);
        $reasons = $this->reasonsBreakdown($restaurantId, $from, $to, $branchId);

        return [
            'cancelled_count' => $cancelledCount,
            'total_orders_count' => $totalCount,
            'cancellation_rate' => $totalCount > 0 ? round(($cancelledCount / $totalCount) * 100, 1) : 0,
            'top_reason' => $reasons->first()['reason'] ?? null,
            'reasons_breakdown' => $reasons,
            'by_branch' => $this->byBranch($restaurantId, $from, $to),
            'by_day' => $this->byDay($restaurantId, $from, $to, $branchId),
            'cancelled_orders' => $this->cancelledOrders($restaurantId, $from, $to, $branchId),
        ];
    }

    private function cancelledQuery(int $restaurantId, Carbon $from, Carbon $to, ?int $branchId = null): Builder
    {
        return Order::query()
            ->where('restaurant_id', $restaurantId)
            ->where('status', 'cancelled')
            ->whereBetween('created_at', [$from, $to])
            ->when($branchId, fn (Builder $q, int $id) => $q->where('branch_id', $id));
    }

    private function cancelledCount(int $restaurantId, Carbon $from, Carbon $to, ?int $branchId): int
    {
        return $this->cancelledQuery($restaurantId, $from, $to, $branchId)->count();
    }

    private function totalOrdersCount(int $restaurantId, Carbon $from, Carbon $to, ?int $branchId): int
    {
        return Order::query()
            ->where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$from, $to])
            ->when($branchId, fn (Builder $q, int $id) => $q->where('branch_id', $id))
            ->count();
    }

    /** @return Collection<int, array{reason: string, count: int, percentage: float}> */
    private function reasonsBreakdown(int $restaurantId, Carbon $from, Carbon $to, ?int $branchId): Collection
    {
        $rows = $this->cancelledQuery($restaurantId, $from, $to, $branchId)
            ->selectRaw("COALESCE(cancellation_reason, 'Sin motivo especificado') as reason, COUNT(*) as count")
            ->groupBy('reason')
            ->orderByDesc('count')
            ->get();

        $total = $rows->sum('count');

        return $rows->map(fn ($row) => [
            'reason' => $row->reason,
            'count' => (int) $row->count,
            'percentage' => $total > 0 ? round(($row->count / $total) * 100, 1) : 0,
        ])->values();
    }

    /** @return Collection<int, array{id: int, name: string, count: int}> */
    private function byBranch(int $restaurantId, Carbon $from, Carbon $to): Collection
    {
        return Branch::query()
            ->where('restaurant_id', $restaurantId)
            ->withCount(['orders' => fn (Builder $q) => $q->where('status', 'cancelled')->whereBetween('created_at', [$from, $to])])
            ->get()
            ->map(fn (Branch $b) => ['id' => $b->id, 'name' => $b->name, 'count' => $b->orders_count])
            ->sortByDesc('count')
            ->values();
    }

    /** @return Collection<int, array{date: string, count: int}> */
    private function byDay(int $restaurantId, Carbon $from, Carbon $to, ?int $branchId): Collection
    {
        return $this->cancelledQuery($restaurantId, $from, $to, $branchId)
            ->selectRaw('DATE(cancelled_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'count' => (int) $row->count,
            ])
            ->values();
    }

    /** @return Collection<int, mixed> */
    private function cancelledOrders(int $restaurantId, Carbon $from, Carbon $to, ?int $branchId): Collection
    {
        return $this->cancelledQuery($restaurantId, $from, $to, $branchId)
            ->with(['customer:id,name,phone', 'branch:id,name'])
            ->latest('cancelled_at')
            ->limit(50)
            ->get(['id', 'customer_id', 'branch_id', 'total', 'cancellation_reason', 'cancelled_at', 'created_at']);
    }
}
