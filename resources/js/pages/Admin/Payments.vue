<script setup lang="ts">
import DonutChart from '@/components/charts/DonutChart.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { paymentStatusLabels, paymentStatusVariants, paymentTypeLabels, toChartData } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { FileSpreadsheet, ListChecks, Printer, Search, Wallet } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';

interface Payment {
    id: number;
    type: string;
    amount: string;
    status: string;
    notes: string | null;
    proof_file_path: string | null;
    participant_name: string | null;
    participant_email: string | null;
    payable: { registration_number?: string; title?: string } | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    payments: { data: Payment[]; links: PaginationLink[]; total: number };
    paymentsByStatus: Record<string, number>;
    paymentsByType: Record<string, number>;
    search: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Verifikasi Pembayaran', href: '/admin/payments' }];

const statusChartData = computed(() => toChartData(props.paymentsByStatus, paymentStatusLabels));
const typeChartData = computed(() => toChartData(props.paymentsByType, paymentTypeLabels));

// Draft notes the admin is typing per payment row, kept until a decision is submitted.
const notesDraft = reactive<Record<number, string>>({});

// Server-side search (the list is paginated, so filtering only the current page would hide
// matches on other pages) — debounced so it doesn't fire a request on every keystroke.
const search = ref(props.search);
let searchTimeout: ReturnType<typeof setTimeout> | undefined;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.payments.index'),
            { search: value || undefined },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 400);
});

// Export/print always reflect whatever is currently searched.
const exportExcelUrl = computed(() => route('admin.payments.export', search.value ? { search: search.value } : {}));
const exportPdfUrl = computed(() => route('admin.payments.export-pdf', search.value ? { search: search.value } : {}));

// Can still be acted on: not yet decided, or decided but reversible (terverifikasi).
// ditolak and dibatalkan are left as final — nothing to undo them back to.
function isActionable(status: string) {
    return status === 'belum_bayar' || status === 'menunggu_verifikasi' || status === 'perlu_perbaikan' || status === 'terverifikasi';
}

function submitDecision(paymentId: number, decision: string, fallbackNotes: string) {
    const notes = notesDraft[paymentId]?.trim() || fallbackNotes;

    useForm({ decision, notes }).put(route('admin.payments.update', paymentId), {
        preserveScroll: true,
        onSuccess: () => {
            delete notesDraft[paymentId];
        },
    });
}

function verify(paymentId: number) {
    submitDecision(paymentId, 'terverifikasi', '');
}

function requestFix(paymentId: number) {
    submitDecision(paymentId, 'perlu_perbaikan', 'Bukti pembayaran tidak sesuai, mohon unggah ulang.');
}

function reject(paymentId: number) {
    if (!confirm('Tolak pembayaran ini? Peserta tidak bisa mengunggah ulang bukti untuk pembayaran ini.')) {
        return;
    }
    submitDecision(paymentId, 'ditolak', 'Bukti pembayaran tidak valid.');
}

function cancel(paymentId: number) {
    if (
        !confirm(
            'Batalkan pembayaran ini? Peserta tidak bisa mengunggah ulang bukti untuk pembayaran ini. Jika pembayaran ini sebelumnya sudah terverifikasi, status pendaftaran akan dikembalikan ke "Menunggu Pembayaran".',
        )
    ) {
        return;
    }
    submitDecision(paymentId, 'dibatalkan', 'Pembayaran dibatalkan oleh panitia.');
}

function resetToUnpaid(paymentId: number) {
    if (!confirm('Ubah pembayaran ini kembali menjadi Belum Bayar? Peserta akan diminta mengunggah ulang bukti pembayaran.')) {
        return;
    }
    submitDecision(paymentId, 'belum_bayar', 'Verifikasi dibatalkan oleh panitia, mohon unggah ulang bukti pembayaran.');
}
</script>

<template>
    <Head title="Verifikasi Pembayaran" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader :icon="Wallet" title="Verifikasi Pembayaran" description="Tinjau dan verifikasi bukti pembayaran peserta SNOS 2026." />

            <div class="grid gap-4 md:grid-cols-2">
                <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                    <CardContent class="pt-6">
                        <DonutChart title="Pembayaran per Status" :data="statusChartData" />
                    </CardContent>
                </Card>
                <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                    <CardContent class="pt-6">
                        <DonutChart title="Pembayaran per Tipe" :data="typeChartData" />
                    </CardContent>
                </Card>
            </div>

            <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <div class="flex items-center gap-2">
                        <CardTitle class="flex items-center gap-2"><ListChecks class="size-4 text-muted-foreground" /> Daftar Pembayaran</CardTitle>
                        <Badge variant="secondary">{{ payments.total }} transaksi</Badge>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="relative w-full max-w-xs">
                            <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="search" placeholder="Cari referensi, nama, atau email..." class="pl-9" />
                        </div>
                        <a :href="exportExcelUrl">
                            <Button variant="outline" size="sm" class="gap-2"><FileSpreadsheet class="size-4" /> Export Excel</Button>
                        </a>
                        <a :href="exportPdfUrl" target="_blank">
                            <Button variant="outline" size="sm" class="gap-2"><Printer class="size-4" /> Cetak PDF</Button>
                        </a>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="py-2">Referensi</th>
                                    <th>Nama</th>
                                    <th>Tipe</th>
                                    <th>Nominal</th>
                                    <th>Bukti</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="payments.data.length === 0">
                                    <td colspan="7" class="py-6 text-center text-muted-foreground">
                                        Tidak ada pembayaran yang cocok dengan pencarian.
                                    </td>
                                </tr>
                                <tr
                                    v-for="payment in payments.data"
                                    :key="payment.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-2">{{ payment.payable?.registration_number ?? payment.payable?.title ?? '-' }}</td>
                                    <td>
                                        <div>{{ payment.participant_name ?? '-' }}</div>
                                        <div v-if="payment.participant_email" class="text-xs text-muted-foreground">
                                            {{ payment.participant_email }}
                                        </div>
                                    </td>
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
                                        <p v-if="payment.notes" class="mt-1 max-w-[220px] text-xs text-muted-foreground">{{ payment.notes }}</p>
                                    </td>
                                    <td class="py-2">
                                        <div v-if="isActionable(payment.status)" class="flex flex-col gap-2">
                                            <textarea
                                                v-model="notesDraft[payment.id]"
                                                rows="1"
                                                placeholder="Catatan (wajib kecuali Verifikasi)"
                                                class="w-48 resize-y rounded-md border border-input bg-background px-2 py-1 text-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                            ></textarea>
                                            <div class="flex flex-wrap gap-2">
                                                <Button
                                                    v-if="payment.status === 'menunggu_verifikasi'"
                                                    size="sm"
                                                    @click="verify(payment.id)"
                                                    >Verifikasi</Button
                                                >
                                                <Button
                                                    v-if="payment.status === 'menunggu_verifikasi'"
                                                    size="sm"
                                                    variant="outline"
                                                    @click="requestFix(payment.id)"
                                                    >Perlu Perbaikan</Button
                                                >
                                                <Button
                                                    v-if="payment.status === 'menunggu_verifikasi'"
                                                    size="sm"
                                                    variant="destructive"
                                                    @click="reject(payment.id)"
                                                    >Tolak</Button
                                                >
                                                <Button
                                                    v-if="payment.status === 'terverifikasi'"
                                                    size="sm"
                                                    variant="outline"
                                                    @click="resetToUnpaid(payment.id)"
                                                    >Ubah ke Belum Bayar</Button
                                                >
                                                <Button size="sm" variant="ghost" class="text-destructive" @click="cancel(payment.id)"
                                                    >Batalkan</Button
                                                >
                                            </div>
                                        </div>
                                        <span v-else class="text-xs text-muted-foreground">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <Link
                            v-for="link in payments.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            :class="[
                                'rounded px-3 py-1 text-sm',
                                link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground',
                                !link.url && 'pointer-events-none opacity-50',
                            ]"
                        >
                            <span v-html="link.label" />
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
