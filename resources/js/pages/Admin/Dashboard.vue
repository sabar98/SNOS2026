<script setup lang="ts">
import BarChart from '@/components/charts/BarChart.vue';
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
import { AlertTriangle, Eye, Users, Wallet } from 'lucide-vue-next';
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
        cardClass: 'border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40',
        titleClass: 'text-emerald-800/80 dark:text-emerald-300/80',
        iconClass: 'bg-emerald-500 text-white',
        valueClass: 'text-emerald-800 dark:text-emerald-300',
    },
    {
        key: 'pending_payment_verifications' as const,
        title: 'Menunggu Verifikasi Bayar',
        icon: Wallet,
        cardClass: 'border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/40',
        titleClass: 'text-amber-800/80 dark:text-amber-300/80',
        iconClass: 'bg-amber-500 text-white',
        valueClass: 'text-amber-800 dark:text-amber-300',
    },
    {
        key: 'articles_awaiting_administration' as const,
        title: 'Artikel Perlu Diperiksa',
        icon: AlertTriangle,
        cardClass: 'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950/40',
        titleClass: 'text-red-800/80 dark:text-red-300/80',
        iconClass: 'bg-red-500 text-white',
        valueClass: 'text-red-800 dark:text-red-300',
    },
    {
        key: 'articles_in_review' as const,
        title: 'Artikel Sedang Direview',
        icon: Eye,
        cardClass: 'border-sky-200 bg-sky-50 dark:border-sky-900 dark:bg-sky-950/40',
        titleClass: 'text-sky-800/80 dark:text-sky-300/80',
        iconClass: 'bg-sky-500 text-white',
        valueClass: 'text-sky-800 dark:text-sky-300',
    },
];
</script>

<template>
    <Head title="Dashboard Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="grid gap-4 md:grid-cols-4">
                <Card v-for="card in cards" :key="card.key" :class="card.cardClass">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle :class="['text-sm font-medium', card.titleClass]">{{ card.title }}</CardTitle>
                        <span :class="['flex size-9 items-center justify-center rounded-full shadow-sm', card.iconClass]">
                            <component :is="card.icon" class="size-4" />
                        </span>
                    </CardHeader>
                    <CardContent :class="['text-2xl font-bold', card.valueClass]">{{ stats[card.key] }}</CardContent>
                </Card>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardContent class="pt-6">
                        <BarChart title="Peserta per Jenis" :data="participantChartData" />
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <BarChart title="Artikel per Status" :data="articleChartData" />
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <BarChart title="Artikel per Jurnal Tujuan" :data="journalChartData" />
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <BarChart title="Pembayaran per Status" :data="paymentChartData" />
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader><CardTitle>Pendaftaran Terbaru</CardTitle></CardHeader>
                <CardContent>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="py-2">Nomor</th>
                                <th>Nama</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="registration in recentRegistrations" :key="registration.id" class="border-b last:border-0">
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
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
