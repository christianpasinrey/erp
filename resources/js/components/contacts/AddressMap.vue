<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import type { Address } from '@/types';

const props = defineProps<{
    addresses: Address[];
}>();

const mapReady = ref(false);
const LMap = ref<ReturnType<typeof defineComponent>>();
const LTileLayer = ref<ReturnType<typeof defineComponent>>();
const LMarker = ref<ReturnType<typeof defineComponent>>();
const LPopup = ref<ReturnType<typeof defineComponent>>();

const geoAddresses = computed(() =>
    props.addresses.filter((a) => a.latitude && a.longitude),
);

const center = computed<[number, number]>(() => {
    if (geoAddresses.value.length > 0) {
        const first = geoAddresses.value[0];
        return [first.latitude!, first.longitude!];
    }
    return [40.4168, -3.7038]; // Madrid default
});

onMounted(async () => {
    try {
        const leafletModule = await import('@vue-leaflet/vue-leaflet');
        LMap.value = leafletModule.LMap;
        LTileLayer.value = leafletModule.LTileLayer;
        LMarker.value = leafletModule.LMarker;
        LPopup.value = leafletModule.LPopup;
        mapReady.value = true;
    } catch {
        // Leaflet not available
    }
});
</script>

<template>
    <div v-if="geoAddresses.length > 0 && mapReady" class="h-64 rounded-lg overflow-hidden border">
        <component
            :is="LMap"
            :zoom="13"
            :center="center"
            :use-global-leaflet="false"
            class="h-full w-full"
        >
            <component
                :is="LTileLayer"
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            />
            <component
                :is="LMarker"
                v-for="address in geoAddresses"
                :key="address.id"
                :lat-lng="[address.latitude!, address.longitude!]"
            >
                <component :is="LPopup">
                    <div class="text-sm">
                        <p v-if="address.label" class="font-medium">{{ address.label }}</p>
                        <p>{{ address.address_line_1 }}</p>
                        <p class="text-muted-foreground">
                            {{ [address.city, address.state].filter(Boolean).join(', ') }}
                        </p>
                    </div>
                </component>
            </component>
        </component>
    </div>
</template>
