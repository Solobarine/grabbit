<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\User;

class CartPolicy
{
    public function destroy(User $user, Cart $cart): bool
    {
        return in_array($user->role, ['admin', 'super_admin']) | $user->id === $cart->user_id;
    }
}
