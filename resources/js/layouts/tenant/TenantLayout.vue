<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { Building2, LayoutDashboard, LogOut, Settings, Bot, ChevronDown, Check, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AppShell from '@/components/AppShell.vue';
import AppContent from '@/components/AppContent.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { BreadcrumbItem, SharedErpData } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { t } = useI18n();
const page = usePage();
const { isCurrentUrl } = useCurrentUrl();
const user = computed(() => page.props.auth?.user as { name: string; email: string } | undefined);
const erp = computed(() => page.props.erp as SharedErpData | undefined);
const permissions = computed(() => erp.value?.permissions ?? []);
const canManageCompanies = computed(() => permissions.value.includes('*') || permissions.value.includes('companies.manage'));

const currentCompany = computed(() => erp.value?.currentCompany);
const companies = computed(() => erp.value?.companies ?? []);
const moduleNav = computed(() => erp.value?.moduleNav ?? []);
const hasMultipleCompanies = computed(() => companies.value.length > 1);

const moduleIcons: Record<string, typeof Bot> = {
    ai: Bot,
    contacts: Users,
    settings: Settings,
};

function getModuleIcon(moduleSlug: string) {
    return moduleIcons[moduleSlug] ?? LayoutDashboard;
}

function switchCompany(companyId: number) {
    router.post('/company/switch', { company_id: companyId });
}
</script>

<template>
    <AppShell variant="sidebar">
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <!-- Company Switcher -->
                        <DropdownMenu v-if="hasMultipleCompanies">
                            <DropdownMenuTrigger as-child>
                                <SidebarMenuButton size="lg" class="cursor-pointer">
                                    <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                                        <Building2 class="size-4" />
                                    </div>
                                    <div class="grid flex-1 text-left text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ currentCompany?.name ?? 'Select Company' }}</span>
                                        <span class="truncate text-xs text-muted-foreground">{{ currentCompany?.currency_code }}</span>
                                    </div>
                                    <ChevronDown class="ml-auto size-4" />
                                </SidebarMenuButton>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent class="w-56" align="start">
                                <DropdownMenuItem
                                    v-for="company in companies"
                                    :key="company.id"
                                    @click="switchCompany(company.id)"
                                    class="cursor-pointer"
                                >
                                    <Check v-if="company.id === currentCompany?.id" class="mr-2 size-4 text-green-500" />
                                    <span v-else class="mr-2 size-4" />
                                    {{ company.name }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <!-- Single company display -->
                        <SidebarMenuButton v-else size="lg" as-child>
                            <Link href="/dashboard">
                                <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                                    <Building2 class="size-4" />
                                </div>
                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ currentCompany?.name ?? 'ERP' }}</span>
                                    <span class="truncate text-xs text-muted-foreground">{{ currentCompany?.currency_code }}</span>
                                </div>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <SidebarGroup class="px-2 py-0">
                    <SidebarGroupLabel>Navigation</SidebarGroupLabel>
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                as-child
                                :is-active="isCurrentUrl('/dashboard')"
                                tooltip="Dashboard"
                            >
                                <Link href="/dashboard">
                                    <LayoutDashboard />
                                    <span>Dashboard</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroup>

                <!-- Module navigation -->
                <SidebarGroup v-if="moduleNav.length > 0" class="px-2 py-0">
                    <SidebarGroupLabel>{{ t('nav.modules') }}</SidebarGroupLabel>
                    <SidebarMenu>
                        <SidebarMenuItem v-for="item in moduleNav" :key="item.route">
                            <SidebarMenuButton
                                as-child
                                :is-active="isCurrentUrl(item.route)"
                                :tooltip="item.label"
                            >
                                <Link :href="item.route">
                                    <component :is="getModuleIcon(item.module)" />
                                    <span>{{ item.label }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroup>

                <!-- Admin section -->
                <SidebarGroup v-if="canManageCompanies" class="px-2 py-0">
                    <SidebarGroupLabel>{{ t('nav.admin') }}</SidebarGroupLabel>
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton
                                as-child
                                :is-active="isCurrentUrl('/companies')"
                                :tooltip="t('company.companies')"
                            >
                                <Link href="/companies">
                                    <Building2 />
                                    <span>{{ t('company.companies') }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroup>
            </SidebarContent>

            <SidebarFooter>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child>
                            <Link href="/logout" method="post" as="button" class="w-full">
                                <LogOut />
                                <span>{{ user?.name ?? 'Logout' }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>

        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
