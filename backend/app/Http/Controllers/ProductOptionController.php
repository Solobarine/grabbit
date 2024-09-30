<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductOptionController extends Controller
{
    public function update(Request $request, ProductOption $productOption)
    {
        $validator = Validator::make($request->all(), [
            'option' => 'sometimes|string|max:255',
            'name' => 'sometimes|string|max:255',
            'quantity' => 'sometimes|integer',
            'price' => 'sometimes|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 422);
        }

        $productOption->option = $request->option ?? $productOption->option;
        $productOption->name = $request->name ?? $productOption->name;
        $productOption->quantity = $request->quantity ?? $productOption->quantity;
        $productOption->price = $request->price ?? $productOption->price;
        $productOption->save();

        return response()->json([
            'status' => true,
            'message' => 'Product Option updated sucessfully',
            'productOption' => $productOption
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'option' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'price' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 422);
        }

        $productOption = ProductOption::create([
            'product_id' => $request->product_id,
            'option' => $request->option,
            'name' => $request->name,
            'quantity' => $request->quantity,
            'price' => $request->price
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Product Option created successfully',
            'productOption' => $productOption
        ], 201);
    }

    public function destroy(ProductOption $productOption)
    {
        $productOption->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product Option deleted successfully',
            'productOption' => $productOption
        ], 200);
    }
}
