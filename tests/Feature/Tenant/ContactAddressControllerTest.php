<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\TenantModule;
use App\Models\User;
use App\Modules\Contacts\Models\Address;
use App\Modules\Contacts\Models\Contact;
use Tests\TenantTestCase;

class ContactAddressControllerTest extends TenantTestCase
{
    private User $user;

    private Company $company;

    private Contact $contact;

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

        $this->contact = Contact::factory()->create(['company_id' => $this->company->id]);
    }

    public function test_store_creates_address(): void
    {
        $data = [
            'label' => 'Main Office',
            'address_line_1' => 'Calle Mayor 1',
            'city' => 'Madrid',
            'state' => 'Madrid',
            'postal_code' => '28001',
            'country_code' => 'ES',
            'type' => 'main',
            'is_primary' => true,
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->post("/contacts/{$this->contact->id}/addresses", $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', [
            'addressable_id' => $this->contact->id,
            'addressable_type' => Contact::class,
            'address_line_1' => 'Calle Mayor 1',
            'is_primary' => true,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->post("/contacts/{$this->contact->id}/addresses", []);

        $response->assertSessionHasErrors(['address_line_1', 'country_code', 'type']);
    }

    public function test_update_modifies_address(): void
    {
        $address = Address::factory()->create([
            'company_id' => $this->company->id,
            'addressable_type' => Contact::class,
            'addressable_id' => $this->contact->id,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->put("/contacts/{$this->contact->id}/addresses/{$address->id}", [
                'address_line_1' => 'Updated Address',
                'country_code' => 'ES',
                'type' => 'billing',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'address_line_1' => 'Updated Address',
            'type' => 'billing',
        ]);
    }

    public function test_setting_primary_unsets_others(): void
    {
        $existing = Address::factory()->create([
            'company_id' => $this->company->id,
            'addressable_type' => Contact::class,
            'addressable_id' => $this->contact->id,
            'is_primary' => true,
        ]);

        $data = [
            'address_line_1' => 'New Primary',
            'country_code' => 'ES',
            'type' => 'main',
            'is_primary' => true,
        ];

        $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->post("/contacts/{$this->contact->id}/addresses", $data);

        $existing->refresh();
        $this->assertFalse($existing->is_primary);
    }

    public function test_destroy_deletes_address(): void
    {
        $address = Address::factory()->create([
            'company_id' => $this->company->id,
            'addressable_type' => Contact::class,
            'addressable_id' => $this->contact->id,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['current_company_id' => $this->company->id])
            ->delete("/contacts/{$this->contact->id}/addresses/{$address->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }
}
