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
            $files = [];
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
                            
                            // Check if this is a file upload
                            if (preg_match('/filename="([^"]*)"/', $headers, $filenameMatches)) {
                                $filename = $filenameMatches[1];
                                if (!empty($filename)) {
                                    // Create a temporary file for the uploaded data
                                    $tempFile = tmpfile();
                                    fwrite($tempFile, $data);
                                    $tempPath = stream_get_meta_data($tempFile)['uri'];
                                    
                                    // Create a mock UploadedFile-like object
                                    $files[$fieldName] = [
                                        'name' => $filename,
                                        'type' => $this->extractContentType($headers),
                                        'tmp_name' => $tempPath,
                                        'error' => 0,
                                        'size' => strlen($data),
                                        'data' => $data
                                    ];
                                }
                            } else {
                                // Regular form field
                                $input[$fieldName] = $data;
                            }
                        }
                    }
                }
                
                // Merge parsed data with request
                $request->merge($input);
                $request->files->replace($files);
                error_log('Parsed multipart data: ' . json_encode($input));
                error_log('Parsed files: ' . json_encode(array_keys($files)));
            }
        }
        
        return $request;
    }

    /**
     * Extract content type from headers
     */
    private function extractContentType($headers)
    {
        if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $headers, $matches)) {
            return trim($matches[1]);
        }
        return 'application/octet-stream';
    }

    /**
     * Handle file upload and return the public URL
     */
    private function handleFileUpload($file)
    {
        if (!$file) {
            return null;
        }

        try {
            // Generate unique filename
            $filename = uniqid() . '_' . time();
            
            // Get file extension from original name if available
            if (is_array($file) && isset($file['name'])) {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                if ($extension) {
                    $filename .= '.' . $extension;
                }
            } elseif (method_exists($file, 'getClientOriginalExtension')) {
                $extension = $file->getClientOriginalExtension();
                if ($extension) {
                    $filename .= '.' . $extension;
                }
            }

            // Create products directory in storage
            $uploadPath = storage_path('app/public/products');
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $filePath = $uploadPath . '/' . $filename;

            // Handle different file types
            if (is_array($file) && isset($file['data'])) {
                // From our custom multipart parser
                file_put_contents($filePath, $file['data']);
            } elseif (method_exists($file, 'move')) {
                // Standard UploadedFile
                $file->move($uploadPath, $filename);
            } elseif (is_uploaded_file($file['tmp_name'] ?? '')) {
                // Standard PHP upload
                move_uploaded_file($file['tmp_name'], $filePath);
            } else {
                throw new \Exception('Invalid file upload');
            }

            // Return public URL
            return '/storage/products/' . $filename;

        } catch (\Exception $e) {
            error_log('File upload error: ' . $e->getMessage());
            throw new \Exception('Failed to upload file: ' . $e->getMessage());
        }
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
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Allow image files up to 2MB
            ]);

            // Handle image upload if present
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $this->handleFileUpload($request->file('image'));
            }

            // ✅ Actually save to database
            $product = Product::create([
                'name' => $request->name,
                'category' => $request->category,
                'price' => $request->price,
                'image' => $imagePath
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
            error_log('Files: ' . json_encode(array_keys($request->files->all())));
            error_log('================');

            // Find the product
            $product = Product::findOrFail($id);
            error_log('Original product: ' . json_encode($product->toArray()));

            // Validate the request
            $this->validate($request, [
                'name' => 'sometimes|required|string|max:255',
                'category' => 'sometimes|required|string|max:255',
                'price' => 'sometimes|required|numeric|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
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

            // Handle image upload
            if ($request->hasFile('image') || $request->files->has('image')) {
                $imageFile = $request->file('image') ?: $request->files->get('image');
                $imagePath = $this->handleFileUpload($imageFile);
                if ($imagePath) {
                    // Delete old image if exists
                    if ($product->image) {
                        $oldImagePath = storage_path('app/public' . str_replace('/storage', '', $product->image));
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    $updateData['image'] = $imagePath;
                }
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
                    'update_data' => $updateData,
                    'has_file' => $request->hasFile('image') || $request->files->has('image')
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
            // Find the product
            $product = Product::findOrFail($id);
            
            // Delete associated image file if exists
            if ($product->image) {
                $imagePath = storage_path('app/public' . str_replace('/storage', '', $product->image));
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                    error_log('Deleted image file: ' . $imagePath);
                }
            }
            
            // Delete the product
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
