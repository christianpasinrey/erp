<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import TenantLayout from '@/layouts/tenant/TenantLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const { t } = useI18n();

const form = useForm({
    name: '',
    legal_name: '',
    tax_id: '',
    currency_code: 'EUR',
    country_code: 'ES',
    locale: 'es_ES',
    timezone: 'Europe/Madrid',
    fiscal_year_start: 1,
    phone: '',
    email: '',
    website: '',
});

function submit() {
    form.post('/companies');
}
</script>

<template>
    <TenantLayout :breadcrumbs="[{ title: t('nav.admin') }, { title: t('company.companies'), href: '/companies' }, { title: t('company.newCompany') }]">
        <div class="p-6 max-w-3xl">
            <h1 class="text-2xl font-bold mb-6">{{ t('company.newCompany') }}</h1>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('company.companyDetails') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="name">{{ t('common.name') }} *</Label>
                                <Input id="name" v-model="form.name" :class="{ 'border-destructive': form.errors.name }" />
                                <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="legal_name">{{ t('company.legalName') }}</Label>
                                <Input id="legal_name" v-model="form.legal_name" />
                                <p v-if="form.errors.legal_name" class="text-sm text-destructive">{{ form.errors.legal_name }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="tax_id">{{ t('company.taxId') }}</Label>
                                <Input id="tax_id" v-model="form.tax_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="currency_code">{{ t('company.currency') }} *</Label>
                                <Input id="currency_code" v-model="form.currency_code" maxlength="3" />
                                <p v-if="form.errors.currency_code" class="text-sm text-destructive">{{ form.errors.currency_code }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="country_code">{{ t('company.country') }} *</Label>
                                <Input id="country_code" v-model="form.country_code" maxlength="2" />
                                <p v-if="form.errors.country_code" class="text-sm text-destructive">{{ form.errors.country_code }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="locale">{{ t('company.locale') }}</Label>
                                <Input id="locale" v-model="form.locale" />
                            </div>
                            <div class="space-y-2">
                                <Label for="timezone">{{ t('company.timezone') }}</Label>
                                <Input id="timezone" v-model="form.timezone" />
                            </div>
                            <div class="space-y-2">
                                <Label for="fiscal_year_start">{{ t('company.fiscalYearStart') }}</Label>
                                <Input id="fiscal_year_start" v-model="form.fiscal_year_start" type="number" min="1" max="12" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="phone">{{ t('common.phone') }}</Label>
                                <Input id="phone" v-model="form.phone" />
                            </div>
                            <div class="space-y-2">
                                <Label for="email">{{ t('common.email') }}</Label>
                                <Input id="email" v-model="form.email" type="email" />
                            </div>
                            <div class="space-y-2">
                                <Label for="website">{{ t('company.website') }}</Label>
                                <Input id="website" v-model="form.website" type="url" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex items-center gap-3 mt-6">
                    <Button type="submit" :disabled="form.processing">
                        {{ t('common.save') }}
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/companies">{{ t('common.cancel') }}</Link>
                    </Button>
                </div>
            </form>
        </div>
    </TenantLayout>
</template>
