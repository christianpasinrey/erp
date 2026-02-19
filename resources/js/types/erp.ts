export type Company = {
    id: number;
    name: string;
    legal_name: string | null;
    currency_code: string;
    country_code: string;
    logo_path: string | null;
    is_active: boolean;
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
