<?php

namespace App\Policies;

use App\Models\Promotion;
use App\Models\User;

class PromotionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->restaurant_id !== null;
    }

    public function view(User $user, Promotion $promotion): bool
    {
        return $user->restaurant_id === $promotion->restaurant_id;
    }

    public function create(User $user): bool
    {
        return $user->restaurant_id !== null;
    }

    public function update(User $user, Promotion $promotion): bool
    {
        return $user->restaurant_id === $promotion->restaurant_id;
    }

    public function delete(User $user, Promotion $promotion): bool
    {
        return $user->restaurant_id === $promotion->restaurant_id;
    }
}
