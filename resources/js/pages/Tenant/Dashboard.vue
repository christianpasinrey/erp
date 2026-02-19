<script setup lang="ts">
import TenantLayout from '@/layouts/tenant/TenantLayout.vue';
import { LayoutDashboard, Building2, Users, Package } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { SharedErpData } from '@/types';

const page = usePage();
const erp = computed(() => page.props.erp as SharedErpData | undefined);
const company = computed(() => erp.value?.currentCompany);
const activeModules = computed(() => erp.value?.activeModules ?? []);
</script>

<template>
    <TenantLayout :breadcrumbs="[{ title: 'Dashboard' }]">
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                <div class="liquid-glass liquid-glass-card p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <Building2 class="size-5 text-muted-foreground" />
                        <span class="text-sm text-muted-foreground">Company</span>
                    </div>
                    <p class="text-xl font-bold">{{ company?.name ?? 'No company selected' }}</p>
                    <p class="text-sm text-muted-foreground mt-1">{{ company?.currency_code }} &middot; {{ company?.country_code }}</p>
                </div>

                <div class="liquid-glass liquid-glass-card p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <Package class="size-5 text-green-400" />
                        <span class="text-sm text-muted-foreground">Active Modules</span>
                    </div>
                    <p class="text-3xl font-bold">{{ activeModules.length }}</p>
                </div>

                <div class="liquid-glass liquid-glass-card p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <Users class="size-5 text-blue-400" />
                        <span class="text-sm text-muted-foreground">Role</span>
                    </div>
                    <p class="text-xl font-bold">Administrator</p>
                </div>
            </div>

            <div v-if="!company" class="liquid-glass liquid-glass-card p-8 text-center">
                <LayoutDashboard class="size-12 text-muted-foreground mx-auto mb-4" />
                <h2 class="text-lg font-semibold mb-2">Welcome to your workspace</h2>
                <p class="text-muted-foreground">Select a company to get started.</p>
            </div>
        </div>
    </TenantLayout>
</template>
