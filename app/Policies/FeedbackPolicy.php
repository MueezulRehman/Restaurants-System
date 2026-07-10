<?php

namespace App\Policies;

use App\Models\Feedback;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeedbackPolicy
{
    use HandlesAuthorization;

    public function view($user, Feedback $feedback): bool
    {
        if ($user instanceof \App\Models\Customer) {
            return $feedback->customer_id === $user->id;
        }

        if ($user instanceof \App\Models\User) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            return $user->restaurant_id === $feedback->restaurant_id && $user->isRestaurantManager();
        }

        return false;
    }

    public function update($user, Feedback $feedback): bool
    {
        if (! $user instanceof \App\Models\User) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->restaurant_id === $feedback->restaurant_id && $user->isRestaurantManager();
    }

    public function delete($user, Feedback $feedback): bool
    {
        return $user instanceof \App\Models\User && $user->isSuperAdmin();
    }
}
