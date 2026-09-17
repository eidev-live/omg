import { reactive } from 'vue';

export interface ConfirmOptions {
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    destructive?: boolean;
}

interface ConfirmState {
    open: boolean;
    options: ConfirmOptions;
}

const state = reactive<ConfirmState>({
    open: false,
    options: { title: '' },
});

let resolver: ((value: boolean) => void) | null = null;

export function confirmAction(options: ConfirmOptions): Promise<boolean> {
    state.options = options;
    state.open = true;

    return new Promise<boolean>((resolve) => {
        resolver = resolve;
    });
}

export function respondConfirm(value: boolean): void {
    state.open = false;
    resolver?.(value);
    resolver = null;
}

export function useConfirmState(): ConfirmState {
    return state;
}

export function useConfirm() {
    return { confirm: confirmAction };
}
