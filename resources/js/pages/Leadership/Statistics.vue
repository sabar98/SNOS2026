<script setup lang="ts">
import BarChart from '@/components/charts/BarChart.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { participantTypeLabels, toChartData } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { BadgeCheck, FileText, Newspaper, ScrollText, Users, Wallet } from 'lucide-vue-next';
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
        cardClass: 'border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40',
        titleClass: 'text-emerald-800/80 dark:text-emerald-300/80',
        iconClass: 'bg-emerald-500 text-white',
        valueClass: 'text-emerald-800 dark:text-emerald-300',
    },
    {
        key: 'total_revenue' as const,
        title: 'Total Pemasukan',
        icon: Wallet,
        format: (v: number) => `Rp${Number(v).toLocaleString('id-ID')}`,
        cardClass: 'border-sky-200 bg-sky-50 dark:border-sky-900 dark:bg-sky-950/40',
        titleClass: 'text-sky-800/80 dark:text-sky-300/80',
        iconClass: 'bg-sky-500 text-white',
        valueClass: 'text-sky-800 dark:text-sky-300',
    },
    {
        key: 'total_articles' as const,
        title: 'Total Artikel',
        icon: FileText,
        format: (v: number) => v.toString(),
        cardClass: 'border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/40',
        titleClass: 'text-amber-800/80 dark:text-amber-300/80',
        iconClass: 'bg-amber-500 text-white',
        valueClass: 'text-amber-800 dark:text-amber-300',
    },
    {
        key: 'articles_accepted' as const,
        title: 'Artikel Diterima',
        icon: BadgeCheck,
        format: (v: number) => v.toString(),
        cardClass: 'border-violet-200 bg-violet-50 dark:border-violet-900 dark:bg-violet-950/40',
        titleClass: 'text-violet-800/80 dark:text-violet-300/80',
        iconClass: 'bg-violet-500 text-white',
        valueClass: 'text-violet-800 dark:text-violet-300',
    },
    {
        key: 'total_publications' as const,
        title: 'Total Publikasi',
        icon: Newspaper,
        format: (v: number) => v.toString(),
        cardClass: 'border-rose-200 bg-rose-50 dark:border-rose-900 dark:bg-rose-950/40',
        titleClass: 'text-rose-800/80 dark:text-rose-300/80',
        iconClass: 'bg-rose-500 text-white',
        valueClass: 'text-rose-800 dark:text-rose-300',
    },
    {
        key: 'published_publications' as const,
        title: 'Publikasi Terbit',
        icon: ScrollText,
        format: (v: number) => v.toString(),
        cardClass: 'border-teal-200 bg-teal-50 dark:border-teal-900 dark:bg-teal-950/40',
        titleClass: 'text-teal-800/80 dark:text-teal-300/80',
        iconClass: 'bg-teal-500 text-white',
        valueClass: 'text-teal-800 dark:text-teal-300',
    },
];
</script>

<template>
    <Head title="Dashboard Statistik" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="grid gap-4 md:grid-cols-3">
                <Card v-for="card in cards" :key="card.key" :class="card.cardClass">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle :class="['text-sm font-medium', card.titleClass]">{{ card.title }}</CardTitle>
                        <span :class="['flex size-9 items-center justify-center rounded-full shadow-sm', card.iconClass]">
                            <component :is="card.icon" class="size-4" />
                        </span>
                    </CardHeader>
                    <CardContent :class="['text-2xl font-bold', card.valueClass]">{{ card.format(stats[card.key]) }}</CardContent>
                </Card>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <BarChart title="Peserta per Jenis" :data="participantChartData" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
