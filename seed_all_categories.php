<?php



$sellerId = 2; // Default seller ID, ensure this user exists

// Child Categories
$childCategories = [
    2 => 'Mobiles', 3 => 'Laptops', 4 => 'Cameras', 5 => 'Televisions', 6 => 'Audio & Accessories',
    8 => "Men's Clothing", 9 => "Women's Clothing", 10 => "Kids' Wear", 11 => 'Winter Wear', 12 => 'Fashion Accessories',
    14 => 'Living Room', 15 => 'Bedroom', 16 => 'Kitchen & Dining', 17 => 'Home Decor', 18 => 'Office Furniture',
    20 => 'Makeup', 21 => 'Skincare', 22 => 'Haircare', 23 => 'Fragrances', 24 => "Men's Grooming",
    26 => 'Staples', 27 => 'Snacks & Branded Foods', 28 => 'Beverages', 29 => 'Dairy & Bakery', 30 => 'Packaged Food',
    32 => 'Fiction Books', 33 => 'Non-Fiction', 34 => 'School Supplies', 35 => 'Office Supplies', 36 => 'Magazines',
    38 => 'Cricket', 39 => 'Fitness Equipment', 40 => 'Yoga & Pilates', 41 => 'Cycling', 42 => 'Team Sports',
    44 => 'Action Figures', 45 => 'Educational Toys', 46 => 'Baby Care', 47 => 'Strollers & Prams', 48 => 'Soft Toys',
    50 => 'Car Accessories', 51 => 'Bike Accessories', 52 => 'Vehicle Cleaning', 53 => 'Spare Parts', 54 => 'Helmets',
    56 => 'Sports Shoes', 57 => 'Casual Shoes', 58 => 'Formal Shoes', 59 => 'Sandals & Floaters', 60 => 'Slippers & Flip Flops',
    62 => 'Vitamins & Supplements', 63 => 'Medical Devices', 64 => 'First Aid', 65 => 'Protein Supplements', 66 => 'Ayurvedic Care',
    68 => 'Gold Jewellery', 69 => 'Silver Jewellery', 70 => 'Artificial Jewellery', 71 => "Men's Watches", 72 => "Women's Watches",
];

$conditions = [1, 2]; // 1 = New, 2 = Used
$cities = ['Delhi', 'Mumbai', 'Bangalore', 'Pune', 'Hyderabad', 'Chennai', 'Kolkata', 'Ahmedabad'];

$genuineNames = [
    2 => ['iPhone 14 Pro 128GB', 'Samsung Galaxy S23 Ultra', 'OnePlus 11 5G', 'Google Pixel 7a', 'Xiaomi Redmi Note 12 Pro'],
    3 => ['Apple MacBook Air M2', 'Dell XPS 15', 'HP Spectre x360', 'Lenovo ThinkPad X1 Carbon', 'Asus ROG Zephyrus G14'],
    4 => ['Sony Alpha a7 IV Mirrorless', 'Canon EOS R6 Mark II', 'Nikon Z6 II', 'GoPro HERO11 Black', 'DJI Mini 3 Pro Drone'],
    5 => ['Samsung 55" QLED 4K Smart TV', 'LG 65" OLED Evo TV', 'Sony Bravia 50" 4K Google TV', 'TCL 43" 4K Smart LED TV', 'Hisense 55" ULED 4K TV'],
    6 => ['Sony WH-1000XM5 Headphones', 'Apple AirPods Pro (2nd Gen)', 'JBL Charge 5 Bluetooth Speaker', 'Bose QuietComfort Earbuds II', 'Sennheiser Momentum 4 Wireless'],
    8 => ['Men\'s Slim Fit Formal Shirt', 'Levi\'s Men\'s 511 Slim Jeans', 'Puma Men\'s Graphic T-Shirt', 'Raymond Formal Trousers', 'U.S. Polo Assn. Men\'s Polo Shirt'],
    9 => ['Women\'s Floral Print Maxi Dress', 'Biba Women\'s Kurta Set', 'Zara High Waist Wide Leg Jeans', 'H&M Women\'s Blouse', 'Vero Moda Wrap Dress'],
    10 => ['Boys Printed Cotton T-Shirt', 'Girls Princess Party Dress', 'Kids Denim Jacket', 'Boys Ethnic Wear Kurta Pajama', 'Girls Floral Jumpsuit'],
    11 => ['Men\'s Quilted Winter Jacket', 'Women\'s Wool Blend Coat', 'Unisex Fleece Hoodie', 'Thermal Innerwear Set', 'Knitted Woolen Beanie'],
    12 => ['Ray-Ban Aviator Sunglasses', 'Fossil Leather Wallet', 'Tommy Hilfiger Canvas Belt', 'Women\'s Tote Handbag', 'Unisex Canvas Backpack'],
    14 => ['L Shape 5 Seater Sofa Set', 'Solid Wood Coffee Table', 'Modern TV Entertainment Unit', 'Fabric Recliner Chair', 'Set of 2 Nested Tables'],
    15 => ['King Size Sheesham Wood Bed', 'Orthopedic Memory Foam Mattress', 'Wooden 3-Door Wardrobe', 'Bedside Table with Drawers', 'Dressing Table with Mirror'],
    16 => ['Non-Stick Cookware Set (3 Pieces)', 'Bone China Dinner Set (16 Pieces)', 'Stainless Steel Cutlery Set', 'Glass Storage Jars (Set of 6)', 'Electric Kettle 1.5L'],
    17 => ['Abstract Canvas Wall Art', 'Ceramic Table Lamp', 'Cotton Printed Window Curtains', 'Geometric Pattern Area Rug', 'Artificial Indoor Plant with Pot'],
    18 => ['Ergonomic Office Chair', 'Adjustable Height Standing Desk', 'Wooden File Cabinet', 'Executive Office Desk', 'Desk Organizer with Drawers'],
    20 => ['MAC Studio Fix Fluid Foundation', 'Maybelline Lash Sensational Mascara', 'Huda Beauty Nude Eyeshadow Palette', 'Lakme Absolute Matte Lipstick', 'Nykaa Liquid Eyeliner'],
    21 => ['Cetaphil Gentle Skin Cleanser', 'The Ordinary Niacinamide 10%', 'Neutrogena Hydro Boost Water Gel', 'Plum Green Tea Toner', 'Minimalist Vitamin C Serum'],
    22 => ['L\'Oreal Paris Moisture Shampoo', 'TRESemmé Keratin Smooth Conditioner', 'Moroccanoil Treatment Hair Oil', 'Dyson Supersonic Hair Dryer', 'BBlunt Heat Protection Spray'],
    23 => ['Chanel Coco Mademoiselle EDP', 'Dior Sauvage Eau De Toilette', 'Versace Bright Crystal', 'Calvin Klein CK One', 'Titan Skinn Raw Perfume'],
    24 => ['Philips Multi Grooming Kit', 'Gillette Mach3 Razor Set', 'Beardo Beard Growth Oil', 'Nivea Men Fresh Face Wash', 'Bombay Shaving Company Shaving Cream'],
    26 => ['India Gate Basmati Rice 5kg', 'Aashirvaad Whole Wheat Atta 10kg', 'Fortune Sunlite Refined Oil 5L', 'Tata Salt 1kg', 'Toor Dal (Arhar) 1kg'],
    27 => ['Haldiram\'s Bhujia Sev 1kg', 'Lay\'s Classic Salted Potato Chips', 'Britannia Good Day Cookies', 'Cadbury Dairy Milk Silk', 'Nutella Hazelnut Spread 350g'],
    28 => ['Nescafé Classic Instant Coffee', 'Taj Mahal Tea 500g', 'Real Fruit Juice - Mixed Fruit 1L', 'Red Bull Energy Drink (4 Pack)', 'Coca-Cola 2L Bottle'],
    29 => ['Amul Butter 500g', 'Britannia 100% Whole Wheat Bread', 'Mother Dairy Fresh Paneer 200g', 'Epigamia Greek Yogurt', 'Kraft Cheddar Cheese Block'],
    30 => ['Maggi 2-Minute Noodles (12 Pack)', 'Kellogg\'s Corn Flakes 500g', 'Kissan Mixed Fruit Jam', 'MTR Ready to Eat Paneer Butter Masala', 'Del Monte Tomato Ketchup 1kg'],
    32 => ['The Alchemist by Paulo Coelho', 'Harry Potter and the Sorcerer\'s Stone', '1984 by George Orwell', 'The Kite Runner by Khaled Hosseini', 'Dune by Frank Herbert'],
    33 => ['Atomic Habits by James Clear', 'Sapiens by Yuval Noah Harari', 'Thinking, Fast and Slow', 'Rich Dad Poor Dad', 'The Power of Habit'],
    34 => ['Classmate Notebooks (Pack of 6)', 'Faber-Castell Color Pencils (24 Colors)', 'Camel Poster Colors (12 Shades)', 'Parker Vector Pen', 'Geometry Box'],
    35 => ['A4 Copier Paper (500 Sheets)', 'Post-it Sticky Notes', 'Stapler and Staple Pins Set', 'Whiteboard Markers (Pack of 4)', 'Desk File Organizer'],
    36 => ['Vogue India Magazine', 'National Geographic Magazine', 'Forbes India', 'TIME Magazine', 'Reader\'s Digest'],
    38 => ['MRF English Willow Cricket Bat', 'Kookaburra Leather Cricket Ball', 'SG Cricket Batting Gloves', 'Shrey Cricket Helmet', 'Puma Cricket Shoes'],
    39 => ['Adjustable Dumbbell Set (20kg)', 'PVC Yoga Mat (6mm)', 'Resistance Bands (Set of 5)', 'Treadmill with Incline', 'Ab Roller Wheel'],
    40 => ['High-Density EVA Yoga Mat', 'Cork Yoga Blocks (Set of 2)', 'Yoga Stretching Strap', 'Pilates Ring', 'Yoga Bolster Cushion'],
    41 => ['Shimano 21-Speed Mountain Bike', 'LED Bike Taillight', 'Bicycle Cycling Helmet', 'Cycling Water Bottle with Cage', 'Padded Cycling Shorts'],
    42 => ['Nivia Football (Size 5)', 'Spalding Basketball (Size 7)', 'Yonex Badminton Racket', 'Cosco Tennis Balls (Pack of 3)', 'Mikasa Volleyball'],
    44 => ['Marvel Avengers Iron Man Action Figure', 'Batman DC Multiverse Figure', 'Transformers Optimus Prime', 'Star Wars Darth Vader Figure', 'Dragon Ball Z Goku Figure'],
    45 => ['LEGO Classic Creative Bricks', 'Rubik\'s Cube (3x3)', 'Melissa & Doug Wooden Blocks', 'Magna-Tiles 32-Piece Set', 'Fisher-Price Rock-a-Stack'],
    46 => ['Himalaya Baby Massage Oil', 'Pampers Premium Care Diapers (Large)', 'Johnson\'s Baby Wash', 'Philips Avent Baby Bottle', 'Sebamed Baby Lotion'],
    47 => ['LuvLap Galaxy Stroller', 'Chicco Echo Stroller', 'Mee Mee Baby Pram', 'R for Rabbit Pocket Stroller', 'Graco Fold Stroller'],
    48 => ['Giant Teddy Bear (3 Feet)', 'Peppa Pig Soft Toy', 'Jellycat Bashful Bunny', 'Ty Beanie Boos Husky', 'Unicorn Plush Toy'],
    50 => ['Godrej Aer Car Freshener', 'Universal Car Phone Mount', 'Microfiber Car Cleaning Cloth', 'Car Seat Covers (Set of 4)', 'Jopasu Car Duster'],
    51 => ['Steelbird Bike Helmet', 'Bike Mobile Holder with Charger', 'Chain Lubricant Spray', 'Bike Disc Brake Lock', 'Waterproof Bike Cover'],
    52 => ['3M Car Wash Shampoo', 'Formula 1 Carnauba Car Wax', 'Turtle Wax Scratch Remover', 'Dashboard Polish', 'Tire Shine Spray'],
    53 => ['Bosch Wiper Blades', 'NGK Spark Plugs', 'K&N Air Filter', 'Motul 7100 Engine Oil', 'Brembo Brake Pads'],
    54 => ['Vega Crux Flip-up Helmet', 'Studds Professional Full Face Helmet', 'Royal Enfield Classic Open Face Helmet', 'SMK Twister Full Face Helmet', 'Axor Street Helmet'],
    56 => ['Nike Air Zoom Pegasus Running Shoes', 'Adidas Ultraboost 22', 'Puma Softride Running Shoes', 'Reebok Flexagon Energy', 'ASICS Gel-Kayano 29'],
    57 => ['Vans Old Skool Sneakers', 'Converse Chuck Taylor All Star', 'Puma Smash v2', 'Adidas Stan Smith', 'Skechers Slip-ins'],
    58 => ['Hush Puppies Leather Oxford Shoes', 'Bata Formal Derby Shoes', 'Red Tape Men\'s Brogues', 'Clarks Tilden Cap Toe Shoes', 'Ruosh Leather Loafers'],
    59 => ['Crocs Classic Clogs', 'Woodland Leather Sandals', 'Sparx Men\'s Floaters', 'Wildcraft Outdoor Sandals', 'Bata Comfit Women\'s Sandals'],
    60 => ['Flite Men\'s PU Slippers', 'Puma Popcat Slides', 'Adidas Adilette Shower Slides', 'Havaianas Flip Flops', 'Nike Victori One Slides'],
    62 => ['Centrum Multivitamin Tablets', 'MuscleBlaze Fish Oil (1000mg)', 'Supradyn Daily Multivitamin', 'Nature Made Vitamin D3', 'HealthKart Vitamin C'],
    63 => ['Omron Automatic Blood Pressure Monitor', 'Dr. Trust Pulse Oximeter', 'Accu-Chek Active Blood Glucometer', 'Digital Thermometer', 'Dr. Morepen Compressor Nebulizer'],
    64 => ['Savlon First Aid Kit', 'Hansaplast Washproof Band-Aids', 'Betadine Antiseptic Ointment', 'Crepe Bandage', 'Dettol Antiseptic Liquid'],
    65 => ['Optimum Nutrition (ON) Gold Standard Whey', 'MuscleBlaze Biozyme Performance Whey', 'Myprotein Impact Whey', 'Dymatize ISO100 Protein', 'GNC Pro Performance Whey'],
    66 => ['Dabur Chyawanprash (1kg)', 'Patanjali Ashwagandha Churna', 'Zandu Balm', 'Himalaya Liv.52 Tablets', 'Baidyanath Giloy Juice'],
    68 => ['22KT Gold Floral Necklace', '18KT Gold Diamond Ring', '22KT Gold Chain for Men', '22KT Gold Jhumka Earrings', '18KT Gold Pendant'],
    69 => ['925 Sterling Silver Ring', 'Silver Anklets (Payal)', 'Oxidized Silver Jhumkas', 'Silver Chain for Men', 'Silver Charm Bracelet'],
    70 => ['Kundan Bridal Jewellery Set', 'American Diamond Necklace Set', 'Polki Choker Set', 'Temple Jewellery Set', 'Pearl Beaded Necklace'],
    71 => ['Casio Edifice Chronograph Watch', 'Fossil Grant Chronograph Watch', 'Titan Neo Analog Watch', 'Seiko 5 Sports Automatic', 'G-Shock Digital Watch'],
    72 => ['Daniel Wellington Classic Watch', 'Fossil Rose Gold Women\'s Watch', 'Titan Raga Women\'s Watch', 'Michael Kors Runway Watch', 'Casio Vintage Digital Watch'],
];

// Delete previous dummy products generated today for this seller
$deleted = \App\Models\Product::where('user_id', $sellerId)
    ->where('title', 'LIKE', '%Variant%')
    ->where('created_at', '>=', now()->subHours(2))
    ->delete();
echo "Deleted $deleted old dummy products.\n";

echo "Generating genuine products...\n";

foreach ($childCategories as $catId => $catName) {
    if (!isset($genuineNames[$catId])) continue;
    
    $namesForCat = $genuineNames[$catId];
    
    for ($i = 0; $i < 5; $i++) {
        $condition = $conditions[array_rand($conditions)];
        $city = 'Gurugram';
        $isNegotiable = rand(0, 1) == 1;
        
        $productName = $namesForCat[$i];
        
        $price = rand(500, 25000);
        $conditionText = $condition == 1 ? 'brand new' : 'gently used';
        
        $description = "Looking to sell my $productName. It is in $conditionText condition. Perfectly suited for your needs. Highly recommended for daily use. Please contact if interested.";
        
        // Base latitude and longitude for Gurugram
        $baseLat = 28.4595;
        $baseLng = 77.0266;
        
        // Minor random offset around Gurugram (~5-10 km radius)
        $lat = $baseLat + (rand(-10000, 10000) / 100000); 
        $lng = $baseLng + (rand(-10000, 10000) / 100000);
        
        $productsToInsert[] = [
            'category_id' => $catId,
            'title' => $productName,
            'description' => $description,
            'price' => $price,
            'price_unit' => 'per piece',
            'minimum_quantity' => rand(1, 10),
            'is_negotiable' => $isNegotiable,
            'condition' => $condition,
            'location' => $city . ', India',
            'city' => $city,
            'latitude' => $lat,
            'longitude' => $lng,
            'user_id' => $sellerId,
            'status' => \App\Models\Product::STATUS_ACTIVE,
            'slug' => \Illuminate\Support\Str::slug($productName) . '-' . rand(10000, 99999),
            'expires_at' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

foreach ($productsToInsert as $data) {
    \App\Models\Product::create($data);
}

echo "Successfully seeded " . count($productsToInsert) . " products across all subcategories!\n";
