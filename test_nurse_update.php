<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('role', 2)->has('nurseProfile')->first();
$data = [
    'name' => 'Test Nurse Update',
    'email' => 'nurse' . rand(1,100) . '@example.com',
    'is_available' => 1,
    'address' => 'Test Address',
    'city' => 'Test City',
    'state' => 'Test State',
    'country' => 'Test Country',
    'pincode' => '123456',
    'latitude' => 10.0,
    'longitude' => 20.0,
    'emergency_contact_phone' => '1231231234',
];

$service = app(\App\Services\Admin\NurseService::class);
try {
    $service->updateNurse($user, $data);
    echo "Success! Name is now: " . $user->fresh()->name . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
