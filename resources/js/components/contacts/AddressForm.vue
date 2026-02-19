<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { useWorldData } from '@/composables/useWorldData';

const { t } = useI18n();

const model = defineModel<{
    label: string;
    address_line_1: string;
    address_line_2: string;
    city: string;
    state: string;
    postal_code: string;
    country_code: string;
    latitude: number | string;
    longitude: number | string;
    is_primary: boolean;
    type: string;
}>({ required: true });

defineProps<{
    errors?: Record<string, string>;
}>();

const { countries, states, cities, fetchCountries, fetchStates, fetchCities } = useWorldData();

onMounted(() => {
    fetchCountries();
    if (model.value.country_code) {
        fetchStates(model.value.country_code);
    }
});

watch(() => model.value.country_code, (code) => {
    if (code) {
        fetchStates(code);
        model.value.state = '';
        model.value.city = '';
    }
});

const addressTypes = [
    { value: 'main', label: () => t('addresses.main') },
    { value: 'billing', label: () => t('addresses.billing') },
    { value: 'shipping', label: () => t('addresses.shipping') },
    { value: 'other', label: () => t('addresses.other') },
];
</script>

<template>
    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <Label for="addr_label">{{ t('addresses.label') }}</Label>
                <Input id="addr_label" v-model="model.label" placeholder="e.g. Main Office, Home" />
            </div>
            <div class="space-y-2">
                <Label for="addr_type">{{ t('common.type') }}</Label>
                <select
                    id="addr_type"
                    v-model="model.type"
                    class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                >
                    <option v-for="at in addressTypes" :key="at.value" :value="at.value">
                        {{ at.label() }}
                    </option>
                </select>
            </div>
        </div>

        <div class="space-y-2">
            <Label for="addr_line1">{{ t('addresses.addressLine1') }} *</Label>
            <Input id="addr_line1" v-model="model.address_line_1" :class="{ 'border-destructive': errors?.address_line_1 }" />
            <p v-if="errors?.address_line_1" class="text-sm text-destructive">{{ errors.address_line_1 }}</p>
        </div>

        <div class="space-y-2">
            <Label for="addr_line2">{{ t('addresses.addressLine2') }}</Label>
            <Input id="addr_line2" v-model="model.address_line_2" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-2">
                <Label for="addr_country">{{ t('addresses.country') }} *</Label>
                <select
                    id="addr_country"
                    v-model="model.country_code"
                    class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                >
                    <option value="">{{ t('addresses.selectCountry') }}</option>
                    <option v-for="c in countries" :key="c.iso2" :value="c.iso2">
                        {{ c.name }}
                    </option>
                </select>
                <p v-if="errors?.country_code" class="text-sm text-destructive">{{ errors.country_code }}</p>
            </div>
            <div class="space-y-2">
                <Label for="addr_state">{{ t('addresses.state') }}</Label>
                <select
                    v-if="states.length > 0"
                    id="addr_state"
                    v-model="model.state"
                    class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                >
                    <option value="">{{ t('addresses.selectState') }}</option>
                    <option v-for="s in states" :key="s.id" :value="s.name">
                        {{ s.name }}
                    </option>
                </select>
                <Input v-else id="addr_state" v-model="model.state" />
            </div>
            <div class="space-y-2">
                <Label for="addr_city">{{ t('addresses.city') }}</Label>
                <Input id="addr_city" v-model="model.city" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-2">
                <Label for="addr_postal">{{ t('addresses.postalCode') }}</Label>
                <Input id="addr_postal" v-model="model.postal_code" />
            </div>
            <div class="space-y-2">
                <Label for="addr_lat">Latitude</Label>
                <Input id="addr_lat" v-model="model.latitude" type="number" step="0.0000001" />
            </div>
            <div class="space-y-2">
                <Label for="addr_lng">Longitude</Label>
                <Input id="addr_lng" v-model="model.longitude" type="number" step="0.0000001" />
            </div>
        </div>

        <div class="flex items-center gap-2">
            <Checkbox
                id="addr_primary"
                :checked="model.is_primary"
                @update:checked="(val: boolean) => model.is_primary = val"
            />
            <Label for="addr_primary" class="cursor-pointer">{{ t('addresses.isPrimary') }}</Label>
        </div>
    </div>
</template>
