export type ContactType = 'person' | 'organization';

export type ContactSource = 'manual' | 'import' | 'api';

export type Contact = {
    id: number;
    type: ContactType;
    first_name: string | null;
    last_name: string | null;
    job_title: string | null;
    organization_id: number | null;
    name: string | null;
    industry: string | null;
    display_name: string;
    email: string | null;
    phone: string | null;
    mobile: string | null;
    website: string | null;
    notes: string | null;
    tags: string[] | null;
    custom_fields: Record<string, unknown> | null;
    avatar_path: string | null;
    logo_path: string | null;
    is_active: boolean;
    source: ContactSource;
    organization?: { id: number; name: string } | null;
    members?: Array<{
        id: number;
        first_name: string | null;
        last_name: string | null;
        display_name: string;
        email: string | null;
        job_title: string | null;
    }>;
    addresses?: Address[];
    created_at: string;
    updated_at: string;
};

export type ContactFilters = {
    search?: string;
    type?: ContactType | '';
};

export type Address = {
    id: number;
    label: string | null;
    address_line_1: string;
    address_line_2: string | null;
    city: string | null;
    state: string | null;
    postal_code: string | null;
    country_code: string;
    latitude: number | null;
    longitude: number | null;
    is_primary: boolean;
    type: AddressType;
    created_at: string;
    updated_at: string;
};

export type AddressType = 'main' | 'billing' | 'shipping' | 'other';

export type OrganizationOption = {
    id: number;
    name: string;
};
