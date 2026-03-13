<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Restaurant;

class LimitService
{
    public function isOrderLimitReached(Restaurant $restaurant): bool
    {
        return $this->limitReason($restaurant) !== null;
    }

    /**
     * @return 'period_not_started'|'period_expired'|'limit_reached'|null
     */
    public function limitReason(Restaurant $restaurant): ?string
    {
        if (! $restaurant->orders_limit_start || ! $restaurant->orders_limit_end) {
            return null;
        }

        if (now()->startOfDay()->lessThan($restaurant->orders_limit_start)) {
            return 'period_not_started';
        }

        if (now()->startOfDay()->greaterThan($restaurant->orders_limit_end)) {
            return 'period_expired';
        }

        $count = Order::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [
                $restaurant->orders_limit_start->startOfDay(),
                $restaurant->orders_limit_end->endOfDay(),
            ])
            ->count();

        return $count >= $restaurant->orders_limit ? 'limit_reached' : null;
    }

    public function orderCountInPeriod(Restaurant $restaurant): int
    {
        if (! $restaurant->orders_limit_start || ! $restaurant->orders_limit_end) {
            return 0;
        }

        return Order::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [
                $restaurant->orders_limit_start->startOfDay(),
                $restaurant->orders_limit_end->endOfDay(),
            ])
            ->count();
    }
}
