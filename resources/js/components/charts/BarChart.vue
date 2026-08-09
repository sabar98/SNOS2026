<script setup lang="ts">
import { computed, ref } from 'vue';

interface BarDatum {
    label: string;
    value: number;
}

const props = withDefaults(
    defineProps<{
        title: string;
        data: BarDatum[];
        valueFormatter?: (value: number) => string;
    }>(),
    {
        valueFormatter: (value: number) => value.toLocaleString('id-ID'),
    },
);

const showTable = ref(false);
const hoveredIndex = ref<number | null>(null);

const maxValue = computed(() => Math.max(1, ...props.data.map((d) => d.value)));

function barWidthPercent(value: number): number {
    return Math.max(2, (value / maxValue.value) * 100);
}
</script>

<template>
    <div class="viz-root">
        <div class="viz-header">
            <h3 class="viz-title">{{ title }}</h3>
            <button type="button" class="viz-toggle" @click="showTable = !showTable">
                {{ showTable ? 'Lihat sebagai grafik' : 'Lihat sebagai tabel' }}
            </button>
        </div>

        <p v-if="data.length === 0" class="viz-empty">Belum ada data.</p>

        <table v-else-if="showTable" class="viz-table">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in data" :key="row.label">
                    <td>{{ row.label }}</td>
                    <td class="viz-table-value">{{ valueFormatter(row.value) }}</td>
                </tr>
            </tbody>
        </table>

        <div v-else class="viz-rows">
            <div v-for="(row, index) in data" :key="row.label" class="viz-row" @mouseenter="hoveredIndex = index" @mouseleave="hoveredIndex = null">
                <span class="viz-label" :title="row.label">{{ row.label }}</span>
                <div class="viz-track">
                    <div
                        class="viz-bar"
                        :class="{ 'viz-bar--active': hoveredIndex === index }"
                        :style="{ width: barWidthPercent(row.value) + '%' }"
                    />
                    <div v-if="hoveredIndex === index" class="viz-tooltip">{{ row.label }}: {{ valueFormatter(row.value) }}</div>
                </div>
                <span class="viz-value">{{ valueFormatter(row.value) }}</span>
            </div>
        </div>
    </div>
</template>

<style>
.viz-root {
    color-scheme: light;
    --viz-surface: #fcfcfb;
    --viz-text-primary: #0b0b0b;
    --viz-text-secondary: #52514e;
    --viz-text-muted: #898781;
    --viz-gridline: #e1e0d9;
    --viz-baseline: #c3c2b7;
    --viz-series-1: #2a78d6;
    --viz-series-1-active: #1c5cab;
    width: 100%;
}
.dark .viz-root {
    color-scheme: dark;
    --viz-surface: #1a1a19;
    --viz-text-primary: #ffffff;
    --viz-text-secondary: #c3c2b7;
    --viz-text-muted: #898781;
    --viz-gridline: #2c2c2a;
    --viz-baseline: #383835;
    --viz-series-1: #3987e5;
    --viz-series-1-active: #5ea1ef;
}

.viz-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}
.viz-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--viz-text-primary);
    margin: 0;
}
.viz-toggle {
    font-size: 0.75rem;
    color: var(--viz-text-secondary);
    text-decoration: underline;
    text-underline-offset: 2px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}
.viz-toggle:hover {
    color: var(--viz-text-primary);
}
.viz-empty {
    font-size: 0.875rem;
    color: var(--viz-text-muted);
}

.viz-rows {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.viz-row {
    display: grid;
    grid-template-columns: minmax(0, 34%) 1fr auto;
    align-items: center;
    gap: 10px;
}
.viz-label {
    font-size: 0.8125rem;
    color: var(--viz-text-secondary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.viz-track {
    position: relative;
    height: 20px;
    border-bottom: 1px solid var(--viz-baseline);
}
.viz-bar {
    height: 20px;
    background: var(--viz-series-1);
    border-radius: 0 4px 4px 0;
    transition:
        width 0.2s ease,
        background-color 0.15s ease;
}
.viz-bar--active {
    background: var(--viz-series-1-active);
}
.viz-value {
    font-size: 0.8125rem;
    font-variant-numeric: tabular-nums;
    color: var(--viz-text-primary);
    text-align: right;
    min-width: 3ch;
}
.viz-tooltip {
    position: absolute;
    left: 0;
    top: -28px;
    background: var(--viz-text-primary);
    color: var(--viz-surface);
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 4px;
    white-space: nowrap;
    pointer-events: none;
    z-index: 10;
}

.viz-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8125rem;
}
.viz-table th {
    text-align: left;
    color: var(--viz-text-muted);
    font-weight: 500;
    border-bottom: 1px solid var(--viz-gridline);
    padding: 6px 4px;
}
.viz-table td {
    padding: 6px 4px;
    border-bottom: 1px solid var(--viz-gridline);
    color: var(--viz-text-primary);
}
.viz-table-value {
    font-variant-numeric: tabular-nums;
    text-align: right;
}
</style>
