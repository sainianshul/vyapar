<?php

function addSwaggerProperty($file) {
    $content = file_get_contents($file);
    
    // Look for category_id in properties
    $search = '@OA\Property(property="category_id", type="integer", example=1),';
    $replace = $search . "\n     *             @OA\Property(property=\"other_category_name\", type=\"string\", example=\"Drones\", description=\"Name of the category if 'Other' is selected\"),";
    
    // For requirement it might not have example=1
    $searchReq = '@OA\Property(property="category_id", type="integer"';
    
    if (strpos($content, $search) !== false) {
        $content = str_replace($search, $replace, $content);
    } else {
        // Fallback regex for any property="category_id"
        $content = preg_replace('/(@OA\\\\Property\(property="category_id",.*?type="integer".*?\),?)/', "$1\n     *             @OA\Property(property=\"other_category_name\", type=\"string\", example=\"Drones\", description=\"Name of the category if 'Other' is selected\"),", $content);
    }
    
    file_put_contents($file, $content);
    echo "Updated Swagger in: $file\n";
}

addSwaggerProperty('app/Http/Controllers/Api/V1/ProductController.php');
addSwaggerProperty('app/Http/Controllers/Api/V1/RequirementController.php');
