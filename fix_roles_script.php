<?php

use App\Models\User;
use App\Models\Store;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting role fix...\n";

// 1. Fix Admin
$adminEmail = 'admin@gmail.com';
$admin = User::where('email', $adminEmail)->first();
if ($admin) {
    $admin->role = 'admin';
    $admin->is_active = true;
    $admin->save();
    echo "SUCCESS: User $adminEmail is now ADMIN.\n";
} else {
    echo "WARNING: User $adminEmail not found.\n";
}

// 2. Fix Vendor
$vendorEmail = 'seller@gmail.com'; // Change this if you use another email
$vendor = User::where('email', $vendorEmail)->first();
if ($vendor) {
    $vendor->role = 'vendor';
    $vendor->is_active = true;
    $vendor->save();
    echo "SUCCESS: User $vendorEmail is now VENDOR.\n";

    // Ensure store exists
    if (!$vendor->store) {
        Store::create([
            'user_id' => $vendor->id,
            'name' => 'My Great Store',
            'slug' => Str::slug('My Great Store'),
            'description' => 'A great place to shop.',
            'logo' => null,
            'is_completed' => true
        ]);
        echo "SUCCESS: Created store for $vendorEmail.\n";
    } else {
        echo "INFO: Store already exists for $vendorEmail.\n";
    }
} else {
    echo "WARNING: User $vendorEmail not found.\n";
}

echo "Role fix completed.\n";
