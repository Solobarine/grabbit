<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json(['status' => true, 'categories' => $categories], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,png,avif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 422);
        }

        $path = Storage::putFile('/categories', $request->file('image'));

        $category = Category::create([
            'name' => $request->name,
            'image' => $path
        ]);

        return response()->json([
            'status' => true,
            'category' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return response()->json([
            'status' => true,
            'category' => $category
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 422);
        }

        $category->name = $request->name ?? $category->name;
        $category->save();

        return response()->json([
            'status' => true,
            'category' => $category
        ], 200);
    }

    public function updateImage(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,png,avif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }

        // Check if Image Exists
        if (Storage::exists($category->image)) {
            Storage::delete($category->image);
        }

        $path = Storage::putFile('/categories', $request->file('image'));
        $category->image = $path;
        $category->save();

        return response()->json([
            'status' => true,
            'message' => 'Image updated successfully',
            'category' => $category
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'status' => true,
            'category' => $category
        ], 200);
    }
}
