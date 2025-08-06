<?php
require_once 'bootstrap/app.php';

use App\Models\Product;

try {
    $products = Product::all();
    echo "Products in database:\n";
    foreach($products as $product) {
        echo "ID: {$product->id}, Name: {$product->name}, Image: {$product->image}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
