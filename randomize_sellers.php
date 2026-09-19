<?php
$userIds = \App\Models\User::pluck('id')->toArray();

if (empty($userIds)) {
    echo "No users found.\n";
    exit;
}

$products = \App\Models\Product::where('user_id', 2)
    ->where('created_at', '>', now()->subHours(2))
    ->get();

$count = 0;
foreach($products as $p) {
    $randomUserId = $userIds[array_rand($userIds)];
    $p->update(['user_id' => $randomUserId]);
    $count++;
}

echo "Updated $count products with random sellers!\n";
