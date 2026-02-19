<?php

declare(strict_types=1);

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Stancl\Tenancy\Database\Models\Domain;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $tenants = Tenant::all();

        return Inertia::render('Landlord/Dashboard', [
            'stats' => [
                'total_tenants' => $tenants->count(),
                'active_tenants' => $tenants->where('is_active', true)->count(),
                'trial_tenants' => $tenants->filter(fn ($t) => $t->isOnTrial())->count(),
                'total_domains' => Domain::count(),
                'plans' => [
                    'starter' => $tenants->where('plan', 'starter')->count(),
                    'business' => $tenants->where('plan', 'business')->count(),
                    'enterprise' => $tenants->where('plan', 'enterprise')->count(),
                ],
            ],
        ]);
    }
}
