<script setup lang="ts">
import DonutChart from '@/components/charts/DonutChart.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    articleStatusLabels,
    participantTypeLabels,
    paymentStatusLabels,
    registrationStatusLabels,
    registrationStatusVariants,
    toChartData,
} from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, ClipboardList, Eye, LayoutGrid, Users, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

interface Stats {
    total_participants: number;
    pending_payment_verifications: number;
    articles_awaiting_administration: number;
    articles_in_review: number;
}

interface Registration {
    id: number;
    registration_number: string;
    status: string;
    user: { name: string };
}

const props = defineProps<{
    stats: Stats;
    recentRegistrations: Registration[];
    participantsByType: Record<string, number>;
    articlesByStatus: Record<string, number>;
    articlesByJournal: Record<string, number>;
    paymentsByStatus: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard Admin', href: '/admin/dashboard' }];

const participantChartData = computed(() => toChartData(props.participantsByType, participantTypeLabels));
const articleChartData = computed(() => toChartData(props.articlesByStatus, articleStatusLabels));
const journalChartData = computed(() => Object.entries(props.articlesByJournal).map(([label, value]) => ({ label, value })));
const paymentChartData = computed(() => toChartData(props.paymentsByStatus, paymentStatusLabels));

const cards = [
    {
        key: 'total_participants' as const,
        title: 'Total Peserta',
        icon: Users,
        cardClass: 'bg-emerald-600 dark:bg-emerald-700',
    },
    {
        key: 'pending_payment_verifications' as const,
        title: 'Menunggu Verifikasi Bayar',
        icon: Wallet,
        cardClass: 'bg-amber-600 dark:bg-amber-700',
    },
    {
        key: 'articles_awaiting_administration' as const,
        title: 'Artikel Perlu Diperiksa',
        icon: AlertTriangle,
        cardClass: 'bg-red-600 dark:bg-red-700',
    },
    {
        key: 'articles_in_review' as const,
        title: 'Artikel Sedang Direview',
        icon: Eye,
        cardClass: 'bg-sky-600 dark:bg-sky-700',
    },
];
</script>

<template>
    <Head title="Dashboard Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader :icon="LayoutGrid" title="Dashboard Admin" description="Ringkasan aktivitas Seminar Nasional Optimasi Sistem (SNOS) 2026." />

            <div class="grid gap-4 md:grid-cols-4">
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
                    <CardContent class="text-3xl font-bold text-white">{{ stats[card.key] }}</CardContent>
                </Card>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                    <CardContent class="pt-6">
                        <DonutChart title="Peserta per Jenis" :data="participantChartData" />
                    </CardContent>
                </Card>
                <Card class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                    <CardContent class="pt-6">
                        <DonutChart title="Artikel per Status" :data="articleChartData" />
                    </CardContent>
                </Card>
                <Card class="border-indigo-100 bg-indigo-50 dark:border-border dark:bg-indigo-950/40">
                    <CardContent class="pt-6">
                        <DonutChart title="Artikel per Jurnal Tujuan" :data="journalChartData" />
                    </CardContent>
                </Card>
                <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                    <CardContent class="pt-6">
                        <DonutChart title="Pembayaran per Status" :data="paymentChartData" />
                    </CardContent>
                </Card>
            </div>

            <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2"><ClipboardList class="size-4 text-muted-foreground" /> Pendaftaran Terbaru</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[420px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="py-2">Nomor</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="registration in recentRegistrations"
                                    :key="registration.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-2 font-mono">
                                        <Link :href="`/admin/participants/${registration.id}`" class="text-primary hover:underline">
                                            {{ registration.registration_number }}
                                        </Link>
                                    </td>
                                    <td>{{ registration.user.name }}</td>
                                    <td class="py-2">
                                        <Badge :variant="registrationStatusVariants[registration.status] ?? 'secondary'">
                                            {{ registrationStatusLabels[registration.status] ?? registration.status }}
                                        </Badge>
                                    </td>
                                </tr>
                                <tr v-if="recentRegistrations.length === 0">
                                    <td colspan="3" class="py-6 text-center text-muted-foreground">Belum ada pendaftaran.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
