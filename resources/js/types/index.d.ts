export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export interface Company {
    id: number;
    name: string;
    registration_number?: string;
    branches: Branch[];
}

export interface Branch {
    id: number;
    company_id: number;
    name: string;
    address?: string;
}

export interface Employee {
    id: number;
    company_id: number;
    branch_id: number;
    name: string;
    email?: string;
}

export interface CommissionNote {
    id: number;
    company_id: number;
    branch_id: number;
    employee_id: number;
    created_by: number;
    amount: string;
    notes?: string;
    payment_date: string;
    employee?: Employee;
    author?: User;
    branch?: Branch;
    company?: Company;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};

