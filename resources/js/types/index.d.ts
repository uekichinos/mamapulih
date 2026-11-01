export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    role: 'owner' | 'kitchen';
}

export interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    preferred_locale: string;
    email_verified_at?: string | null;
    do_not_contact: boolean;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
        customer: Customer | null;
        impersonator: { id: number; name: string } | null;
    };
    locale: 'en' | 'ms';
};
