<script setup lang="ts">
import { Link, useForm, router } from '@inertiajs/vue3';
import { Pencil, Trash2, Upload, UserPlus, UserMinus } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import TenantLayout from '@/layouts/tenant/TenantLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { CompanyFull } from '@/types';

const { t } = useI18n();

const props = defineProps<{
    company: CompanyFull;
}>();

const activeTab = ref<'overview' | 'users'>('overview');

const assignForm = useForm({
    user_id: '',
});

function assignUser() {
    assignForm.post(`/companies/${props.company.id}/users`, {
        onSuccess: () => assignForm.reset(),
    });
}

function removeUser(userId: number) {
    if (confirm(t('common.areYouSure'))) {
        router.delete(`/companies/${props.company.id}/users/${userId}`);
    }
}

function deleteCompany() {
    if (confirm(t('company.deleteConfirm'))) {
        router.delete(`/companies/${props.company.id}`);
    }
}

const logoInput = ref<HTMLInputElement>();

function uploadLogo() {
    const file = logoInput.value?.files?.[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('logo', file);

    router.post(`/companies/${props.company.id}/logo`, formData as never);
}
</script>

<template>
    <TenantLayout :breadcrumbs="[{ title: t('nav.admin') }, { title: t('company.companies'), href: '/companies' }, { title: company.name }]">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">{{ company.name }}</h1>
                    <p v-if="company.legal_name" class="text-muted-foreground">{{ company.legal_name }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/companies/${company.id}/edit`">
                            <Pencil class="size-4 mr-2" />
                            {{ t('common.edit') }}
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="deleteCompany">
                        <Trash2 class="size-4 mr-2" />
                        {{ t('common.delete') }}
                    </Button>
                </div>
            </div>

            <!-- Tab navigation -->
            <div class="flex gap-1 border-b mb-6">
                <button
                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
                    :class="activeTab === 'overview' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    @click="activeTab = 'overview'"
                >
                    {{ t('company.overview') }}
                </button>
                <button
                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
                    :class="activeTab === 'users' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    @click="activeTab = 'users'"
                >
                    {{ t('company.users') }} ({{ company.users_count ?? 0 }})
                </button>
            </div>

            <!-- Overview tab -->
            <div v-if="activeTab === 'overview'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('company.companyDetails') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('company.taxId') }}</span>
                            <span>{{ company.tax_id ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('company.currency') }}</span>
                            <span>{{ company.currency_code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('company.country') }}</span>
                            <span>{{ company.country_code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('company.locale') }}</span>
                            <span>{{ company.locale }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('company.timezone') }}</span>
                            <span>{{ company.timezone }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('company.fiscalYearStart') }}</span>
                            <span>{{ company.fiscal_year_start }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('common.status') }}</span>
                            <Badge :variant="company.is_active ? 'default' : 'secondary'">
                                {{ company.is_active ? t('common.active') : t('common.inactive') }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('contacts.details') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('common.phone') }}</span>
                            <span>{{ company.phone ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('common.email') }}</span>
                            <span>{{ company.email ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('company.website') }}</span>
                            <span>{{ company.website ?? '—' }}</span>
                        </div>
                        <div class="mt-4">
                            <Label class="mb-2 block">{{ t('company.logo') }}</Label>
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="company.logo_path"
                                    :src="`/storage/${company.logo_path}`"
                                    :alt="company.name"
                                    class="size-16 rounded-lg object-cover border"
                                />
                                <div v-else class="size-16 rounded-lg bg-muted flex items-center justify-center text-muted-foreground text-xs">
                                    Logo
                                </div>
                                <div>
                                    <input ref="logoInput" type="file" accept="image/*" class="hidden" @change="uploadLogo" />
                                    <Button variant="outline" size="sm" @click="logoInput?.click()">
                                        <Upload class="size-4 mr-2" />
                                        {{ t('company.uploadLogo') }}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Users tab -->
            <div v-if="activeTab === 'users'">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('company.assignUser') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form class="flex items-end gap-3" @submit.prevent="assignUser">
                            <div class="space-y-2 flex-1">
                                <Label for="user_id">User ID</Label>
                                <Input id="user_id" v-model="assignForm.user_id" type="number" placeholder="User ID" />
                                <p v-if="assignForm.errors.user_id" class="text-sm text-destructive">{{ assignForm.errors.user_id }}</p>
                            </div>
                            <Button type="submit" :disabled="assignForm.processing">
                                <UserPlus class="size-4 mr-2" />
                                {{ t('company.assignUser') }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <div class="mt-4 overflow-x-auto rounded-lg border">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium">ID</th>
                                <th class="px-4 py-3 text-left font-medium">{{ t('common.name') }}</th>
                                <th class="px-4 py-3 text-left font-medium">{{ t('common.email') }}</th>
                                <th class="px-4 py-3 text-right font-medium">{{ t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in company.users" :key="user.id" class="border-b last:border-0 hover:bg-muted/30">
                                <td class="px-4 py-3">{{ user.id }}</td>
                                <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ user.email }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Button variant="ghost" size="sm" @click="removeUser(user.id)">
                                        <UserMinus class="size-4 text-destructive" />
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="!company.users?.length">
                                <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                                    {{ t('common.noResults') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
