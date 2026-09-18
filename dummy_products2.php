<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sellerId = 2;
$products = [
    [
        "category_id" => 2,
        "title" => "Samsung Galaxy S23 Ultra",
        "description" => "Experience the ultimate smartphone with the Samsung Galaxy S23 Ultra. Features a stunning 6.8-inch Dynamic AMOLED display, 200MP camera for professional-grade photography, and the Snapdragon 8 Gen 2 processor for unparalleled performance.",
        "price" => 124999.00,
        "price_unit" => "per piece",
        "minimum_quantity" => 1,
        "is_negotiable" => 1,
        "condition" => 1,
        "location" => "Delhi, India",
        "city" => "Delhi",
        "latitude" => 28.7041,
        "longitude" => 77.1025,
    ],
    [
        "category_id" => 3,
        "title" => "Apple MacBook Pro 14-inch (M3 Pro)",
        "description" => "The 14-inch MacBook Pro blasts forward with M3 Pro, an incredibly advanced chip that brings massive performance and capabilities. Features a beautiful Liquid Retina XDR display and up to 18 hours of battery life.",
        "price" => 169900.00,
        "price_unit" => "per piece",
        "minimum_quantity" => 1,
        "is_negotiable" => 0,
        "condition" => 1,
        "location" => "Mumbai, Maharashtra",
        "city" => "Mumbai",
        "latitude" => 19.0760,
        "longitude" => 72.8777,
    ],
    [
        "category_id" => 8,
        "title" => "Premium Cotton Formal Shirt",
        "description" => "Elevate your formal wardrobe with this premium 100% cotton solid color formal shirt. Features a classic collar, button-down front, and full sleeves. Perfect for office wear and business meetings.",
        "price" => 1299.00,
        "price_unit" => "per piece",
        "minimum_quantity" => 50,
        "is_negotiable" => 1,
        "condition" => 1,
        "location" => "Surat, Gujarat",
        "city" => "Surat",
        "latitude" => 21.1702,
        "longitude" => 72.8311,
    ],
    [
        "category_id" => 9,
        "title" => "Designer Silk Saree with Blouse Piece",
        "description" => "Exquisite designer silk saree perfect for weddings and festive occasions. Comes with a matching unstitched blouse piece. Intricate zari work and vibrant colors make it a must-have in your ethnic collection.",
        "price" => 3499.00,
        "price_unit" => "per piece",
        "minimum_quantity" => 10,
        "is_negotiable" => 1,
        "condition" => 1,
        "location" => "Varanasi, UP",
        "city" => "Varanasi",
        "latitude" => 25.3176,
        "longitude" => 82.9739,
    ],
    [
        "category_id" => 10,
        "title" => "Boys Party Wear Suit Set",
        "description" => "Stylish 3-piece party wear suit for boys (Ages 3-10 years). Includes a blazer, waistcoat, and matching trousers made from premium blend fabric. Comfortable fit for all-day wear.",
        "price" => 1899.00,
        "price_unit" => "per pack",
        "minimum_quantity" => 20,
        "is_negotiable" => 1,
        "condition" => 1,
        "location" => "Ludhiana, Punjab",
        "city" => "Ludhiana",
        "latitude" => 30.9010,
        "longitude" => 75.8573,
    ],
    [
        "category_id" => 2,
        "title" => "iPhone 15 (128GB)",
        "description" => "Apple iPhone 15 with Dynamic Island, 48MP Main camera, and USB-C. Experience gorgeous design, incredible performance, and an advanced camera system that takes magical photos.",
        "price" => 79900.00,
        "price_unit" => "per piece",
        "minimum_quantity" => 5,
        "is_negotiable" => 0,
        "condition" => 1,
        "location" => "Bengaluru, Karnataka",
        "city" => "Bengaluru",
        "latitude" => 12.9716,
        "longitude" => 77.5946,
    ],
    [
        "category_id" => 3,
        "title" => "Dell XPS 15",
        "description" => "The Dell XPS 15 strikes the perfect balance of power and portability. Features a stunning 15.6-inch OLED display, Intel Core i7 processor, and NVIDIA RTX graphics for creative professionals.",
        "price" => 185000.00,
        "price_unit" => "per piece",
        "minimum_quantity" => 2,
        "is_negotiable" => 1,
        "condition" => 1,
        "location" => "Pune, Maharashtra",
        "city" => "Pune",
        "latitude" => 18.5204,
        "longitude" => 73.8567,
    ]
];

foreach ($products as $data) {
    $data["user_id"] = $sellerId;
    $data["status"] = \App\Models\Product::STATUS_ACTIVE;
    $data["slug"] = \Illuminate\Support\Str::slug($data["title"]) . "-" . rand(1000, 9999);
    $data["expires_at"] = now()->addDays(30);
    $data["created_at"] = now();
    $data["updated_at"] = now();
    
    \App\Models\Product::create($data);
}
echo "Dummy products created successfully!\n";
