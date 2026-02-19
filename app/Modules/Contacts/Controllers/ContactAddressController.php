<?php

declare(strict_types=1);

namespace App\Modules\Contacts\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Contacts\Models\Address;
use App\Modules\Contacts\Models\Contact;
use App\Modules\Contacts\Requests\StoreAddressRequest;
use App\Modules\Contacts\Requests\UpdateAddressRequest;
use Illuminate\Http\RedirectResponse;

class ContactAddressController extends Controller
{
    public function store(StoreAddressRequest $request, Contact $contact): RedirectResponse
    {
        $data = $request->validated();

        // If marking as primary, un-primary all others
        if (! empty($data['is_primary'])) {
            $contact->addresses()->update(['is_primary' => false]);
        }

        $contact->addresses()->create($data);

        return back()->with('success', __('Created successfully.'));
    }

    public function update(UpdateAddressRequest $request, Contact $contact, Address $address): RedirectResponse
    {
        $data = $request->validated();

        // If marking as primary, un-primary all others
        if (! empty($data['is_primary'])) {
            $contact->addresses()->where('id', '!=', $address->id)->update(['is_primary' => false]);
        }

        $address->update($data);

        return back()->with('success', __('Updated successfully.'));
    }

    public function destroy(Contact $contact, Address $address): RedirectResponse
    {
        $address->delete();

        return back()->with('success', __('Deleted successfully.'));
    }
}
