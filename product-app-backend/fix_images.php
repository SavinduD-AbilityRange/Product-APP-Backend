<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$app->boot();

use App\Models\Product;

// Find products with invalid image paths
$products = Product::where('image', 'like', '/private/%')
    ->orWhere('image', 'like', '/tmp/%')
    ->get();

echo "Found " . $products->count() . " products with invalid image paths:\n";

foreach ($products as $product) {
    echo "Product ID {$product->id}: {$product->name} - Image: {$product->image}\n";
    
    // Set image to null for these invalid entries
    $product->update(['image' => null]);
    echo "  → Updated to null\n";
}

echo "\nCleanup completed!\n";
