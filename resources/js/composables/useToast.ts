import { ref } from 'vue';

export type ToastVariant = 'success' | 'error' | 'info';

export interface ToastItem {
    id: number;
    title: string;
    description?: string;
    variant: ToastVariant;
}

interface ToastOptions {
    description?: string;
    timeout?: number;
}

export const toasts = ref<ToastItem[]>([]);

let counter = 0;
const timers = new Map<number, number>();

export function dismiss(id: number): void {
    toasts.value = toasts.value.filter((item) => item.id !== id);

    const timer = timers.get(id);

    if (timer !== undefined) {
        window.clearTimeout(timer);
        timers.delete(id);
    }
}

function push(variant: ToastVariant, title: string, options: ToastOptions = {}): number {
    const id = ++counter;
    const timeout = options.timeout ?? (variant === 'error' ? 6000 : 4000);

    toasts.value = [...toasts.value, { id, title, description: options.description, variant }];

    if (timeout > 0) {
        timers.set(
            id,
            window.setTimeout(() => dismiss(id), timeout),
        );
    }

    return id;
}

export function useToast() {
    return {
        toasts,
        dismiss,
        toast: {
            success: (title: string, options?: ToastOptions) => push('success', title, options),
            error: (title: string, options?: ToastOptions) => push('error', title, options),
            info: (title: string, options?: ToastOptions) => push('info', title, options),
        },
    };
}
