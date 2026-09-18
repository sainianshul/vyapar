<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = \App\Models\Conversation::find(2);
echo 'Buyer: '.$c->buyer_id.' Seller: '.$c->seller_id;
