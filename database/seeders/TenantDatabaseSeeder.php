<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Currency;
use App\Models\Role;
use App\Models\TenantModule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCurrencies();
        $this->seedRoles();
        $this->seedCompanies();
        $this->seedUsers();
        $this->seedModules();
    }

    private function seedCurrencies(): void
    {
        $currencies = [
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimal_places' => 2],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'decimal_places' => 2],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(['code' => $currency['code']], $currency);
        }
    }

    private function seedRoles(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full access to all modules and settings.',
                'permissions' => ['*'],
                'is_system' => true,
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Can manage most operations within assigned modules.',
                'permissions' => [],
                'is_system' => true,
            ],
            [
                'name' => 'Employee',
                'slug' => 'employee',
                'description' => 'Basic access to assigned modules.',
                'permissions' => [],
                'is_system' => true,
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'description' => 'Read-only access.',
                'permissions' => [],
                'is_system' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }

    private function seedCompanies(): void
    {
        Company::updateOrCreate(['name' => 'Empresa Principal'], [
            'legal_name' => 'Empresa Principal S.L.',
            'tax_id' => 'B12345678',
            'currency_code' => 'EUR',
            'country_code' => 'ES',
            'locale' => 'es_ES',
            'timezone' => 'Europe/Madrid',
            'fiscal_year_start' => 1,
            'address' => [
                'street' => 'Calle Gran Vía, 1',
                'city' => 'Madrid',
                'state' => 'Madrid',
                'postal_code' => '28013',
                'country' => 'ES',
            ],
            'phone' => '+34 91 000 0000',
            'email' => 'info@empresa-principal.test',
            'is_active' => true,
        ]);

        Company::updateOrCreate(['name' => 'Filial Internacional'], [
            'legal_name' => 'Filial Internacional Ltd.',
            'tax_id' => 'GB123456789',
            'currency_code' => 'GBP',
            'country_code' => 'GB',
            'locale' => 'en_GB',
            'timezone' => 'Europe/London',
            'fiscal_year_start' => 4,
            'address' => [
                'street' => '10 Downing Street',
                'city' => 'London',
                'postal_code' => 'SW1A 2AA',
                'country' => 'GB',
            ],
            'phone' => '+44 20 0000 0000',
            'email' => 'info@filial-intl.test',
            'is_active' => true,
            'parent_id' => Company::where('name', 'Empresa Principal')->first()?->id,
        ]);
    }

    private function seedUsers(): void
    {
        $companies = Company::all();
        $adminRole = Role::where('slug', 'admin')->first();
        $managerRole = Role::where('slug', 'manager')->first();

        // Superadmin
        $superadmin = User::updateOrCreate(['email' => 'admin@erp.test'], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'locale' => 'es',
        ]);

        // Attach to all companies (first is default)
        foreach ($companies as $index => $company) {
            $superadmin->companies()->syncWithoutDetaching([
                $company->id => ['is_default' => $index === 0],
            ]);

            $superadmin->roles()->syncWithoutDetaching([
                $adminRole->id => ['company_id' => $company->id],
            ]);
        }

        // Regular manager user
        $manager = User::updateOrCreate(['email' => 'manager@erp.test'], [
            'name' => 'Manager User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_superadmin' => false,
            'locale' => 'es',
        ]);

        $firstCompany = $companies->first();
        if ($firstCompany) {
            $manager->companies()->syncWithoutDetaching([
                $firstCompany->id => ['is_default' => true],
            ]);

            $manager->roles()->syncWithoutDetaching([
                $managerRole->id => ['company_id' => $firstCompany->id],
            ]);
        }
    }

    private function seedModules(): void
    {
        $modules = [
            ['module' => 'ai', 'is_active' => true, 'plan' => 'free'],
            ['module' => 'contacts', 'is_active' => true, 'plan' => 'free'],
            ['module' => 'settings', 'is_active' => true, 'plan' => 'free'],
        ];

        foreach ($modules as $module) {
            TenantModule::updateOrCreate(
                ['module' => $module['module']],
                [...$module, 'activated_at' => now()],
            );
        }
    }
}
