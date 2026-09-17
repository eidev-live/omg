<script setup lang="ts">
import { CategoryScale, Chart as ChartJS, Filler, Legend, LineElement, LinearScale, PointElement, Tooltip, type ChartOptions } from 'chart.js';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend, Filler);

const props = withDefaults(
    defineProps<{
        labels: string[];
        data: number[];
        color?: string;
        valuePrefix?: string;
    }>(),
    {
        color: '#F9844A',
        valuePrefix: 'Rp',
    },
);

const formatter = new Intl.NumberFormat('id-ID');

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            data: props.data,
            borderColor: props.color,
            backgroundColor: `${props.color}33`,
            fill: true,
            tension: 0.3,
            pointRadius: 2,
            pointHoverRadius: 4,
            borderWidth: 2,
        },
    ],
}));

const options = computed<ChartOptions<'line'>>(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (context) => `${props.valuePrefix}${formatter.format(Number(context.parsed.y))}`,
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 6 },
        },
        y: {
            beginAtZero: true,
            ticks: { callback: (value) => formatter.format(Number(value)) },
        },
    },
}));
</script>

<template>
    <div class="h-64 w-full">
        <Line :data="chartData" :options="options" />
    </div>
</template>
