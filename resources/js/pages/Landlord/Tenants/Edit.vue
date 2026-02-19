<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import LandlordLayout from '@/layouts/landlord/LandlordLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    tenant: {
        id: string;
        name: string;
        plan: string;
        max_users: number | null;
        max_companies: number;
        is_active: boolean;
        trial_ends_at: string | null;
        domains: string[];
    };
}>();

const form = useForm({
    name: props.tenant.name,
    plan: props.tenant.plan,
    max_users: props.tenant.max_users,
    max_companies: props.tenant.max_companies,
    is_active: props.tenant.is_active,
    trial_ends_at: props.tenant.trial_ends_at ?? '',
});

function submit() {
    form.put(`/landlord/tenants/${props.tenant.id}`);
}
</script>

<template>
    <LandlordLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/landlord' },
        { title: 'Tenants', href: '/landlord/tenants' },
        { title: `Edit: ${tenant.name}` },
    ]">
        <div class="p-6 max-w-2xl">
            <h1 class="text-2xl font-bold mb-6">Edit Tenant: {{ tenant.name }}</h1>

            <form @submit.prevent="submit" class="liquid-glass liquid-glass-card p-6 space-y-4">
                <div>
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" class="mt-1" />
                    <InputError :message="form.errors.name" />
                </div>

                <div>
                    <Label for="plan">Plan</Label>
                    <select
                        id="plan"
                        v-model="form.plan"
                        class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    >
                        <option value="starter">Starter</option>
                        <option value="business">Business</option>
                        <option value="enterprise">Enterprise</option>
                    </select>
                    <InputError :message="form.errors.plan" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label for="max_users">Max Users</Label>
                        <Input id="max_users" v-model.number="form.max_users" type="number" class="mt-1" />
                        <InputError :message="form.errors.max_users" />
                    </div>
                    <div>
                        <Label for="max_companies">Max Companies</Label>
                        <Input id="max_companies" v-model.number="form.max_companies" type="number" class="mt-1" />
                        <InputError :message="form.errors.max_companies" />
                    </div>
                </div>

                <div>
                    <Label for="trial_ends_at">Trial Ends At</Label>
                    <Input id="trial_ends_at" v-model="form.trial_ends_at" type="date" class="mt-1" />
                    <InputError :message="form.errors.trial_ends_at" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="is_active"
                        :model-value="form.is_active"
                        @update:model-value="form.is_active = $event as boolean"
                    />
                    <Label for="is_active">Active</Label>
                </div>

                <div class="text-sm text-muted-foreground">
                    <strong>Domains:</strong> {{ tenant.domains.join(', ') }}
                </div>

                <div class="flex justify-end pt-4">
                    <Button type="submit" :disabled="form.processing">Update Tenant</Button>
                </div>
            </form>
        </div>
    </LandlordLayout>
</template>
