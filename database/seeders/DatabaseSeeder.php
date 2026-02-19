<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Central/Landlord admin user (for erp.test login)
        User::updateOrCreate(
            ['email' => 'landlord@erp.test'],
            [
                'name' => 'Landlord Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_superadmin' => true,
                'is_active' => true,
                'locale' => 'es',
            ]
        );

        $tenant = Tenant::firstOrCreate(['id' => 'demo'], [
            'name' => 'Demo Tenant',
            'plan' => 'enterprise',
            'max_users' => 50,
            'max_companies' => 5,
            'is_active' => true,
        ]);

        $tenant->domains()->firstOrCreate(['domain' => 'demo.erp.test']);

        // Run the tenant seeder inside the tenant context
        $tenant->run(function () {
            $this->call(TenantDatabaseSeeder::class);
        });
    }
}
