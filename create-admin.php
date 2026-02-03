<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$user = User::create([
    'name' => 'Ivan David',
    'email' => 'ivan.david@gdn-commerce.com',
    'password' => bcrypt('Check000611!')
]);

$user->assignRole('admin');

echo "Admin user created successfully!\n";
echo "Email: " . $user->email . "\n";
echo "Password: Check000611!\n";
