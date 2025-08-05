<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

class SimpleProductController extends Controller
{
    /**
     * Parse multipart form data for PUT requests
     * PHP doesn't automatically parse multipart data for PUT requests
     */
    private function parseMultipartData(Request $request)
    {
        if ($request->isMethod('PUT') && str_contains($request->header('Content-Type', ''), 'multipart/form-data')) {
            $input = [];
            $contentType = $request->header('Content-Type');
            
            if (preg_match('/boundary=(.*)$/', $contentType, $matches)) {
                $boundary = $matches[1];
                $rawData = $request->getContent();
                
                // Split the raw data by boundary
                $parts = explode('--' . $boundary, $rawData);
                
                foreach ($parts as $part) {
                    if (trim($part) === '' || trim($part) === '--') continue;
                    
                    // Parse each part
                    $sections = explode("\r\n\r\n", $part, 2);
                    if (count($sections) === 2) {
                        $headers = $sections[0];
                        $data = rtrim($sections[1], "\r\n");
                        
                        // Extract field name from Content-Disposition header
                        if (preg_match('/name="([^"]+)"/', $headers, $nameMatches)) {
                            $fieldName = $nameMatches[1];
                            $input[$fieldName] = $data;
                        }
                    }
                }
                
                // Merge parsed data with request
                $request->merge($input);
                error_log('Parsed multipart data: ' . json_encode($input));
            }
        }
        
        return $request;
    }

    public function index()
    {
        try {
            $products = Product::all();
            return response()->json([
                'message' => 'Products retrieved successfully',
                'products' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve products',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            // Validate the request
            $this->validate($request, [
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|string'
            ]);

            // ✅ Actually save to database
            $product = Product::create([
                'name' => $request->name,
                'category' => $request->category,
                'price' => $request->price,
                'image' => $request->image
            ]);

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create product',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            // ✅ Parse multipart form data for PUT requests
            $request = $this->parseMultipartData($request);

            // === DEBUG: Log what we received ===
            error_log('=== UPDATE REQUEST RECEIVED ===');
            error_log('Product ID: ' . $id);
            error_log('Request method: ' . $request->method());
            error_log('Content-Type: ' . $request->header('Content-Type'));
            error_log('Request body: ' . json_encode($request->all()));
            error_log('Raw input: ' . substr($request->getContent(), 0, 500) . '...');
            error_log('================');

            // Find the product
            $product = Product::findOrFail($id);
            error_log('Original product: ' . json_encode($product->toArray()));

            // Validate the request
            $this->validate($request, [
                'name' => 'sometimes|required|string|max:255',
                'category' => 'sometimes|required|string|max:255',
                'price' => 'sometimes|required|numeric|min:0',
                'image' => 'nullable|string'
            ]);

            // ✅ Extract form data explicitly
            $updateData = [];
            if ($request->filled('name')) {
                $updateData['name'] = $request->input('name');
            }
            if ($request->filled('category')) {
                $updateData['category'] = $request->input('category');
            }
            if ($request->filled('price')) {
                $updateData['price'] = (float) $request->input('price');
            }
            if ($request->has('image')) {
                $updateData['image'] = $request->input('image');
            }

            error_log('Update data to save: ' . json_encode($updateData));

            // ✅ Only update if we have data to update
            if (!empty($updateData)) {
                $product->update($updateData);
            }

            // Get fresh data from database
            $updatedProduct = $product->fresh();
            error_log('Updated product from DB: ' . json_encode($updatedProduct->toArray()));

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $updatedProduct,
                'debug' => [
                    'method' => $request->method(),
                    'content_type' => $request->header('Content-Type'),
                    'received_data' => $request->all(),
                    'update_data' => $updateData
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            error_log('Update error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update product',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            // Find and delete the product
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully',
                'id' => $id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete product',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
