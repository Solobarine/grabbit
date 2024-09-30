<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();

        return response()->json([
            'status' => true,
            'products' => $products
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*' => 'mimes:jpg,png,avif',
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|integer',
            'options' => 'required|array',
            'options.*.title' => 'required|string|max:255',
            'options.*.values' => 'required|array',
            'options.*.values.*.name' => 'required|string|max:255',
            'options.*.values.*.quantity' => 'required|integer',
            'options.*.values.*.price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 422);
        }

        // Check if Category Exists
        $category = Category::query()->find($request->category_id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'error' => 'Category with id not found'
            ], 404);
        }

        $product = $category->products()->create([
            'name' => $request->name,
            'brand' => $request->brand,
            'description' => $request->description,
        ]);


        foreach ($request->options as $option) {
            foreach ($option['values'] as $value) {
                $product->options()->create([
                    'option' => $option['title'],
                    'name' => $value['name'],
                    'quantity' => $value['quantity'],
                    'price' => $value['price']
                ]);
            }
        }


        // Save Images
        foreach ($request->file('images') as $image) {
            $path = Storage::putFile('/products', $image);
            $product->images()->create([
                'url' => $path
            ]);
        };

        return response()->json([
            'status' => true,
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $product = Product::query()->with(['images', 'category'])->find($request->id);

        return response()->json([
            'status' => $product ? true : false,
            'product' => $product
        ], $product ? 200 : 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'brand' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 422);
        }

        $product->name = $request->name ?? $product->name;
        $product->brand = $request->brand ?? $product->brand;
        $product->description = $request->description ?? $product->description;
        $product->category_id = $request->category_id ?? $product->category_id;
        $product->save();

        return response()->json([
            'status' => true,
            'message' => 'Product updated successfully',
            'product' => $product
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully',
            'product' => $product
        ], 200);
    }
}
