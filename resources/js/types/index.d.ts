import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    locale: string;
    translations: Record<string, string>;
    categories: { name: string; slug: string }[];
    ziggy: Config & { location: string };
};

export interface Product {
    id: string;
    name: string;
    category: string;
    image: string;
    images?: string[];
    description: string;
    features: string[];
}

export interface Solution {
    id: string;
    title: string;
    summary: string;
    icon: string;
    image: string;
}
