<script setup lang="ts">
import BarChart from '@/components/charts/BarChart.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { paymentStatusLabels, paymentStatusVariants, paymentTypeLabels, toChartData } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Payment {
    id: number;
    type: string;
    amount: string;
    status: string;
    proof_file_path: string | null;
    payable: { registration_number?: string; title?: string } | null;
}

const props = defineProps<{
    payments: { data: Payment[] };
    paymentsByStatus: Record<string, number>;
    paymentsByType: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Verifikasi Pembayaran', href: '/admin/payments' }];

const statusChartData = computed(() => toChartData(props.paymentsByStatus, paymentStatusLabels));
const typeChartData = computed(() => toChartData(props.paymentsByType, paymentTypeLabels));

const notesDraft: Record<number, string> = {};

function verify(paymentId: number) {
    useForm({ decision: 'terverifikasi', notes: '' }).put(route('admin.payments.update', paymentId), { preserveScroll: true });
}

function reject(paymentId: number) {
    useForm({ decision: 'perlu_perbaikan', notes: notesDraft[paymentId] ?? 'Bukti pembayaran tidak sesuai.' }).put(
        route('admin.payments.update', paymentId),
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Verifikasi Pembayaran" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardContent class="pt-6">
                        <BarChart title="Pembayaran per Status" :data="statusChartData" />
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <BarChart title="Pembayaran per Tipe" :data="typeChartData" />
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader><CardTitle>Daftar Pembayaran</CardTitle></CardHeader>
                <CardContent>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="py-2">Referensi</th>
                                <th>Tipe</th>
                                <th>Nominal</th>
                                <th>Bukti</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payment in payments.data" :key="payment.id" class="border-b last:border-0">
                                <td class="py-2">{{ payment.payable?.registration_number ?? payment.payable?.title ?? '-' }}</td>
                                <td>{{ paymentTypeLabels[payment.type] ?? payment.type }}</td>
                                <td>Rp{{ Number(payment.amount).toLocaleString('id-ID') }}</td>
                                <td>
                                    <a
                                        v-if="payment.proof_file_path"
                                        :href="`/storage/${payment.proof_file_path}`"
                                        target="_blank"
                                        class="text-primary underline"
                                        >Lihat</a
                                    >
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>
                                <td>
                                    <Badge :variant="paymentStatusVariants[payment.status] ?? 'secondary'">
                                        {{ paymentStatusLabels[payment.status] ?? payment.status }}
                                    </Badge>
                                </td>
                                <td v-if="payment.status === 'menunggu_verifikasi'" class="flex gap-2 py-2">
                                    <Button size="sm" @click="verify(payment.id)">Verifikasi</Button>
                                    <Button size="sm" variant="destructive" @click="reject(payment.id)">Tolak</Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
