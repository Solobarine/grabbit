<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductImageController extends Controller
{
    public function update(Request $request, ProductImage $productImage)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,png,avif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 422);
        }

        Storage::delete($productImage->url);

        $path = Storage::putFile('/products', $request->file('image'));

        $productImage->url = $path;
        $productImage->save;

        return response()->json([
            'status' => true,
            'message' => 'Image updated successfully',
            'url' => $path
        ], 200);
    }
}
