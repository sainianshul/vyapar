<?php

$replacements = [
    'Product' => 'Requirement',
    'product' => 'requirement',
    'Products' => 'Requirements',
    'products' => 'requirements',
    'Seller' => 'Buyer',
    'seller' => 'buyer',
    'price' => 'target_budget',
    'price_unit' => 'delivery_pincode', // wait, delivery_pincode is used differently. Let's just manually fix fields.
    'minimum_quantity' => 'quantity',
    'location' => 'delivery_location',
    'pincode' => 'delivery_pincode',
];

function str_replace_assoc($replace, $subject) {
    return str_replace(array_keys($replace), array_values($replace), $subject);
}

// 1. Service
$productService = file_get_contents('app/Services/ProductService.php');
$reqService = str_replace_assoc($replacements, $productService);
// fix status fallback
$reqService = str_replace('Requirement::STATUS_ACTIVE', 'Requirement::STATUS_OPEN', $reqService);
file_put_contents('app/Services/RequirementService.php', $reqService);

// 2. Form Requests
mkdir('app/Http/Requests/Admin/Requirement', 0777, true);
$storeReq = file_get_contents('app/Http/Requests/Admin/Product/StoreProductRequest.php');
$storeReq = str_replace_assoc($replacements, $storeReq);
// Fix rules
$storeReq = str_replace("'target_budget' => 'required|numeric|min:0',", "'target_budget' => 'required|numeric|min:0',\n            'quantity' => 'required|integer|min:1',", $storeReq);
// remove old minimum_quantity line
$storeReq = preg_replace("/'quantity' => 'required\|integer\|min:1',\r?\n?/", "", $storeReq, 1);
$storeReq = preg_replace("/'condition' => .*,\r?\n?/", "", $storeReq);
$storeReq = preg_replace("/'delivery_pincode' => 'nullable\|string\|max:50',\r?\n?/", "", $storeReq);
$storeReq = preg_replace("/'is_negotiable' => 'nullable\|boolean',\r?\n?/", "", $storeReq);
$storeReq = preg_replace("/'is_featured' => 'nullable\|boolean',\r?\n?/", "", $storeReq);
$storeReq = preg_replace("/'is_verified' => 'nullable\|boolean',\r?\n?/", "", $storeReq);
file_put_contents('app/Http/Requests/Admin/Requirement/StoreRequirementRequest.php', $storeReq);

$updateReq = file_get_contents('app/Http/Requests/Admin/Product/UpdateProductRequest.php');
$updateReq = str_replace_assoc($replacements, $updateReq);
$updateReq = preg_replace("/'condition' => .*,\r?\n?/", "", $updateReq);
$updateReq = preg_replace("/'delivery_pincode' => 'nullable\|string\|max:50',\r?\n?/", "", $updateReq);
$updateReq = preg_replace("/'is_negotiable' => 'nullable\|boolean',\r?\n?/", "", $updateReq);
$updateReq = preg_replace("/'is_featured' => 'nullable\|boolean',\r?\n?/", "", $updateReq);
$updateReq = preg_replace("/'is_verified' => 'nullable\|boolean',\r?\n?/", "", $updateReq);
file_put_contents('app/Http/Requests/Admin/Requirement/UpdateRequirementRequest.php', $updateReq);

// 3. Controller
$prodCtrl = file_get_contents('app/Http/Controllers/Admin/ProductController.php');
$reqCtrl = str_replace_assoc($replacements, $prodCtrl);
$reqCtrl = preg_replace("/\\\$data\['is_negotiable'\] = \\\$request->boolean\('is_negotiable'\);\r?\n?/", "", $reqCtrl);
$reqCtrl = preg_replace("/\\\$data\['is_featured'\] = \\\$request->boolean\('is_featured'\);\r?\n?/", "", $reqCtrl);
$reqCtrl = preg_replace("/\\\$data\['is_verified'\] = \\\$request->boolean\('is_verified'\);\r?\n?/", "", $reqCtrl);
$reqCtrl = preg_replace("/public function updateFeatured.*?\n    }\n/s", "", $reqCtrl);
file_put_contents('app/Http/Controllers/Admin/RequirementController.php', $reqCtrl);

// 4. Views
@mkdir('resources/views/admin/requirements', 0777, true);
$createView = file_get_contents('resources/views/admin/products/create.blade.php');
$createView = str_replace_assoc($replacements, $createView);
// manually fix some fields later
file_put_contents('resources/views/admin/requirements/create.blade.php', $createView);

$editView = file_get_contents('resources/views/admin/products/edit.blade.php');
$editView = str_replace_assoc($replacements, $editView);
file_put_contents('resources/views/admin/requirements/edit.blade.php', $editView);

$showView = file_get_contents('resources/views/admin/products/show.blade.php');
$showView = str_replace_assoc($replacements, $showView);
file_put_contents('resources/views/admin/requirements/show.blade.php', $showView);

echo "Scaffolded successfully.\n";
