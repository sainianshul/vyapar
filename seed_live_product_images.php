<?php

use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting image seeding for products (Live Version)...\n";

// Ensure the directory exists
$directory = 'products/images';
if (!Storage::disk('public')->exists($directory)) {
    Storage::disk('public')->makeDirectory($directory);
}

// Download 15 random product-related images
$downloadedImages = [];
$numberOfUniqueImagesToDownload = 15;

echo "Downloading $numberOfUniqueImagesToDownload product-related images...\n";
for ($i = 0; $i < $numberOfUniqueImagesToDownload; $i++) {
    $filename = Str::random(10) . '.jpg';
    $path = $directory . '/' . $filename;
    
    // Using loremflickr with 'product' keyword to get relevant images
    // Adding random= param to bypass cache
    $url = "https://loremflickr.com/600/600/product?random={$i}";
    
    // Some basic curl setup to handle redirects and user agent
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    $imageContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && $imageContent) {
        Storage::disk('public')->put($path, $imageContent);
        $downloadedImages[] = $path;
        echo "Downloaded product image " . ($i + 1) . "\n";
    } else {
        echo "Failed to download image " . ($i + 1) . " (HTTP $httpCode)\n";
    }
}

if (empty($downloadedImages)) {
    echo "Failed to download any images.\n";
    exit;
}

// Loop through all products and replace their images
$products = Product::all();

echo "Found " . $products->count() . " products. Replacing images...\n";

$count = 0;
foreach ($products as $product) {
    // Delete existing images from database
    // Note: We are not deleting the physical files here as they might be used by multiple products or are the ones we just downloaded
    ProductImage::where('product_id', $product->id)->delete();

    // Pick 1-2 random images from our downloaded pool for each product
    $numImages = rand(1, 2);
    $selectedImages = array_rand(array_flip($downloadedImages), $numImages);
    
    if (!is_array($selectedImages)) {
        $selectedImages = [$selectedImages];
    }
    
    foreach ($selectedImages as $index => $imagePath) {
        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $imagePath,
            'is_primary' => $index === 0, // First image is primary
            'sort_order' => $index,
        ]);
    }
    
    $count++;
    if ($count % 10 == 0) {
        echo "Replaced images for $count products...\n";
    }
}

echo "Done! Replaced images for $count products.\n";
