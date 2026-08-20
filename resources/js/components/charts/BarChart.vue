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

const dataMax = computed(() => Math.max(0, ...props.data.map((d) => d.value)));
const allIntegers = computed(() => props.data.every((d) => Number.isInteger(d.value)));

function niceNumber(value: number, round: boolean): number {
    if (value <= 0) return 1;
    const exponent = Math.floor(Math.log10(value));
    const fraction = value / 10 ** exponent;
    let niceFraction: number;
    if (round) {
        if (fraction < 1.5) niceFraction = 1;
        else if (fraction < 3) niceFraction = 2;
        else if (fraction < 7) niceFraction = 5;
        else niceFraction = 10;
    } else {
        if (fraction <= 1) niceFraction = 1;
        else if (fraction <= 2) niceFraction = 2;
        else if (fraction <= 5) niceFraction = 5;
        else niceFraction = 10;
    }
    return niceFraction * 10 ** exponent;
}

const scale = computed(() => {
    if (dataMax.value <= 0) {
        return { max: allIntegers.value ? 4 : 1, step: allIntegers.value ? 1 : 0.25 };
    }
    let step = niceNumber(dataMax.value / 4, true);
    if (allIntegers.value) {
        step = Math.max(1, Math.round(step));
    }
    const max = Math.ceil(dataMax.value / step) * step;
    return { max, step };
});

const ticks = computed(() => {
    const values: number[] = [];
    for (let v = 0; v <= scale.value.max + 1e-9; v += scale.value.step) {
        values.push(Math.round(v * 1000) / 1000);
    }
    return values;
});

function barWidthPercent(value: number): number {
    return scale.value.max > 0 ? Math.max(value > 0 ? 1 : 0, (value / scale.value.max) * 100) : 0;
}

function tickPercent(tick: number): number {
    return scale.value.max > 0 ? (tick / scale.value.max) * 100 : 0;
}

const SERIES_SLOTS = 8;

function seriesColor(index: number): string {
    return index < SERIES_SLOTS ? `var(--viz-series-${index + 1})` : 'var(--viz-series-overflow)';
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
                <tr v-for="(row, index) in data" :key="row.label">
                    <td class="viz-table-label">
                        <span class="viz-tooltip-key" :style="{ backgroundColor: seriesColor(index) }" />
                        {{ row.label }}
                    </td>
                    <td class="viz-table-value">{{ valueFormatter(row.value) }}</td>
                </tr>
            </tbody>
        </table>

        <div v-else class="viz-chart">
            <div class="viz-col viz-col-labels">
                <span v-for="row in data" :key="`label-${row.label}`" class="viz-label" :title="row.label">{{ row.label }}</span>
            </div>

            <div class="viz-plot">
                <div class="viz-gridlines" aria-hidden="true">
                    <div v-for="tick in ticks" :key="`grid-${tick}`" class="viz-gridline" :style="{ left: tickPercent(tick) + '%' }" />
                </div>

                <div class="viz-col viz-col-tracks">
                    <div
                        v-for="(row, index) in data"
                        :key="`track-${row.label}`"
                        class="viz-track"
                        tabindex="0"
                        role="graphics-symbol"
                        aria-roledescription="bar"
                        :aria-label="`${row.label}: ${valueFormatter(row.value)}`"
                        @mouseenter="hoveredIndex = index"
                        @mouseleave="hoveredIndex = null"
                        @focus="hoveredIndex = index"
                        @blur="hoveredIndex = null"
                    >
                        <div
                            class="viz-bar"
                            :class="{ 'viz-bar--active': hoveredIndex === index }"
                            :style="{ width: barWidthPercent(row.value) + '%', backgroundColor: seriesColor(index) }"
                        />
                        <div v-if="hoveredIndex === index" class="viz-tooltip">
                            <span class="viz-tooltip-key" :style="{ backgroundColor: seriesColor(index) }" />
                            <strong>{{ valueFormatter(row.value) }}</strong> &middot; {{ row.label }}
                        </div>
                    </div>
                </div>

                <div class="viz-axis" aria-hidden="true">
                    <span v-for="tick in ticks" :key="`tick-${tick}`" class="viz-axis-label" :style="{ left: tickPercent(tick) + '%' }">
                        {{ valueFormatter(tick) }}
                    </span>
                </div>
            </div>

            <div class="viz-col viz-col-values">
                <span v-for="row in data" :key="`value-${row.label}`" class="viz-value">{{ valueFormatter(row.value) }}</span>
            </div>
        </div>
    </div>
</template>

<style>
.viz-root {
    --viz-row-height: 22px;
    --viz-row-gap: 10px;
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

.viz-chart {
    display: flex;
    align-items: stretch;
    gap: 10px;
    padding-bottom: 20px;
}
.viz-col {
    display: flex;
    flex-direction: column;
    gap: var(--viz-row-gap);
}
.viz-col-labels {
    flex: 0 1 30%;
    min-width: 0;
}
.viz-col-values {
    flex: 0 0 auto;
}
.viz-col-tracks {
    width: 100%;
}
.viz-plot {
    position: relative;
    flex: 1 1 auto;
    min-width: 0;
}
.viz-label {
    height: var(--viz-row-height);
    line-height: var(--viz-row-height);
    font-size: 0.8125rem;
    color: hsl(var(--muted-foreground));
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.viz-value {
    height: var(--viz-row-height);
    line-height: var(--viz-row-height);
    font-size: 0.8125rem;
    font-variant-numeric: tabular-nums;
    color: hsl(var(--foreground));
    text-align: right;
    min-width: 3ch;
}

.viz-gridlines {
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.viz-gridline {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 1px;
    background: hsl(var(--border));
}

.viz-track {
    position: relative;
    height: var(--viz-row-height);
    border-radius: 3px;
    outline: none;
}
.viz-track:focus-visible {
    box-shadow: 0 0 0 2px hsl(var(--ring) / 0.5);
}
.viz-bar {
    height: 100%;
    border-radius: 0 4px 4px 0;
    transition:
        width 0.2s ease,
        filter 0.15s ease;
}
.viz-bar--active {
    filter: brightness(0.9);
}
.viz-tooltip {
    position: absolute;
    left: 0;
    top: -30px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: hsl(var(--foreground));
    color: hsl(var(--background));
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 6px;
    white-space: nowrap;
    pointer-events: none;
    z-index: 10;
}
.viz-tooltip-key {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 2px;
    flex-shrink: 0;
}
.viz-tooltip strong {
    font-variant-numeric: tabular-nums;
}

.viz-axis {
    position: absolute;
    left: 0;
    right: 0;
    bottom: -18px;
    height: 14px;
}
.viz-axis-label {
    position: absolute;
    top: 0;
    transform: translateX(-50%);
    font-size: 0.6875rem;
    font-variant-numeric: tabular-nums;
    color: hsl(var(--muted-foreground));
    white-space: nowrap;
}
.viz-axis-label:first-child {
    transform: translateX(0);
}
.viz-axis-label:last-child {
    transform: translateX(-100%);
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
