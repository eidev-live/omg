import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href?: string;
    icon?: LucideIcon;
    isActive?: boolean;
    items?: NavItem[];
}

export interface SharedData {
    [key: string]: unknown;
    name: string;
    auth: Auth;
    flash: {
        success?: string | null;
        error?: string | null;
    };
    ziggy: {
        location: string;
        url: string;
        port: null | number;
        defaults: Record<string, unknown>;
        routes: Record<string, string>;
    };
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface DataTableColumn {
    key: string;
    label: string;
    type?: 'text' | 'currency' | 'number' | 'date' | 'datetime';
    align?: 'left' | 'right' | 'center';
    primary?: boolean;
    hideOnMobile?: boolean;
    class?: string;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginator<T> {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    per_page: number;
}
