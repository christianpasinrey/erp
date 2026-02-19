<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import TenantLayout from '@/layouts/tenant/TenantLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { CompanyFull } from '@/types';

const { t } = useI18n();

defineProps<{
    companies: CompanyFull[];
}>();

function deleteCompany(company: CompanyFull) {
    if (confirm(t('company.deleteConfirm'))) {
        router.delete(`/companies/${company.id}`);
    }
}
</script>

<template>
    <TenantLayout :breadcrumbs="[{ title: t('nav.admin') }, { title: t('company.companies') }]">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">{{ t('company.companies') }}</h1>
                <Button as-child>
                    <Link href="/companies/create">
                        <Plus class="size-4 mr-2" />
                        {{ t('company.newCompany') }}
                    </Link>
                </Button>
            </div>

            <div class="overflow-x-auto rounded-lg border">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium">{{ t('common.name') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ t('company.legalName') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ t('company.taxId') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ t('company.currency') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ t('company.users') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ t('common.status') }}</th>
                            <th class="px-4 py-3 text-right font-medium">{{ t('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="company in companies" :key="company.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium">{{ company.name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ company.legal_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ company.tax_id ?? '—' }}</td>
                            <td class="px-4 py-3">{{ company.currency_code }}</td>
                            <td class="px-4 py-3">{{ company.users_count ?? 0 }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="company.is_active ? 'default' : 'secondary'">
                                    {{ company.is_active ? t('common.active') : t('common.inactive') }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="`/companies/${company.id}`">
                                            <Eye class="size-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="`/companies/${company.id}/edit`">
                                            <Pencil class="size-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="sm" @click="deleteCompany(company)">
                                        <Trash2 class="size-4 text-destructive" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="companies.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                {{ t('common.noResults') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </TenantLayout>
</template>
