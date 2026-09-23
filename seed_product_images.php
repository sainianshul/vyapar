<?php

use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting image seeding for products...\n";

// Ensure the directory exists
$directory = 'products/images';
if (!Storage::disk('public')->exists($directory)) {
    Storage::disk('public')->makeDirectory($directory);
}

// 1. Download 10 random images from picsum.photos so we don't spam their server
// and it keeps your storage light, but every product gets a good looking image.
$downloadedImages = [];
$numberOfUniqueImagesToDownload = 10;

echo "Downloading $numberOfUniqueImagesToDownload dummy images...\n";
for ($i = 0; $i < $numberOfUniqueImagesToDownload; $i++) {
    $filename = Str::random(10) . '.jpg';
    $path = $directory . '/' . $filename;
    
    // Get random image content from picsum (real photos)
    $imageContent = file_get_contents("https://picsum.photos/600/600?random={$i}");
    
    if ($imageContent) {
        Storage::disk('public')->put($path, $imageContent);
        $downloadedImages[] = $path;
        echo "Downloaded dummy image " . ($i + 1) . "\n";
    }
}

if (empty($downloadedImages)) {
    echo "Failed to download any images.\n";
    exit;
}

// 2. Loop through all products that don't have a primary image yet and assign one
$products = Product::doesntHave('images')->get();

echo "Found " . $products->count() . " products without images.\n";

$count = 0;
foreach ($products as $product) {
    // Pick 1-3 random images from our downloaded pool for each product
    $numImages = rand(1, 3);
    $selectedImages = array_rand(array_flip($downloadedImages), $numImages);
    
    // If array_rand returns a single element, it's not an array
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
        echo "Assigned images to $count products...\n";
    }
}

echo "Done! Assigned images to $count products.\n";
