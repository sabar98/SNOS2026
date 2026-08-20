<script setup lang="ts">
import DonutChart from '@/components/charts/DonutChart.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { participantTypeLabels, toChartData } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { BadgeCheck, FileText, LineChart, Newspaper, ScrollText, Users, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

interface Stats {
    total_participants: number;
    total_revenue: number;
    total_articles: number;
    articles_accepted: number;
    total_publications: number;
    published_publications: number;
}

const props = defineProps<{
    stats: Stats;
    participantsByType: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Statistik', href: '/pimpinan/dashboard' }];
const participantChartData = computed(() => toChartData(props.participantsByType, participantTypeLabels));

const cards = [
    {
        key: 'total_participants' as const,
        title: 'Total Peserta',
        icon: Users,
        format: (v: number) => v.toString(),
        cardClass: 'bg-emerald-600 dark:bg-emerald-700',
    },
    {
        key: 'total_revenue' as const,
        title: 'Total Pemasukan',
        icon: Wallet,
        format: (v: number) => `Rp${Number(v).toLocaleString('id-ID')}`,
        cardClass: 'bg-sky-600 dark:bg-sky-700',
    },
    {
        key: 'total_articles' as const,
        title: 'Total Artikel',
        icon: FileText,
        format: (v: number) => v.toString(),
        cardClass: 'bg-amber-600 dark:bg-amber-700',
    },
    {
        key: 'articles_accepted' as const,
        title: 'Artikel Diterima',
        icon: BadgeCheck,
        format: (v: number) => v.toString(),
        cardClass: 'bg-violet-600 dark:bg-violet-700',
    },
    {
        key: 'total_publications' as const,
        title: 'Total Publikasi',
        icon: Newspaper,
        format: (v: number) => v.toString(),
        cardClass: 'bg-rose-600 dark:bg-rose-700',
    },
    {
        key: 'published_publications' as const,
        title: 'Publikasi Terbit',
        icon: ScrollText,
        format: (v: number) => v.toString(),
        cardClass: 'bg-teal-600 dark:bg-teal-700',
    },
];
</script>

<template>
    <Head title="Dashboard Statistik" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="LineChart"
                title="Statistik SNOS 2026"
                description="Ringkasan capaian peserta, pemasukan, artikel, dan publikasi secara real-time."
            />

            <div class="grid gap-4 md:grid-cols-3">
                <Card
                    v-for="card in cards"
                    :key="card.key"
                    :class="[
                        card.cardClass,
                        'border-0 text-white shadow-md transition-transform duration-200 hover:-translate-y-0.5 hover:shadow-lg',
                    ]"
                >
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-white/90">{{ card.title }}</CardTitle>
                        <span class="flex size-9 items-center justify-center rounded-full bg-white/20">
                            <component :is="card.icon" class="size-4 text-white" />
                        </span>
                    </CardHeader>
                    <CardContent class="text-3xl font-bold text-white">{{ card.format(stats[card.key]) }}</CardContent>
                </Card>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <DonutChart title="Peserta per Jenis" :data="participantChartData" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
