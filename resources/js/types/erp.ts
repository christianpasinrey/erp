export type Company = {
    id: number;
    name: string;
    legal_name: string | null;
    currency_code: string;
    country_code: string;
    logo_path: string | null;
    is_active: boolean;
};

export type CompanyFull = Company & {
    tax_id: string | null;
    locale: string;
    timezone: string;
    fiscal_year_start: number;
    address: Record<string, string> | null;
    phone: string | null;
    email: string | null;
    website: string | null;
    settings: Record<string, unknown> | null;
    parent_id: number | null;
    users_count?: number;
    users?: Array<{ id: number; name: string; email: string }>;
    created_at: string;
    updated_at: string;
};

export type ModuleInfo = {
    id: string;
    name: string;
    is_active: boolean;
};

export type ModuleNavItem = {
    label: string;
    route: string;
    icon: string;
    order: number;
    module: string;
};

export type ModuleWidget = {
    component: string;
    order: number;
    span: number;
    module: string;
};

export type SharedErpData = {
    currentCompany: Company | null;
    companies: Company[];
    activeModules: string[];
    moduleNav: ModuleNavItem[];
    permissions: string[];
};
