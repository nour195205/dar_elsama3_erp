<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionsSeeder::class);

        User::updateOrCreate(
            ['email' => 'admin@dar.com'],
            [
                'name' => 'مسؤول النظام',
                'password' => Hash::make('password'),
                'phone' => '01000000001',
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Employee',
                'phone' => '01000000000',
                'hourly_rate' => 50,
                'password' => Hash::make('password'),
                'role' => 'employee',
                'is_active' => true,
            ]
        );

        $defaultGroup = PermissionGroup::query()->where('name', 'موظف عيادة — أساسي')->first();
        $demoUser = User::query()->where('email', 'test@example.com')->first();
        if ($defaultGroup && $demoUser && $demoUser->role !== 'admin') {
            $demoUser->permissionGroups()->syncWithoutDetaching([$defaultGroup->id]);
        }
    }
}
