<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import LandlordLayout from '@/layouts/landlord/LandlordLayout.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

type Module = {
    id: string;
    name: string;
    is_active: boolean;
};

const props = defineProps<{
    tenant: {
        id: string;
        name: string;
    };
    modules: Module[];
}>();

const form = useForm({
    modules: Object.fromEntries(
        props.modules.map((m) => [m.id, m.is_active]),
    ),
});

function submit() {
    form.put(`/landlord/tenants/${props.tenant.id}/modules`);
}
</script>

<template>
    <LandlordLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/landlord' },
        { title: 'Tenants', href: '/landlord/tenants' },
        { title: `${tenant.name}: Modules` },
    ]">
        <div class="p-6 max-w-2xl">
            <h1 class="text-2xl font-bold mb-6">Modules for {{ tenant.name }}</h1>

            <form @submit.prevent="submit" class="liquid-glass liquid-glass-card p-6 space-y-3">
                <div
                    v-for="mod in modules"
                    :key="mod.id"
                    class="flex items-center justify-between rounded-lg border border-white/5 p-4 hover:bg-white/5 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <Checkbox
                            :id="`mod-${mod.id}`"
                            :model-value="form.modules[mod.id]"
                            @update:model-value="form.modules[mod.id] = $event as boolean"
                        />
                        <Label :for="`mod-${mod.id}`" class="cursor-pointer">
                            <span class="font-medium">{{ mod.name }}</span>
                            <span class="ml-2 text-xs text-muted-foreground">({{ mod.id }})</span>
                        </Label>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <Button type="submit" :disabled="form.processing">Save Modules</Button>
                </div>
            </form>
        </div>
    </LandlordLayout>
</template>
