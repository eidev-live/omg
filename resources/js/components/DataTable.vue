<script setup lang="ts" generic="T extends Record<string, unknown>">
import EmptyState from '@/components/EmptyState.vue';
import LoadingState from '@/components/LoadingState.vue';
import { formatDate, formatDateTime, formatNumber, formatRupiah } from '@/lib/format';
import type { DataTableColumn } from '@/types';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        columns: DataTableColumn[];
        rows: T[];
        rowKey?: string;
        loading?: boolean;
        emptyTitle?: string;
        emptyDescription?: string;
    }>(),
    {
        rowKey: 'id',
        loading: false,
        emptyTitle: 'Belum ada data',
        emptyDescription: undefined,
    },
);

const primaryColumn = computed(() => props.columns.find((column) => column.primary) ?? props.columns[0]);

const detailColumns = computed(() => props.columns.filter((column) => column !== primaryColumn.value));

function getValue(row: T, column: DataTableColumn): unknown {
    return row[column.key];
}

function display(row: T, column: DataTableColumn): string {
    const value = getValue(row, column);

    if (value === null || value === undefined || value === '') {
        return '-';
    }

    switch (column.type) {
        case 'currency':
            return formatRupiah(value as number);
        case 'number':
            return formatNumber(value as number);
        case 'date':
            return formatDate(value as string);
        case 'datetime':
            return formatDateTime(value as string);
        default:
            return String(value);
    }
}

function alignClass(column: DataTableColumn): string {
    if (column.align === 'right') {
        return 'text-right';
    }

    if (column.align === 'center') {
        return 'text-center';
    }

    return 'text-left';
}
</script>

<template>
    <div>
        <LoadingState v-if="loading" />

        <EmptyState v-else-if="rows.length === 0" :title="emptyTitle" :description="emptyDescription">
            <template #action>
                <slot name="empty-action" />
            </template>
        </EmptyState>

        <template v-else>
            <div class="space-y-3 md:hidden">
                <article
                    v-for="row in rows"
                    :key="String(row[rowKey])"
                    class="rounded-xl border border-border bg-card p-4 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <slot :name="`cell-${primaryColumn.key}`" :row="row" :value="getValue(row, primaryColumn)">
                                <p class="truncate text-sm font-semibold text-foreground">
                                    {{ display(row, primaryColumn) }}
                                </p>
                            </slot>
                        </div>
                        <div v-if="$slots.actions" class="shrink-0">
                            <slot name="actions" :row="row" />
                        </div>
                    </div>

                    <dl class="mt-3 space-y-2">
                        <div
                            v-for="column in detailColumns"
                            v-show="!column.hideOnMobile"
                            :key="column.key"
                            class="flex items-center justify-between gap-3 text-sm"
                        >
                            <dt class="text-muted-foreground">{{ column.label }}</dt>
                            <dd class="min-w-0 text-right font-medium text-foreground">
                                <slot :name="`cell-${column.key}`" :row="row" :value="getValue(row, column)">
                                    {{ display(row, column) }}
                                </slot>
                            </dd>
                        </div>
                    </dl>
                </article>
            </div>

            <div class="hidden overflow-x-auto rounded-xl border border-border bg-card md:block">
                <table class="w-full caption-bottom text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/40">
                            <th
                                v-for="column in columns"
                                :key="column.key"
                                class="h-11 whitespace-nowrap px-4 align-middle text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                :class="alignClass(column)"
                            >
                                {{ column.label }}
                            </th>
                            <th
                                v-if="$slots.actions"
                                class="h-11 whitespace-nowrap px-4 text-right align-middle text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                            >
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in rows"
                            :key="String(row[rowKey])"
                            class="border-b border-border transition-colors last:border-0 hover:bg-muted/40"
                        >
                            <td
                                v-for="column in columns"
                                :key="column.key"
                                class="px-4 py-3 align-middle"
                                :class="[alignClass(column), column.class]"
                            >
                                <slot :name="`cell-${column.key}`" :row="row" :value="getValue(row, column)">
                                    {{ display(row, column) }}
                                </slot>
                            </td>
                            <td v-if="$slots.actions" class="px-4 py-3 text-right align-middle">
                                <div class="flex justify-end">
                                    <slot name="actions" :row="row" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</template>
