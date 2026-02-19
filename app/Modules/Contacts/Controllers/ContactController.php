<?php

declare(strict_types=1);

namespace App\Modules\Contacts\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Contacts\Models\Contact;
use App\Modules\Contacts\Requests\StoreContactRequest;
use App\Modules\Contacts\Requests\UpdateContactRequest;
use App\Modules\Contacts\Resources\ContactResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(Request $request): Response
    {
        $contacts = Contact::query()
            ->search($request->input('search'))
            ->when($request->input('type'), fn ($q, $type) => $q->where('type', $type))
            ->with('organization:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Tenant/Contacts/Index', [
            'contacts' => ContactResource::collection($contacts),
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function create(Request $request): Response
    {
        $organizations = Contact::query()
            ->organizations()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Tenant/Contacts/Create', [
            'organizations' => $organizations,
            'defaultType' => $request->input('type', 'person'),
        ]);
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        Contact::create($request->validated());

        return redirect()
            ->route('contacts.index')
            ->with('success', __('Created successfully.'));
    }

    public function show(Contact $contact): Response
    {
        $contact->load(['organization:id,name', 'members:id,first_name,last_name,email,job_title', 'addresses']);

        return Inertia::render('Tenant/Contacts/Show', [
            'contact' => new ContactResource($contact),
        ]);
    }

    public function edit(Contact $contact): Response
    {
        $organizations = Contact::query()
            ->organizations()
            ->where('id', '!=', $contact->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Tenant/Contacts/Edit', [
            'contact' => new ContactResource($contact),
            'organizations' => $organizations,
        ]);
    }

    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        $contact->update($request->validated());

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', __('Updated successfully.'));
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with('success', __('Deleted successfully.'));
    }
}
