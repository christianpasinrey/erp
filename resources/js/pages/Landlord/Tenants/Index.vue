<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Plus, Puzzle, Pencil, Power } from 'lucide-vue-next';
import LandlordLayout from '@/layouts/landlord/LandlordLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

type Tenant = {
    id: string;
    name: string;
    plan: string;
    max_users: number | null;
    max_companies: number;
    is_active: boolean;
    trial_ends_at: string | null;
    domains: string[];
    created_at: string;
};

defineProps<{
    tenants: Tenant[];
}>();

function planVariant(plan: string) {
    const map: Record<string, string> = {
        starter: 'secondary',
        business: 'default',
        enterprise: 'destructive',
    };
    return map[plan] ?? 'secondary';
}
</script>

<template>
    <LandlordLayout :breadcrumbs="[{ title: 'Dashboard', href: '/landlord' }, { title: 'Tenants' }]">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Tenants</h1>
                <Button as-child>
                    <Link href="/landlord/tenants/create">
                        <Plus class="size-4 mr-2" />
                        New Tenant
                    </Link>
                </Button>
            </div>

            <div class="liquid-glass liquid-glass-card overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="text-left p-4 font-medium text-muted-foreground">Name</th>
                            <th class="text-left p-4 font-medium text-muted-foreground">Domain</th>
                            <th class="text-left p-4 font-medium text-muted-foreground">Plan</th>
                            <th class="text-left p-4 font-medium text-muted-foreground">Limits</th>
                            <th class="text-left p-4 font-medium text-muted-foreground">Status</th>
                            <th class="text-right p-4 font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="tenant in tenants"
                            :key="tenant.id"
                            class="border-b border-white/5 hover:bg-white/5 transition-colors"
                        >
                            <td class="p-4 font-medium">{{ tenant.name }}</td>
                            <td class="p-4 text-muted-foreground">
                                <span v-for="(d, i) in tenant.domains" :key="d">
                                    {{ d }}<span v-if="i < tenant.domains.length - 1">, </span>
                                </span>
                            </td>
                            <td class="p-4">
                                <Badge :variant="planVariant(tenant.plan) as any">
                                    {{ tenant.plan }}
                                </Badge>
                            </td>
                            <td class="p-4 text-muted-foreground">
                                {{ tenant.max_users ?? '∞' }} users / {{ tenant.max_companies }} companies
                            </td>
                            <td class="p-4">
                                <Badge :variant="tenant.is_active ? 'default' : 'secondary'">
                                    {{ tenant.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="ghost" size="icon" as-child>
                                        <Link :href="`/landlord/tenants/${tenant.id}/modules`" title="Modules">
                                            <Puzzle class="size-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="icon" as-child>
                                        <Link :href="`/landlord/tenants/${tenant.id}/edit`" title="Edit">
                                            <Pencil class="size-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </LandlordLayout>
</template>
