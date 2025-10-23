<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\DeviceCategory;
use App\Models\Technician;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Starting database seeding...');

        // Create Roles
        $this->command->info('Creating roles...');
        $roles = ['admin', 'manager', 'supervisor', 'front_desk', 'technician', 'client'];
        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $this->command->line("  ✓ Role created: {$roleName}");
        }

        // Create Admin User
        $this->command->info('Creating admin user...');
        $admin = User::firstOrCreate(
            ['email' => 'admin@gadgetrepair.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'phone' => '+263771234567',
                'address' => '123 Main Street',
                'city' => 'Harare',
                'country' => 'Zimbabwe',
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
        $this->command->line("  ✓ Admin created: {$admin->email}");

        // Create Manager
        $this->command->info('Creating manager user...');
        $manager = User::firstOrCreate(
            ['email' => 'manager@gadgetrepair.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password'),
                'phone' => '+263774888464',
                'address' => '124 Main Street',
                'city' => 'Harare',
                'country' => 'Zimbabwe',
            ]
        );
        if (!$manager->hasRole('manager')) {
            $manager->assignRole('manager');
        }
        $this->command->line("  ✓ Manager created: {$manager->email}");

        // Create Supervisor
        $this->command->info('Creating supervisor user...');
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@gadgetrepair.com'],
            [
                'name' => 'Supervisor User',
                'password' => Hash::make('password'),
                'phone' => '+263774888464',
                'address' => '125 Main Street',
                'city' => 'Harare',
                'country' => 'Zimbabwe',
            ]
        );
        if (!$supervisor->hasRole('supervisor')) {
            $supervisor->assignRole('supervisor');
        }
        $this->command->line("  ✓ Supervisor created: {$supervisor->email}");

        // Create Front Desk
        $this->command->info('Creating front desk user...');
        $frontDesk = User::firstOrCreate(
            ['email' => 'frontdesk@gadgetrepair.com'],
            [
                'name' => 'Front Desk User',
                'password' => Hash::make('password'),
                'phone' => '+263774888464',
                'address' => '126 Main Street',
                'city' => 'Harare',
                'country' => 'Zimbabwe',
            ]
        );
        if (!$frontDesk->hasRole('front_desk')) {
            $frontDesk->assignRole('front_desk');
        }
        $this->command->line("  ✓ Front Desk created: {$frontDesk->email}");

        // Create Device Categories
        $this->command->info('Creating device categories...');
        $categories = [
            ['name' => 'Mobile Phone', 'code' => 'PHN', 'service_cost' => 15.00, 'size' => 'small'],
            ['name' => 'Laptop', 'code' => 'LPT', 'service_cost' => 35.00, 'size' => 'large'],
            ['name' => 'Tablet', 'code' => 'TAB', 'service_cost' => 20.00, 'size' => 'medium'],
            ['name' => 'Desktop', 'code' => 'DSK', 'service_cost' => 40.00, 'size' => 'large'],
            ['name' => 'Smartwatch', 'code' => 'SWT', 'service_cost' => 10.00, 'size' => 'small'],
            ['name' => 'Gaming Console', 'code' => 'GMC', 'service_cost' => 30.00, 'size' => 'medium'],
        ];

        foreach ($categories as $category) {
            DeviceCategory::firstOrCreate(
                ['code' => $category['code']],
                $category
            );
            $this->command->line("  ✓ Category created: {$category['name']}");
        }

        // Get all categories for technician specializations
        $techCategories = DeviceCategory::all();

        // Create Sample Technicians
        $this->command->info('Creating technicians...');
        for ($i = 1; $i <= 5; $i++) {
            $tech = User::firstOrCreate(
                ['email' => "tech$i@gadgetrepair.com"],
                [
                    'name' => "Technician $i",
                    'password' => Hash::make('password'),
                    'phone' => "+26377123456$i",
                    'address' => "12$i Main Street",
                    'city' => 'Harare',
                    'country' => 'Zimbabwe',
                ]
            );

            if (!$tech->hasRole('technician')) {
                $tech->assignRole('technician');
            }

            // Assign random specializations (2-3 categories per technician)
            $specializations = $techCategories->random(rand(2, 3))->pluck('id')->toArray();

            Technician::firstOrCreate(
                ['user_id' => $tech->id],
                [
                    'specializations' => $specializations,
                    'max_workload' => 10,
                    'is_available' => true,
                    'hourly_rate' => rand(15, 30),
                ]
            );
            $this->command->line("  ✓ Technician created: {$tech->email}");
        }

        // Create Sample Client
        $this->command->info('Creating sample client...');
        $client = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'phone' => '+263771234599',
                'address' => '456 Client Avenue',
                'city' => 'Harare',
                'country' => 'Zimbabwe',
            ]
        );
        if (!$client->hasRole('client')) {
            $client->assignRole('client');
        }
        $this->command->line("  ✓ Client created: {$client->email}");

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('📋 Test Credentials:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin', 'admin@gadgetrepair.com', 'password'],
                ['Manager', 'manager@gadgetrepair.com', 'password'],
                ['Supervisor', 'supervisor@gadgetrepair.com', 'password'],
                ['Front Desk', 'frontdesk@gadgetrepair.com', 'password'],
                ['Technician', 'tech1@gadgetrepair.com', 'password'],
                ['Client', 'client@example.com', 'password'],
            ]
        );
    }
}
