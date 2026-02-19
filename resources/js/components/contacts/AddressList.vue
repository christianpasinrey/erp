<script setup lang="ts">
import { MapPin, Pencil, Trash2 } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import type { Address } from '@/types';

const { t } = useI18n();

defineProps<{
    addresses: Address[];
}>();

const emit = defineEmits<{
    edit: [address: Address];
    delete: [address: Address];
}>();
</script>

<template>
    <div v-if="addresses.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <Card v-for="address in addresses" :key="address.id">
            <CardContent class="pt-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-2 flex-1">
                        <MapPin class="size-4 mt-0.5 text-muted-foreground shrink-0" />
                        <div class="min-w-0">
                            <p v-if="address.label" class="font-medium text-sm">{{ address.label }}</p>
                            <p class="text-sm">{{ address.address_line_1 }}</p>
                            <p v-if="address.address_line_2" class="text-sm">{{ address.address_line_2 }}</p>
                            <p class="text-sm text-muted-foreground">
                                {{ [address.city, address.state, address.postal_code].filter(Boolean).join(', ') }}
                            </p>
                            <p class="text-sm text-muted-foreground">{{ address.country_code }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2 ml-2">
                        <div class="flex gap-1">
                            <Badge v-if="address.is_primary" variant="default">{{ t('common.primary') }}</Badge>
                            <Badge variant="outline">{{ address.type }}</Badge>
                        </div>
                        <div class="flex gap-1">
                            <Button variant="ghost" size="sm" @click="emit('edit', address)">
                                <Pencil class="size-3" />
                            </Button>
                            <Button variant="ghost" size="sm" @click="emit('delete', address)">
                                <Trash2 class="size-3 text-destructive" />
                            </Button>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
    <div v-else class="text-center py-8 text-muted-foreground">
        {{ t('common.noResults') }}
    </div>
</template>
