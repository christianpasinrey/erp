<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import TenantLayout from '@/layouts/tenant/TenantLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { ContactType, OrganizationOption } from '@/types';

const { t } = useI18n();

const props = defineProps<{
    organizations: OrganizationOption[];
    defaultType: ContactType;
}>();

const contactType = ref<ContactType>(props.defaultType);

const form = useForm({
    type: props.defaultType,
    first_name: '',
    last_name: '',
    job_title: '',
    organization_id: '' as string | number,
    name: '',
    industry: '',
    email: '',
    phone: '',
    mobile: '',
    website: '',
    notes: '',
    tags: [] as string[],
});

watch(contactType, (val) => {
    form.type = val;
});

const tagInput = ref('');

function addTag() {
    const tag = tagInput.value.trim();
    if (tag && !form.tags.includes(tag)) {
        form.tags.push(tag);
    }
    tagInput.value = '';
}

function removeTag(index: number) {
    form.tags.splice(index, 1);
}

function submit() {
    form.post('/contacts');
}
</script>

<template>
    <TenantLayout :breadcrumbs="[{ title: t('contacts.contacts'), href: '/contacts' }, { title: t('contacts.newContact') }]">
        <div class="p-6 max-w-3xl">
            <h1 class="text-2xl font-bold mb-6">{{ t('contacts.newContact') }}</h1>

            <!-- Type selector -->
            <div class="flex gap-2 mb-6">
                <Button
                    :variant="contactType === 'person' ? 'default' : 'outline'"
                    @click="contactType = 'person'"
                >
                    {{ t('contacts.person') }}
                </Button>
                <Button
                    :variant="contactType === 'organization' ? 'default' : 'outline'"
                    @click="contactType = 'organization'"
                >
                    {{ t('contacts.organization') }}
                </Button>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ contactType === 'person' ? t('contacts.person') : t('contacts.organization') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Person fields -->
                        <template v-if="contactType === 'person'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="first_name">{{ t('contacts.firstName') }} *</Label>
                                    <Input id="first_name" v-model="form.first_name" :class="{ 'border-destructive': form.errors.first_name }" />
                                    <p v-if="form.errors.first_name" class="text-sm text-destructive">{{ form.errors.first_name }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="last_name">{{ t('contacts.lastName') }}</Label>
                                    <Input id="last_name" v-model="form.last_name" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="job_title">{{ t('contacts.jobTitle') }}</Label>
                                    <Input id="job_title" v-model="form.job_title" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="organization_id">{{ t('contacts.organization') }}</Label>
                                    <select
                                        id="organization_id"
                                        v-model="form.organization_id"
                                        class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                                    >
                                        <option value="">— {{ t('common.none') }} —</option>
                                        <option v-for="org in organizations" :key="org.id" :value="org.id">
                                            {{ org.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </template>

                        <!-- Organization fields -->
                        <template v-else>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="name">{{ t('common.name') }} *</Label>
                                    <Input id="name" v-model="form.name" :class="{ 'border-destructive': form.errors.name }" />
                                    <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="industry">{{ t('contacts.industry') }}</Label>
                                    <Input id="industry" v-model="form.industry" />
                                </div>
                            </div>
                        </template>

                        <!-- Shared fields -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="email">{{ t('common.email') }}</Label>
                                <Input id="email" v-model="form.email" type="email" />
                                <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="phone">{{ t('common.phone') }}</Label>
                                <Input id="phone" v-model="form.phone" />
                            </div>
                            <div class="space-y-2">
                                <Label for="mobile">{{ t('contacts.mobile') }}</Label>
                                <Input id="mobile" v-model="form.mobile" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="website">{{ t('company.website') }}</Label>
                            <Input id="website" v-model="form.website" type="url" />
                        </div>

                        <div class="space-y-2">
                            <Label for="notes">{{ t('common.notes') }}</Label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs resize-none"
                            />
                        </div>

                        <!-- Tags -->
                        <div class="space-y-2">
                            <Label>{{ t('contacts.tags') }}</Label>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <span
                                    v-for="(tag, i) in form.tags"
                                    :key="i"
                                    class="inline-flex items-center gap-1 rounded-full bg-muted px-3 py-1 text-xs"
                                >
                                    {{ tag }}
                                    <button type="button" class="text-muted-foreground hover:text-foreground" @click="removeTag(i)">&times;</button>
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <Input v-model="tagInput" placeholder="Add tag..." @keydown.enter.prevent="addTag" />
                                <Button type="button" variant="outline" size="sm" @click="addTag">+</Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex items-center gap-3 mt-6">
                    <Button type="submit" :disabled="form.processing">
                        {{ t('common.save') }}
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/contacts">{{ t('common.cancel') }}</Link>
                    </Button>
                </div>
            </form>
        </div>
    </TenantLayout>
</template>
