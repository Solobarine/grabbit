<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartItemController extends Controller
{
    public function addItemToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer',
            'options' => 'required|array',
            'options.*.id' => 'integer:exists:productOptions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 422);
        }

        if (!auth()->user()->cart) {
            auth()->user()->cart()->create();
        }

        $cartItem = auth()->user()->cart->items()->create([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'options' => $request->options
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Item added to cart',
            'cartItem' => $cartItem
        ], 200);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $validator = Validator::make($request->all(), [
            'options' => 'required|array',
            'options.*.id' => 'integer:exists:productOptions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 422);
        }

        $cartItem->options = $request->options;
        $cartItem->save();

        return response()->json([
            'status' => true,
            'message' => 'Item updated successfully',
            'cartItem' => $cartItem
        ], 200);
    }

    public function updateQuantity(Request $request, CartItem $cartItem)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 422);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'status' => true,
            'message' => 'Item updated successfully',
            'cartItem' => $cartItem
        ], 200);
    }

    public function removeItemFromCart(CartItem $cartItem)
    {
        $cartItem->delete();

        return response()->json([
            'status' => true,
            'message' => 'Item removed from cart',
            'cartItem' => $cartItem
        ], 200);
    }
}
