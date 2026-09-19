<?php

$files = [
    'app/Http/Requests/Admin/Product/StoreProductRequest.php',
    'app/Http/Requests/Admin/Product/UpdateProductRequest.php',
    'app/Http/Requests/Admin/Requirement/StoreRequirementRequest.php',
    'app/Http/Requests/Admin/Requirement/UpdateRequirementRequest.php',
    'app/Http/Requests/Api/V1/Product/StoreProductRequest.php',
    'app/Http/Requests/Api/V1/Product/UpdateProductRequest.php',
    'app/Http/Requests/Api/V1/Requirement/StoreRequirementRequest.php',
    'app/Http/Requests/Api/V1/Requirement/UpdateRequirementRequest.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Missing: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    if (strpos($content, "'other_category_name'") === false) {
        // Insert after category_id
        $content = preg_replace("/'category_id' => 'required\|exists:categories,id',/", "'category_id' => 'required|exists:categories,id',\n            'other_category_name' => 'nullable|string|max:100',", $content);
        // Sometimes it might just be 'category_id' => 'sometimes|exists:categories,id',
        $content = preg_replace("/'category_id' => 'sometimes\|exists:categories,id',/", "'category_id' => 'sometimes|exists:categories,id',\n            'other_category_name' => 'nullable|string|max:100',", $content);
        
        file_put_contents($file, $content);
        echo "Updated: $file\n";
    }
}
