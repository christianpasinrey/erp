<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\AssignUserRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class CompanyUserController extends Controller
{
    public function store(AssignUserRequest $request, Company $company): RedirectResponse
    {
        $company->users()->syncWithoutDetaching([$request->validated('user_id')]);

        return back()->with('success', __('Updated successfully.'));
    }

    public function destroy(Company $company, User $user): RedirectResponse
    {
        $company->users()->detach($user->id);

        return back()->with('success', __('Updated successfully.'));
    }
}
