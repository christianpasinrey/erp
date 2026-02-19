<script setup lang="ts">
import LandlordLayout from '@/layouts/landlord/LandlordLayout.vue';
import { Building2, Globe, Users, Zap } from 'lucide-vue-next';

type Stats = {
    total_tenants: number;
    active_tenants: number;
    trial_tenants: number;
    total_domains: number;
    plans: {
        starter: number;
        business: number;
        enterprise: number;
    };
};

defineProps<{
    stats: Stats;
}>();
</script>

<template>
    <LandlordLayout :breadcrumbs="[{ title: 'Dashboard' }]">
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Platform Overview</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="liquid-glass liquid-glass-card p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <Building2 class="size-5 text-muted-foreground" />
                        <span class="text-sm text-muted-foreground">Total Tenants</span>
                    </div>
                    <p class="text-3xl font-bold">{{ stats.total_tenants }}</p>
                </div>

                <div class="liquid-glass liquid-glass-card p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <Zap class="size-5 text-green-400" />
                        <span class="text-sm text-muted-foreground">Active</span>
                    </div>
                    <p class="text-3xl font-bold">{{ stats.active_tenants }}</p>
                </div>

                <div class="liquid-glass liquid-glass-card p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <Users class="size-5 text-yellow-400" />
                        <span class="text-sm text-muted-foreground">On Trial</span>
                    </div>
                    <p class="text-3xl font-bold">{{ stats.trial_tenants }}</p>
                </div>

                <div class="liquid-glass liquid-glass-card p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <Globe class="size-5 text-blue-400" />
                        <span class="text-sm text-muted-foreground">Domains</span>
                    </div>
                    <p class="text-3xl font-bold">{{ stats.total_domains }}</p>
                </div>
            </div>

            <div class="liquid-glass liquid-glass-card p-6">
                <h2 class="text-lg font-semibold mb-4">Plans Distribution</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold">{{ stats.plans.starter }}</p>
                        <p class="text-sm text-muted-foreground">Starter</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold">{{ stats.plans.business }}</p>
                        <p class="text-sm text-muted-foreground">Business</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold">{{ stats.plans.enterprise }}</p>
                        <p class="text-sm text-muted-foreground">Enterprise</p>
                    </div>
                </div>
            </div>
        </div>
    </LandlordLayout>
</template>
