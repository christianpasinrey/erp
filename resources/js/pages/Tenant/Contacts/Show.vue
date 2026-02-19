<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2, Mail, Phone, Globe, Plus } from 'lucide-vue-next';
import { reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import TenantLayout from '@/layouts/tenant/TenantLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import AddressList from '@/components/contacts/AddressList.vue';
import AddressMap from '@/components/contacts/AddressMap.vue';
import AddressForm from '@/components/contacts/AddressForm.vue';
import type { Contact, Address } from '@/types';

const { t } = useI18n();

const props = defineProps<{
    contact: Contact;
}>();

const activeTab = ref<'details' | 'addresses' | 'members'>('details');
const showAddressDialog = ref(false);
const editingAddress = ref<Address | null>(null);

const emptyAddress = () => ({
    label: '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    postal_code: '',
    country_code: 'ES',
    latitude: '' as string | number,
    longitude: '' as string | number,
    is_primary: false,
    type: 'main',
});

const addressFormData = reactive(emptyAddress());

function openNewAddress() {
    editingAddress.value = null;
    Object.assign(addressFormData, emptyAddress());
    showAddressDialog.value = true;
}

function openEditAddress(address: Address) {
    editingAddress.value = address;
    Object.assign(addressFormData, {
        label: address.label ?? '',
        address_line_1: address.address_line_1,
        address_line_2: address.address_line_2 ?? '',
        city: address.city ?? '',
        state: address.state ?? '',
        postal_code: address.postal_code ?? '',
        country_code: address.country_code,
        latitude: address.latitude ?? '',
        longitude: address.longitude ?? '',
        is_primary: address.is_primary,
        type: address.type,
    });
    showAddressDialog.value = true;
}

function saveAddress() {
    const data = { ...addressFormData };
    if (data.latitude === '') data.latitude = null as never;
    if (data.longitude === '') data.longitude = null as never;

    if (editingAddress.value) {
        router.put(`/contacts/${props.contact.id}/addresses/${editingAddress.value.id}`, data as never, {
            onSuccess: () => { showAddressDialog.value = false; },
        });
    } else {
        router.post(`/contacts/${props.contact.id}/addresses`, data as never, {
            onSuccess: () => { showAddressDialog.value = false; },
        });
    }
}

function deleteAddress(address: Address) {
    if (confirm(t('addresses.deleteConfirm'))) {
        router.delete(`/contacts/${props.contact.id}/addresses/${address.id}`);
    }
}

function deleteContact() {
    if (confirm(t('contacts.deleteConfirm'))) {
        router.delete(`/contacts/${props.contact.id}`);
    }
}
</script>

<template>
    <TenantLayout :breadcrumbs="[{ title: t('contacts.contacts'), href: '/contacts' }, { title: contact.display_name }]">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold">{{ contact.display_name }}</h1>
                        <Badge variant="outline">
                            {{ contact.type === 'person' ? t('contacts.person') : t('contacts.organization') }}
                        </Badge>
                        <Badge :variant="contact.is_active ? 'default' : 'secondary'">
                            {{ contact.is_active ? t('common.active') : t('common.inactive') }}
                        </Badge>
                    </div>
                    <p v-if="contact.job_title" class="text-muted-foreground mt-1">
                        {{ contact.job_title }}
                        <span v-if="contact.organization"> &mdash; {{ contact.organization.name }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/contacts/${contact.id}/edit`">
                            <Pencil class="size-4 mr-2" />
                            {{ t('common.edit') }}
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="deleteContact">
                        <Trash2 class="size-4 mr-2" />
                        {{ t('common.delete') }}
                    </Button>
                </div>
            </div>

            <!-- Tab navigation -->
            <div class="flex gap-1 border-b mb-6">
                <button
                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
                    :class="activeTab === 'details' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    @click="activeTab = 'details'"
                >
                    {{ t('contacts.details') }}
                </button>
                <button
                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
                    :class="activeTab === 'addresses' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    @click="activeTab = 'addresses'"
                >
                    {{ t('addresses.addresses') }} ({{ contact.addresses?.length ?? 0 }})
                </button>
                <button
                    v-if="contact.type === 'organization'"
                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
                    :class="activeTab === 'members' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    @click="activeTab = 'members'"
                >
                    {{ t('contacts.members') }} ({{ contact.members?.length ?? 0 }})
                </button>
            </div>

            <!-- Details tab -->
            <div v-if="activeTab === 'details'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('contacts.details') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div v-if="contact.email" class="flex items-center gap-2">
                            <Mail class="size-4 text-muted-foreground" />
                            <span>{{ contact.email }}</span>
                        </div>
                        <div v-if="contact.phone" class="flex items-center gap-2">
                            <Phone class="size-4 text-muted-foreground" />
                            <span>{{ contact.phone }}</span>
                        </div>
                        <div v-if="contact.mobile" class="flex items-center gap-2">
                            <Phone class="size-4 text-muted-foreground" />
                            <span>{{ contact.mobile }} ({{ t('contacts.mobile') }})</span>
                        </div>
                        <div v-if="contact.website" class="flex items-center gap-2">
                            <Globe class="size-4 text-muted-foreground" />
                            <span>{{ contact.website }}</span>
                        </div>
                        <div v-if="contact.industry" class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('contacts.industry') }}</span>
                            <span>{{ contact.industry }}</span>
                        </div>
                        <div v-if="contact.source" class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('contacts.source') }}</span>
                            <span>{{ contact.source }}</span>
                        </div>
                    </CardContent>
                </Card>

                <div class="space-y-6">
                    <Card v-if="contact.notes">
                        <CardHeader>
                            <CardTitle>{{ t('common.notes') }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm whitespace-pre-wrap">{{ contact.notes }}</p>
                        </CardContent>
                    </Card>

                    <Card v-if="contact.tags?.length">
                        <CardHeader>
                            <CardTitle>{{ t('contacts.tags') }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="tag in contact.tags" :key="tag" variant="outline">
                                    {{ tag }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Addresses tab -->
            <div v-if="activeTab === 'addresses'" class="space-y-4">
                <div class="flex justify-end">
                    <Button @click="openNewAddress">
                        <Plus class="size-4 mr-2" />
                        {{ t('addresses.newAddress') }}
                    </Button>
                </div>

                <AddressMap v-if="contact.addresses?.length" :addresses="contact.addresses" />

                <AddressList
                    :addresses="contact.addresses ?? []"
                    @edit="openEditAddress"
                    @delete="deleteAddress"
                />
            </div>

            <!-- Members tab -->
            <div v-if="activeTab === 'members' && contact.type === 'organization'">
                <div v-if="contact.members?.length" class="overflow-x-auto rounded-lg border">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium">{{ t('common.name') }}</th>
                                <th class="px-4 py-3 text-left font-medium">{{ t('contacts.jobTitle') }}</th>
                                <th class="px-4 py-3 text-left font-medium">{{ t('common.email') }}</th>
                                <th class="px-4 py-3 text-right font-medium">{{ t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="member in contact.members" :key="member.id" class="border-b last:border-0 hover:bg-muted/30">
                                <td class="px-4 py-3 font-medium">{{ member.display_name }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ member.job_title ?? '—' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ member.email ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="`/contacts/${member.id}`">View</Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-8 text-muted-foreground">
                    {{ t('common.noResults') }}
                </div>
            </div>
        </div>

        <!-- Address Dialog -->
        <Dialog v-model:open="showAddressDialog">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{{ editingAddress ? t('addresses.editAddress') : t('addresses.newAddress') }}</DialogTitle>
                </DialogHeader>
                <AddressForm v-model="addressFormData" />
                <DialogFooter>
                    <Button variant="outline" @click="showAddressDialog = false">{{ t('common.cancel') }}</Button>
                    <Button @click="saveAddress">{{ t('common.save') }}</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </TenantLayout>
</template>
