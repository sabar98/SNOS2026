<script setup lang="ts">
import BarChart from '@/components/charts/BarChart.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { articleStatusLabels, participantTypeLabels, toChartData } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { FileText, UserCheck, Users, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    participantsByType: Record<string, number>;
    revenue: number;
    articlesByStatus: Record<string, number>;
    attendanceCount: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Laporan', href: '/admin/reports' }];

const participantChartData = computed(() => toChartData(props.participantsByType, participantTypeLabels));
const articleChartData = computed(() => toChartData(props.articlesByStatus, articleStatusLabels));
</script>

<template>
    <Head title="Laporan Kegiatan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
            <div class="grid gap-4 md:grid-cols-2">
                <Card class="border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-emerald-800/80 dark:text-emerald-300/80">Pendapatan Terverifikasi</CardTitle>
                        <span class="flex size-9 items-center justify-center rounded-full bg-emerald-500 text-white shadow-sm">
                            <Wallet class="size-4" />
                        </span>
                    </CardHeader>
                    <CardContent class="text-2xl font-bold text-emerald-800 dark:text-emerald-300">
                        Rp{{ Number(revenue).toLocaleString('id-ID') }}
                    </CardContent>
                </Card>
                <Card class="border-sky-200 bg-sky-50 dark:border-sky-900 dark:bg-sky-950/40">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-sky-800/80 dark:text-sky-300/80">Total Kehadiran</CardTitle>
                        <span class="flex size-9 items-center justify-center rounded-full bg-sky-500 text-white shadow-sm">
                            <UserCheck class="size-4" />
                        </span>
                    </CardHeader>
                    <CardContent class="text-2xl font-bold text-sky-800 dark:text-sky-300">{{ attendanceCount }}</CardContent>
                </Card>
            </div>

            <Card class="border-violet-200 bg-violet-50 dark:border-violet-900 dark:bg-violet-950/40">
                <CardHeader class="flex flex-row items-center gap-2 space-y-0 pb-2">
                    <span class="flex size-8 items-center justify-center rounded-full bg-violet-500 text-white shadow-sm">
                        <Users class="size-4" />
                    </span>
                    <CardTitle class="text-sm font-medium text-violet-800/80 dark:text-violet-300/80">Ringkasan Peserta</CardTitle>
                </CardHeader>
                <CardContent>
                    <BarChart title="Peserta per Jenis" :data="participantChartData" />
                </CardContent>
            </Card>

            <Card class="border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/40">
                <CardHeader class="flex flex-row items-center gap-2 space-y-0 pb-2">
                    <span class="flex size-8 items-center justify-center rounded-full bg-amber-500 text-white shadow-sm">
                        <FileText class="size-4" />
                    </span>
                    <CardTitle class="text-sm font-medium text-amber-800/80 dark:text-amber-300/80">Ringkasan Artikel</CardTitle>
                </CardHeader>
                <CardContent>
                    <BarChart title="Artikel per Status" :data="articleChartData" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
