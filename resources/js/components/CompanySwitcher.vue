<script setup lang="ts">
import { ChevronsUpDown, Building2, Check } from 'lucide-vue-next';
import { useCompany } from '@/composables/useCompany';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenuButton,
} from '@/components/ui/sidebar';

const { currentCompany, companies, hasMultipleCompanies, switchCompany } = useCompany();
</script>

<template>
    <DropdownMenu v-if="currentCompany">
        <DropdownMenuTrigger as-child>
            <SidebarMenuButton
                size="lg"
                class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
            >
                <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                    <Building2 class="size-4" />
                </div>
                <div class="grid flex-1 text-left text-sm leading-tight">
                    <span class="truncate font-semibold">{{ currentCompany.name }}</span>
                    <span class="truncate text-xs text-muted-foreground">{{ currentCompany.currency_code }}</span>
                </div>
                <ChevronsUpDown v-if="hasMultipleCompanies" class="ml-auto size-4" />
            </SidebarMenuButton>
        </DropdownMenuTrigger>
        <DropdownMenuContent
            v-if="hasMultipleCompanies"
            class="w-[--reka-dropdown-menu-trigger-width] min-w-56"
            align="start"
        >
            <DropdownMenuLabel>Companies</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                v-for="company in companies"
                :key="company.id"
                @click="switchCompany(company.id)"
                class="gap-2"
            >
                <Building2 class="size-4" />
                <span>{{ company.name }}</span>
                <Check
                    v-if="company.id === currentCompany.id"
                    class="ml-auto size-4"
                />
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
