import { usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { Company, SharedErpData } from '@/types';

export function useCompany() {
    const page = usePage<{ erp: SharedErpData }>();

    const currentCompany = computed<Company | null>(
        () => page.props.erp?.currentCompany ?? null,
    );

    const companies = computed<Company[]>(
        () => page.props.erp?.companies ?? [],
    );

    const hasMultipleCompanies = computed(
        () => companies.value.length > 1,
    );

    function switchCompany(companyId: number) {
        router.post('/company/switch', { company_id: companyId }, {
            preserveState: false,
        });
    }

    return {
        currentCompany,
        companies,
        hasMultipleCompanies,
        switchCompany,
    };
}
