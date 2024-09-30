<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;

class CartItemPolicy
{
    public function addItemToCart(User $user, CartItem $cartItem)
    {
        return $user->id === $cartItem->cart->user_id;
    }

    public function update(User $user, CartItem $cartItem)
    {
        return $user->id === $cartItem->cart->user_id;
    }

    public function updateQuantity(User $user, CartItem $cartItem)
    {
        return $user->id === $cartItem->cart->user_id;
    }

    public function removeItemFromCart(User $user, CartItem $cartItem)
    {
        return $user->id === $cartItem->cart->user_id;
    }
}
