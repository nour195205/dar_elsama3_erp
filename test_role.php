<?php
// Quick test: create an admin user and check the role is saved
$user = App\Models\User::create([
    'name' => 'Test Admin',
    'email' => 'admin@test.com',
    'phone' => '01000000000',
    'hourly_rate' => 50,
    'role' => 'admin',
    'password' => bcrypt('123456'),
]);
echo "Created user role: " . $user->role . "\n";

// Update to employee
$user->update(['role' => 'employee']);
$user->refresh();
echo "After update role: " . $user->role . "\n";

// Cleanup
$user->delete();
echo "Done!\n";
