<?php

use Illuminate\Support\Facades\Storage;
use App\Models\Requirement;
use App\Models\RequirementImage;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting image seeding for requirements (Live Version)...\n";

// Ensure the directory exists
$directory = 'requirements/images';
if (!Storage::disk('public')->exists($directory)) {
    Storage::disk('public')->makeDirectory($directory);
}

// Download 20 random machinery-related images
$downloadedImages = [];
$numberOfUniqueImagesToDownload = 20;

echo "Downloading $numberOfUniqueImagesToDownload machinery-related images...\n";
for ($i = 0; $i < $numberOfUniqueImagesToDownload; $i++) {
    $filename = Str::random(10) . '.jpg';
    $path = $directory . '/' . $filename;
    
    // Using loremflickr with 'machinery' keyword to get relevant images
    $url = "https://loremflickr.com/600/600/machinery?random={$i}";
    
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
        echo "Downloaded machinery image " . ($i + 1) . "\n";
    } else {
        echo "Failed to download image " . ($i + 1) . " (HTTP $httpCode)\n";
    }
}

if (empty($downloadedImages)) {
    echo "Failed to download any images.\n";
    exit;
}

// Loop through all requirements and replace their images
$requirements = Requirement::all();

echo "Found " . $requirements->count() . " requirements. Replacing images...\n";

$count = 0;
foreach ($requirements as $requirement) {
    // Delete existing images from database
    RequirementImage::where('requirement_id', $requirement->id)->delete();

    // Pick 1-2 random images from our downloaded pool for each requirement
    $numImages = rand(1, 2);
    $selectedImages = array_rand(array_flip($downloadedImages), $numImages);
    
    if (!is_array($selectedImages)) {
        $selectedImages = [$selectedImages];
    }
    
    foreach ($selectedImages as $index => $imagePath) {
        RequirementImage::create([
            'requirement_id' => $requirement->id,
            'image_path' => $imagePath,
            'is_primary' => $index === 0, // First image is primary
            'sort_order' => $index,
        ]);
    }
    
    $count++;
    if ($count % 10 == 0) {
        echo "Replaced images for $count requirements...\n";
    }
}

echo "Done! Replaced images for $count requirements.\n";
