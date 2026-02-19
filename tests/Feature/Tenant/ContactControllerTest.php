<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\TenantModule;
use App\Models\User;
use App\Modules\Contacts\Models\Contact;
use Tests\TenantTestCase;

class ContactControllerTest extends TenantTestCase
{
    private User $user;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->user = User::factory()->superadmin()->create();
        $this->user->companies()->attach($this->company->id, ['is_default' => true]);

        TenantModule::create([
            'module' => 'contacts',
            'is_active' => true,
        ]);
    }

    public function test_index_returns_contacts_list(): void
    {
        Contact::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->get('/contacts');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Tenant/Contacts/Index')
            ->has('contacts.data', 3)
        );
    }

    public function test_index_filters_by_type(): void
    {
        Contact::factory()->person()->count(2)->create(['company_id' => $this->company->id]);
        Contact::factory()->organization()->create(['company_id' => $this->company->id]);

        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->get('/contacts?type=person');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('contacts.data', 2));
    }

    public function test_index_searches_contacts(): void
    {
        Contact::factory()->create([
            'company_id' => $this->company->id,
            'first_name' => 'UniqueSearchName',
        ]);
        Contact::factory()->create(['company_id' => $this->company->id]);

        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->get('/contacts?search=UniqueSearchName');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('contacts.data', 1));
    }

    public function test_create_shows_form(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->get('/contacts/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Tenant/Contacts/Create'));
    }

    public function test_store_creates_person_contact(): void
    {
        $data = [
            'type' => 'person',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+34 600 000 000',
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->post('/contacts', $data);

        $response->assertRedirect('/contacts');
        $this->assertDatabaseHas('contacts', [
            'first_name' => 'John',
            'type' => 'person',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_store_creates_organization_contact(): void
    {
        $data = [
            'type' => 'organization',
            'name' => 'ACME Corp',
            'industry' => 'Technology',
            'email' => 'info@acme.com',
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->post('/contacts', $data);

        $response->assertRedirect('/contacts');
        $this->assertDatabaseHas('contacts', ['name' => 'ACME Corp', 'type' => 'organization']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->post('/contacts', ['type' => 'person']);

        $response->assertSessionHasErrors(['first_name']);
    }

    public function test_show_displays_contact(): void
    {
        $contact = Contact::factory()->create(['company_id' => $this->company->id]);

        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->get("/contacts/{$contact->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Tenant/Contacts/Show')
            ->has('contact')
        );
    }

    public function test_update_modifies_contact(): void
    {
        $contact = Contact::factory()->create([
            'company_id' => $this->company->id,
            'first_name' => 'Old',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->put("/contacts/{$contact->id}", [
                'first_name' => 'New',
            ]);

        $response->assertRedirect("/contacts/{$contact->id}");
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'first_name' => 'New']);
    }

    public function test_destroy_soft_deletes_contact(): void
    {
        $contact = Contact::factory()->create(['company_id' => $this->company->id]);

        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->delete("/contacts/{$contact->id}");

        $response->assertRedirect('/contacts');
        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    }

    public function test_contact_scoped_to_company(): void
    {
        $otherCompany = Company::factory()->create();
        $otherContact = Contact::factory()->create(['company_id' => $otherCompany->id]);

        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->get("/contacts/{$otherContact->id}");

        $response->assertNotFound();
    }
}
