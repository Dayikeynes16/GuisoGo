<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('restaurant.{restaurantId}', function (User $user, int $restaurantId): bool {
    return (int) $user->restaurant_id === $restaurantId;
});
