<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import LandlordLayout from '@/layouts/landlord/LandlordLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

const form = useForm({
    name: '',
    domain: '',
    plan: 'starter',
    max_users: null as number | null,
    max_companies: 1,
    trial_ends_at: '',
});

function submit() {
    form.post('/landlord/tenants');
}
</script>

<template>
    <LandlordLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/landlord' },
        { title: 'Tenants', href: '/landlord/tenants' },
        { title: 'Create' },
    ]">
        <div class="p-6 max-w-2xl">
            <h1 class="text-2xl font-bold mb-6">Create Tenant</h1>

            <form @submit.prevent="submit" class="liquid-glass liquid-glass-card p-6 space-y-4">
                <div>
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" class="mt-1" />
                    <InputError :message="form.errors.name" />
                </div>

                <div>
                    <Label for="domain">Domain</Label>
                    <Input id="domain" v-model="form.domain" placeholder="acme.erp.test" class="mt-1" />
                    <InputError :message="form.errors.domain" />
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

                <div class="flex justify-end pt-4">
                    <Button type="submit" :disabled="form.processing">Create Tenant</Button>
                </div>
            </form>
        </div>
    </LandlordLayout>
</template>
