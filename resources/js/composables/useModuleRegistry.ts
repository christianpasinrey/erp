import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ModuleNavItem, SharedErpData } from '@/types';

export function useModuleRegistry() {
    const page = usePage<{ erp: SharedErpData }>();

    const activeModules = computed<string[]>(
        () => page.props.erp?.activeModules ?? [],
    );

    const moduleNav = computed<ModuleNavItem[]>(
        () => page.props.erp?.moduleNav ?? [],
    );

    const permissions = computed<string[]>(
        () => page.props.erp?.permissions ?? [],
    );

    function isModuleActive(moduleId: string): boolean {
        return activeModules.value.includes(moduleId);
    }

    function can(permission: string): boolean {
        if (permissions.value.includes('*')) {
            return true;
        }

        return permissions.value.includes(permission);
    }

    function canAny(perms: string[]): boolean {
        return perms.some((p) => can(p));
    }

    return {
        activeModules,
        moduleNav,
        permissions,
        isModuleActive,
        can,
        canAny,
    };
}
