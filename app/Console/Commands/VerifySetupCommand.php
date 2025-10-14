<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\DeviceCategory;
use App\Models\Technician;
use App\Models\Task;
use App\Models\Booking;
use Spatie\Permission\Models\Role;

class VerifySetupCommand extends Command
{
    protected $signature = 'verify:setup';
    protected $description = 'Verify that the system is set up correctly';

    public function handle()
    {
        $this->info('🔍 Verifying Gadget Repair System Setup...');
        $this->newLine();

        // Check database connection
        try {
            DB::connection()->getPdo();
            $this->line('✅ Database connection: OK');
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
            return 1;
        }

        // Check tables
        $this->info('📊 Checking Tables...');

        $tables = [
            'users',
            'roles',
            'model_has_roles',
            'device_categories',
            'technicians',
            'bookings',
            'tasks',
            'job_progress',
            'materials_used',
            'invoices',
            'storage_fees',
            'notifications',
            'sms_logs',
        ];

        foreach ($tables as $table) {
            try {
                DB::table($table)->count();
                $this->line("   ✅ Table '{$table}' exists");
            } catch (\Exception $e) {
                $this->error("   ❌ Table '{$table}' NOT FOUND");
            }
        }

        $this->newLine();

        // Check data
        $this->info('📈 Checking Data...');

        try {
            $roleCount = Role::count();
            $userCount = User::count();
            $categoryCount = DeviceCategory::count();
            $technicianCount = Technician::count();
            $taskCount = Task::count();
            $bookingCount = Booking::count();

            $this->table(
                ['Item', 'Count', 'Status'],
                [
                    ['Roles', $roleCount, $roleCount >= 6 ? '✅' : '❌'],
                    ['Users', $userCount, $userCount >= 10 ? '✅' : '❌'],
                    ['Device Categories', $categoryCount, $categoryCount >= 6 ? '✅' : '❌'],
                    ['Technicians', $technicianCount, $technicianCount >= 5 ? '✅' : '❌'],
                    ['Tasks', $taskCount, '✅'],
                    ['Bookings', $bookingCount, '✅'],
                ]
            );
        } catch (\Exception $e) {
            $this->error('Error checking data: ' . $e->getMessage());
        }

        $this->newLine();

        // Check test users
        $this->info('👥 Test User Credentials...');

        $testUsers = [
            'admin@gadgetrepair.com',
            'manager@gadgetrepair.com',
            'supervisor@gadgetrepair.com',
            'frontdesk@gadgetrepair.com',
            'tech1@gadgetrepair.com',
            'client@example.com',
        ];

        foreach ($testUsers as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $role = $user->roles->first()?->name ?? 'No role';
                $this->line("   ✅ {$email} ({$role})");
            } else {
                $this->error("   ❌ {$email} NOT FOUND");
            }
        }

        $this->newLine();
        $this->info('🔐 All passwords are: password');
        $this->newLine();

        // Check routes
        $this->info('🛣️  Checking Routes...');
        try {
            $routes = \Illuminate\Support\Facades\Route::getRoutes();
            $frontdeskRoutes = collect($routes)->filter(function ($route) {
                return str_contains($route->getName() ?? '', 'frontdesk');
            })->count();

            if ($frontdeskRoutes > 0) {
                $this->line("   ✅ Front desk routes registered ({$frontdeskRoutes} routes)");
            } else {
                $this->error('   ❌ No front desk routes found');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error checking routes');
        }

        $this->newLine();

        // Final status
        if ($roleCount >= 6 && $userCount >= 10 && $categoryCount >= 6) {
            $this->info('✅ System setup is COMPLETE and ready to use!');
            $this->newLine();
            $this->line('🚀 Start the server: php artisan serve');
            $this->line('🌐 Access front desk: http://127.0.0.1:8000/frontdesk');
            $this->line('🔑 Login: frontdesk@gadgetrepair.com / password');
        } else {
            $this->error('❌ System setup is INCOMPLETE. Please run:');
            $this->line('   php artisan migrate:fresh --seed');
        }

        return 0;
    }
}
