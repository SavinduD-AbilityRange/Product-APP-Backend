<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // 📦 GET /api/products
    public function index()
    {
        $products = Product::all()->map(function ($product) {
            $product->image_url = $product->image 
                ? url('storage/uploads/' . $product->image) 
                : '';
            return $product;
        });

        return response()->json($products);
    }

    // ➕ POST /api/products
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'category' => 'required|string',
            'price'    => 'required|numeric',
            'image'    => 'nullable|image|max:2048',
        ]);

        $filename = null;
        if ($request->hasFile('image')) {
            $filename = Str::random(10) . '.' . $request->image->extension();
            $request->image->storeAs('public/uploads', $filename);
        }

        $product = Product::create([
            'name'     => $request->name,
            'category' => $request->category,
            'price'    => $request->price,
            'image'    => $filename,
        ]);

        return response()->json(['message' => 'Product created', 'product' => $product], 201);
    }

    // ✏️ PUT /api/products/{id}
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'     => 'required|string',
            'category' => 'required|string',
            'price'    => 'required|numeric',
            'image'    => 'nullable|image|max:2048',
        ]);

        $filename = $product->image;

        if ($request->hasFile('image')) {
            if ($filename && Storage::exists('public/uploads/' . $filename)) {
                Storage::delete('public/uploads/' . $filename);
            }

            $filename = Str::random(10) . '.' . $request->image->extension();
            $request->image->storeAs('public/uploads', $filename);
        }

        $product->update([
            'name'     => $request->name,
            'category' => $request->category,
            'price'    => $request->price,
            'image'    => $filename,
        ]);

        return response()->json(['message' => 'Product updated', 'product' => $product]);
    }

    // 🗑 DELETE /api/products/{id}
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && Storage::exists('public/uploads/' . $product->image)) {
            Storage::delete('public/uploads/' . $product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}
