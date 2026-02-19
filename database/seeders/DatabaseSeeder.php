<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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
