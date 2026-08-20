<script setup lang="ts">
import { computed, ref } from 'vue';

interface DonutDatum {
    label: string;
    value: number;
}

const props = withDefaults(
    defineProps<{
        title: string;
        data: DonutDatum[];
        valueFormatter?: (value: number) => string;
    }>(),
    {
        valueFormatter: (value: number) => value.toLocaleString('id-ID'),
    },
);

const showTable = ref(false);
const activeIndex = ref<number | null>(null);

const SERIES_SLOTS = 8;

function seriesColor(index: number): string {
    return index < SERIES_SLOTS ? `var(--viz-series-${index + 1})` : 'var(--viz-series-overflow)';
}

const total = computed(() => props.data.reduce((sum, row) => sum + row.value, 0));

const RADIUS = 60;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;
const GAP = 4;

const segments = computed(() => {
    let cumulative = 0;
    return props.data.map((row, index) => {
        const fraction = total.value > 0 ? row.value / total.value : 0;
        const rawLength = fraction * CIRCUMFERENCE;
        const visibleLength = Math.max(0, rawLength - GAP);
        const offset = -(cumulative * CIRCUMFERENCE) - GAP / 2;
        cumulative += fraction;
        return {
            label: row.label,
            value: row.value,
            percent: fraction * 100,
            color: seriesColor(index),
            dasharray: `${visibleLength} ${CIRCUMFERENCE - visibleLength}`,
            dashoffset: offset,
        };
    });
});

function percentLabel(percent: number): string {
    return `${percent < 10 ? percent.toFixed(1) : Math.round(percent)}%`;
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

        <p v-if="data.length === 0 || total === 0" class="viz-empty">Belum ada data.</p>

        <table v-else-if="showTable" class="viz-table">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Jumlah</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, index) in data" :key="row.label">
                    <td class="viz-table-label">
                        <span class="viz-key" :style="{ backgroundColor: seriesColor(index) }" />
                        {{ row.label }}
                    </td>
                    <td class="viz-table-value">{{ valueFormatter(row.value) }}</td>
                    <td class="viz-table-value">{{ percentLabel(total > 0 ? (row.value / total) * 100 : 0) }}</td>
                </tr>
            </tbody>
        </table>

        <div v-else class="viz-donut-layout">
            <div class="viz-donut-wrap">
                <svg viewBox="0 0 140 140" class="viz-donut-svg">
                    <circle cx="70" cy="70" :r="RADIUS" class="viz-donut-track" />
                    <circle
                        v-for="(segment, index) in segments"
                        :key="`seg-${segment.label}`"
                        cx="70"
                        cy="70"
                        :r="RADIUS"
                        fill="none"
                        :stroke="segment.color"
                        stroke-width="20"
                        :stroke-dasharray="segment.dasharray"
                        :stroke-dashoffset="segment.dashoffset"
                        class="viz-donut-segment"
                        :class="{
                            'viz-donut-segment--active': activeIndex === index,
                            'viz-donut-segment--dim': activeIndex !== null && activeIndex !== index,
                        }"
                        tabindex="0"
                        role="graphics-symbol"
                        aria-roledescription="segmen donat"
                        :aria-label="`${segment.label}: ${valueFormatter(segment.value)} (${percentLabel(segment.percent)})`"
                        @mouseenter="activeIndex = index"
                        @mouseleave="activeIndex = null"
                        @focus="activeIndex = index"
                        @blur="activeIndex = null"
                    />
                </svg>
                <div class="viz-donut-center">
                    <span class="viz-donut-total">{{ valueFormatter(total) }}</span>
                    <span class="viz-donut-total-label">Total</span>
                </div>
            </div>

            <ul class="viz-legend">
                <li
                    v-for="(row, index) in data"
                    :key="`legend-${row.label}`"
                    class="viz-legend-item"
                    :class="{ 'viz-legend-item--dim': activeIndex !== null && activeIndex !== index }"
                    @mouseenter="activeIndex = index"
                    @mouseleave="activeIndex = null"
                >
                    <span class="viz-key" :style="{ backgroundColor: seriesColor(index) }" />
                    <span class="viz-legend-label">{{ row.label }}</span>
                    <span class="viz-legend-value">{{ valueFormatter(row.value) }}</span>
                    <span class="viz-legend-percent">{{ percentLabel(total > 0 ? (row.value / total) * 100 : 0) }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>

<style>
.viz-root {
    --viz-series-1: #2a78d6;
    --viz-series-2: #eb6834;
    --viz-series-3: #1baf7a;
    --viz-series-4: #eda100;
    --viz-series-5: #e87ba4;
    --viz-series-6: #008300;
    --viz-series-7: #4a3aa7;
    --viz-series-8: #e34948;
    --viz-series-overflow: hsl(var(--muted-foreground) / 0.5);
    width: 100%;
}
.dark .viz-root {
    --viz-series-1: #3987e5;
    --viz-series-2: #d95926;
    --viz-series-3: #199e70;
    --viz-series-4: #c98500;
    --viz-series-5: #d55181;
    --viz-series-6: #008300;
    --viz-series-7: #9085e9;
    --viz-series-8: #e66767;
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
    color: hsl(var(--foreground));
    margin: 0;
}
.viz-toggle {
    font-size: 0.75rem;
    color: hsl(var(--muted-foreground));
    text-decoration: underline;
    text-underline-offset: 2px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}
.viz-toggle:hover {
    color: hsl(var(--foreground));
}
.viz-empty {
    font-size: 0.875rem;
    color: hsl(var(--muted-foreground));
}

.viz-donut-layout {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1.5rem;
}
.viz-donut-wrap {
    position: relative;
    width: 140px;
    height: 140px;
    flex-shrink: 0;
}
.viz-donut-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}
.viz-donut-track {
    fill: none;
    stroke: hsl(var(--border));
    stroke-width: 20;
}
.viz-donut-segment {
    transition:
        stroke-width 0.15s ease,
        opacity 0.15s ease;
    outline: none;
}
.viz-donut-segment--active {
    stroke-width: 23;
}
.viz-donut-segment--dim {
    opacity: 0.35;
}
.viz-donut-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}
.viz-donut-total {
    font-size: 1.5rem;
    font-weight: 700;
    color: hsl(var(--foreground));
    line-height: 1.1;
}
.viz-donut-total-label {
    font-size: 0.6875rem;
    color: hsl(var(--muted-foreground));
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.viz-legend {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1 1 180px;
    min-width: 0;
}
.viz-legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8125rem;
    border-radius: 4px;
    padding: 2px 4px;
    margin: -2px -4px;
    transition: opacity 0.15s ease;
}
.viz-legend-item--dim {
    opacity: 0.4;
}
.viz-legend-label {
    color: hsl(var(--foreground));
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1 1 auto;
    min-width: 0;
}
.viz-legend-value {
    font-variant-numeric: tabular-nums;
    color: hsl(var(--muted-foreground));
}
.viz-legend-percent {
    font-variant-numeric: tabular-nums;
    color: hsl(var(--foreground));
    font-weight: 600;
    min-width: 3.5ch;
    text-align: right;
}

.viz-key {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 3px;
    flex-shrink: 0;
}

.viz-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8125rem;
}
.viz-table th {
    text-align: left;
    color: hsl(var(--muted-foreground));
    font-weight: 500;
    border-bottom: 1px solid hsl(var(--border));
    padding: 6px 4px;
}
.viz-table td {
    padding: 6px 4px;
    border-bottom: 1px solid hsl(var(--border));
    color: hsl(var(--foreground));
}
.viz-table-label {
    display: flex;
    align-items: center;
    gap: 8px;
}
.viz-table-value {
    font-variant-numeric: tabular-nums;
    text-align: right;
}
</style>
