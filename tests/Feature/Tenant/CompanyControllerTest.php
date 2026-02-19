<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Tests\TenantTestCase;

class CompanyControllerTest extends TenantTestCase
{
    private User $admin;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();

        $role = Role::factory()->create([
            'permissions' => ['companies.manage'],
        ]);

        $this->admin = User::factory()->superadmin()->create();
        $this->admin->companies()->attach($this->company->id, ['is_default' => true]);
        $this->admin->roles()->attach($role->id, ['company_id' => $this->company->id]);
    }

    public function test_index_returns_companies_list(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['current_company_id' => $this->company->id])
            ->get('/companies');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Tenant/Companies/Index')
            ->has('companies')
        );
    }

    public function test_create_shows_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['current_company_id' => $this->company->id])
            ->get('/companies/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Tenant/Companies/Create'));
    }

    public function test_store_creates_company(): void
    {
        $data = [
            'name' => 'Test Company',
            'legal_name' => 'Test Company S.L.',
            'tax_id' => 'B12345678',
            'currency_code' => 'EUR',
            'country_code' => 'ES',
            'locale' => 'es_ES',
            'timezone' => 'Europe/Madrid',
            'fiscal_year_start' => 1,
        ];

        $response = $this->actingAs($this->admin)
            ->withSession(['current_company_id' => $this->company->id])
            ->post('/companies', $data);

        $response->assertRedirect('/companies');
        $this->assertDatabaseHas('companies', ['name' => 'Test Company']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['current_company_id' => $this->company->id])
            ->post('/companies', []);

        $response->assertSessionHasErrors(['name', 'currency_code', 'country_code']);
    }

    public function test_show_displays_company(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['current_company_id' => $this->company->id])
            ->get("/companies/{$this->company->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Tenant/Companies/Show')
            ->has('company')
        );
    }

    public function test_edit_shows_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['current_company_id' => $this->company->id])
            ->get("/companies/{$this->company->id}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Tenant/Companies/Edit'));
    }

    public function test_update_modifies_company(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['current_company_id' => $this->company->id])
            ->put("/companies/{$this->company->id}", [
                'name' => 'Updated Name',
                'currency_code' => 'USD',
                'country_code' => 'US',
            ]);

        $response->assertRedirect("/companies/{$this->company->id}");
        $this->assertDatabaseHas('companies', ['id' => $this->company->id, 'name' => 'Updated Name']);
    }

    public function test_destroy_deletes_company(): void
    {
        $toDelete = Company::factory()->create();

        $response = $this->actingAs($this->admin)
            ->withSession(['current_company_id' => $this->company->id])
            ->delete("/companies/{$toDelete->id}");

        $response->assertRedirect('/companies');
        $this->assertDatabaseMissing('companies', ['id' => $toDelete->id]);
    }

    public function test_user_assignment_and_removal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->withSession(['current_company_id' => $this->company->id])
            ->post("/companies/{$this->company->id}/users", ['user_id' => $user->id]);

        $response->assertRedirect();
        $this->assertDatabaseHas('company_user', [
            'company_id' => $this->company->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['current_company_id' => $this->company->id])
            ->delete("/companies/{$this->company->id}/users/{$user->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('company_user', [
            'company_id' => $this->company->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_access(): void
    {
        $this->get('/companies')->assertRedirect('/login');
    }

    public function test_non_admin_gets_forbidden(): void
    {
        $regularUser = User::factory()->create();
        $regularUser->companies()->attach($this->company->id, ['is_default' => true]);

        $this->actingAs($regularUser)
            ->withSession(['current_company_id' => $this->company->id])
            ->get('/companies')
            ->assertForbidden();
    }
}
