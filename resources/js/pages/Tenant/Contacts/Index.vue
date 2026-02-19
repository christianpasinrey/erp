<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Eye, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDebounceFn } from '@vueuse/core';
import TenantLayout from '@/layouts/tenant/TenantLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import type { Contact, ContactFilters } from '@/types';

const { t } = useI18n();

const props = defineProps<{
    contacts: {
        data: Contact[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta?: { current_page: number; last_page: number; total: number };
    };
    filters: ContactFilters;
}>();

const search = ref(props.filters.search ?? '');
const typeFilter = ref(props.filters.type ?? '');

const applyFilters = useDebounceFn(() => {
    router.get('/contacts', {
        search: search.value || undefined,
        type: typeFilter.value || undefined,
    }, { preserveState: true, replace: true });
}, 300);

watch([search], applyFilters);

function changeType(type: string) {
    typeFilter.value = type;
    router.get('/contacts', {
        search: search.value || undefined,
        type: type || undefined,
    }, { preserveState: true, replace: true });
}

function deleteContact(contact: Contact) {
    if (confirm(t('contacts.deleteConfirm'))) {
        router.delete(`/contacts/${contact.id}`);
    }
}
</script>

<template>
    <TenantLayout :breadcrumbs="[{ title: t('contacts.contacts') }]">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">{{ t('contacts.contacts') }}</h1>
                <Button as-child>
                    <Link href="/contacts/create">
                        <Plus class="size-4 mr-2" />
                        {{ t('contacts.newContact') }}
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-3 mb-4">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        :placeholder="t('common.searchPlaceholder')"
                        class="pl-9"
                    />
                </div>
                <div class="flex gap-1">
                    <Button
                        size="sm"
                        :variant="typeFilter === '' ? 'default' : 'outline'"
                        @click="changeType('')"
                    >
                        {{ t('contacts.allTypes') }}
                    </Button>
                    <Button
                        size="sm"
                        :variant="typeFilter === 'person' ? 'default' : 'outline'"
                        @click="changeType('person')"
                    >
                        {{ t('contacts.persons') }}
                    </Button>
                    <Button
                        size="sm"
                        :variant="typeFilter === 'organization' ? 'default' : 'outline'"
                        @click="changeType('organization')"
                    >
                        {{ t('contacts.organizations') }}
                    </Button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg border">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium">{{ t('common.name') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ t('common.type') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ t('common.email') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ t('common.phone') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ t('contacts.organization') }}</th>
                            <th class="px-4 py-3 text-left font-medium">{{ t('common.status') }}</th>
                            <th class="px-4 py-3 text-right font-medium">{{ t('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="contact in contacts.data" :key="contact.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium">{{ contact.display_name }}</td>
                            <td class="px-4 py-3">
                                <Badge variant="outline">
                                    {{ contact.type === 'person' ? t('contacts.person') : t('contacts.organization') }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ contact.email ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ contact.phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ contact.organization?.name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="contact.is_active ? 'default' : 'secondary'">
                                    {{ contact.is_active ? t('common.active') : t('common.inactive') }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="`/contacts/${contact.id}`">
                                            <Eye class="size-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="`/contacts/${contact.id}/edit`">
                                            <Pencil class="size-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="sm" @click="deleteContact(contact)">
                                        <Trash2 class="size-4 text-destructive" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="contacts.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                {{ t('common.noResults') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="contacts.links.length > 3" class="flex items-center justify-center gap-1 mt-4">
                <template v-for="link in contacts.links" :key="link.label">
                    <Button
                        v-if="link.url"
                        size="sm"
                        :variant="link.active ? 'default' : 'outline'"
                        as-child
                    >
                        <Link :href="link.url" v-html="link.label" />
                    </Button>
                    <Button
                        v-else
                        size="sm"
                        variant="outline"
                        disabled
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </TenantLayout>
</template>
