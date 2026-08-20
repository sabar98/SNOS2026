<script setup lang="ts">
import DonutChart from '@/components/charts/DonutChart.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { articleStatusLabels, participantTypeLabels, toChartData } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { FileText, ScrollText, UserCheck, Users, Wallet } from 'lucide-vue-next';
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
            <PageHeader :icon="ScrollText" title="Laporan Kegiatan" description="Ringkasan pendapatan, kehadiran, peserta, dan artikel SNOS 2026." />

            <div class="grid gap-4 md:grid-cols-2">
                <Card class="border-0 bg-emerald-600 text-white shadow-md dark:bg-emerald-700">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-white/90">Pendapatan Terverifikasi</CardTitle>
                        <span class="flex size-9 items-center justify-center rounded-full bg-white/20">
                            <Wallet class="size-4 text-white" />
                        </span>
                    </CardHeader>
                    <CardContent class="text-3xl font-bold text-white"> Rp{{ Number(revenue).toLocaleString('id-ID') }} </CardContent>
                </Card>
                <Card class="border-0 bg-sky-600 text-white shadow-md dark:bg-sky-700">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-white/90">Total Kehadiran</CardTitle>
                        <span class="flex size-9 items-center justify-center rounded-full bg-white/20">
                            <UserCheck class="size-4 text-white" />
                        </span>
                    </CardHeader>
                    <CardContent class="text-3xl font-bold text-white">{{ attendanceCount }}</CardContent>
                </Card>
            </div>

            <Card class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                <CardHeader class="flex flex-row items-center gap-2 space-y-0 pb-2">
                    <span class="flex size-8 items-center justify-center rounded-full bg-violet-500 text-white shadow-sm">
                        <Users class="size-4" />
                    </span>
                    <CardTitle class="text-sm font-medium text-muted-foreground">Ringkasan Peserta</CardTitle>
                </CardHeader>
                <CardContent>
                    <DonutChart title="Peserta per Jenis" :data="participantChartData" />
                </CardContent>
            </Card>

            <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                <CardHeader class="flex flex-row items-center gap-2 space-y-0 pb-2">
                    <span class="flex size-8 items-center justify-center rounded-full bg-amber-500 text-white shadow-sm">
                        <FileText class="size-4" />
                    </span>
                    <CardTitle class="text-sm font-medium text-muted-foreground">Ringkasan Artikel</CardTitle>
                </CardHeader>
                <CardContent>
                    <DonutChart title="Artikel per Status" :data="articleChartData" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
