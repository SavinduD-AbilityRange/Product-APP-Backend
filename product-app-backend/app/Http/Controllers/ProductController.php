<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    // Get all products
    public function index()
    {
        return response()->json(Product::all());
    }

    // Create new product
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string',
                'category' => 'required|string',
                'price' => 'required|numeric',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'date' => 'required|date',
            ]);

            $imagePath = '';
            if ($request->hasFile('image')) {
                $imageFile = $request->file('image'); 
                $filename = time() . '_' . $imageFile->getClientOriginalName();
                Log::info($filename);
                $imageFile->move(base_path('public/uploads'), $filename);
                $imagePath = 'uploads/' . $filename;
            }

            $product = Product::create([
                'name' => $request->name,
                'category' => $request->category,
                'price' => $request->price,
                'image' => $imagePath,
                'date' => $request->date,
            ]);

            return response()->json($product, 201);

        } catch (\Exception $e) {
            Log::error('This is an error.');
            Log::error( $e->getMessage());
    return response()->json([
        'error' => 'Server error',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], 500);
}

    }

    // Get one product
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['message' => 'Not Found'], 404);

        return response()->json($product);
    }

    // Update product
    public function update(Request $request, $id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json(['message' => 'Not Found'], 404);
            }

            $this->validate($request, [
                'name' => 'sometimes|string',
                'category' => 'sometimes|string',
                'price' => 'sometimes|numeric',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'date' => 'sometimes|date',
            ]);

            if ($request->hasFile('image')) {
                $imageFile = $request->file('image'); 
                $filename = time() . '_' . $imageFile->getClientOriginalName();
                $imageFile->move(base_path('public/uploads'), $filename);
                $product->image = 'uploads/' . $filename;
            }

            $product->fill($request->except('image'))->save();

            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    // Delete product
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['message' => 'Not Found'], 404);

        $product->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
