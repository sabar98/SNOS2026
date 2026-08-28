<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    articleStatusLabels,
    articleStatusVariants,
    participantTypeLabels,
    paymentStatusLabels,
    paymentStatusVariants,
    registrationStatusLabels,
    registrationStatusVariants,
} from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ClipboardList, MessageSquareHeart, Pencil } from 'lucide-vue-next';

interface Payment {
    id: number;
    type: string;
    amount: string;
    bank_account: string | null;
    payment_code: string;
    due_at: string | null;
    status: string;
    notes: string | null;
}

interface Article {
    id: number;
    title: string;
    status: string;
}

interface Registration {
    id: number;
    registration_number: string;
    participant_type: string;
    attendance_method: string;
    article_scope: string | null;
    institution: string;
    special_needs: string | null;
    join_gala_dinner: boolean;
    status: string;
    payments: Payment[];
    articles: Article[];
}

const props = defineProps<{
    registration: Registration;
    feeLocked: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: props.registration.registration_number, href: '#' }];

const isPresenter = ['presenter_luring', 'presenter_daring'].includes(props.registration.participant_type);

const proofForm = useForm({
    proof: null as File | null,
});

function uploadProof(paymentId: number) {
    proofForm.post(route('participant.payments.upload', paymentId), {
        forceFormData: true,
        preserveScroll: true,
    });
}

function formatRupiah(value: string): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value));
}
</script>

<template>
    <Head :title="registration.registration_number" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
            <PageHeader :icon="ClipboardList" title="Detail Pendaftaran" description="Status pendaftaran, pembayaran, dan artikel Anda.">
                <template #actions>
                    <Link :href="route('participant.registrations.edit', registration.id)">
                        <Button variant="outline" class="gap-2"><Pencil class="size-4" /> Ubah Data</Button>
                    </Link>
                </template>
            </PageHeader>

            <Card class="border-sky-200 bg-sky-50 dark:border-sky-900 dark:bg-sky-950/40">
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle class="text-sky-800 dark:text-sky-300">{{ registration.registration_number }}</CardTitle>
                    <Badge :variant="registrationStatusVariants[registration.status] ?? 'secondary'">
                        {{ registrationStatusLabels[registration.status] ?? registration.status }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-1 text-sm">
                    <p>
                        Jenis:
                        <span class="font-medium">{{ participantTypeLabels[registration.participant_type] ?? registration.participant_type }}</span>
                    </p>
                    <p>
                        Metode kehadiran: <span class="font-medium">{{ registration.attendance_method }}</span>
                    </p>
                    <p>
                        Institusi: <span class="font-medium">{{ registration.institution }}</span>
                    </p>
                    <p v-if="registration.article_scope">
                        Bidang / scope artikel: <span class="font-medium">{{ registration.article_scope }}</span>
                    </p>
                    <p v-if="registration.special_needs">
                        Kebutuhan khusus: <span class="font-medium">{{ registration.special_needs }}</span>
                    </p>
                    <p v-if="registration.join_gala_dinner" class="flex items-center gap-1.5 text-sky-700 dark:text-sky-400">
                        <MessageSquareHeart class="size-3.5" /> Ikut malam keakraban
                    </p>
                </CardContent>
            </Card>

            <Card v-for="payment in registration.payments" :key="payment.id">
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle class="text-base">Pembayaran {{ payment.type === 'registrasi' ? 'Registrasi' : 'Publikasi' }}</CardTitle>
                    <Badge :variant="paymentStatusVariants[payment.status] ?? 'secondary'">
                        {{ paymentStatusLabels[payment.status] ?? payment.status }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <p>
                        Nominal: <span class="font-medium">{{ formatRupiah(payment.amount) }}</span>
                    </p>
                    <p v-if="payment.bank_account">Rekening tujuan: {{ payment.bank_account }}</p>
                    <p>
                        Kode pembayaran: <span class="font-mono">{{ payment.payment_code }}</span>
                    </p>
                    <p v-if="payment.due_at">Batas waktu: {{ new Date(payment.due_at).toLocaleString('id-ID') }}</p>
                    <p v-if="payment.notes" class="text-amber-600">Catatan panitia: {{ payment.notes }}</p>

                    <form
                        v-if="payment.status === 'belum_bayar' || payment.status === 'perlu_perbaikan'"
                        class="flex flex-wrap items-center gap-2 pt-2"
                        @submit.prevent="uploadProof(payment.id)"
                    >
                        <input
                            type="file"
                            accept="image/*,.pdf"
                            class="text-sm"
                            @change="proofForm.proof = ($event.target as HTMLInputElement).files?.[0] ?? null"
                        />
                        <Button type="submit" size="sm" :disabled="proofForm.processing">Unggah Bukti</Button>
                    </form>
                </CardContent>
            </Card>

            <Card v-if="isPresenter">
                <CardHeader>
                    <CardTitle class="text-base">Artikel</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 text-sm">
                    <div
                        v-for="article in registration.articles"
                        :key="article.id"
                        class="flex flex-wrap items-center justify-between gap-2 border-b pb-2"
                    >
                        <span>{{ article.title }}</span>
                        <div class="flex items-center gap-2">
                            <Badge :variant="articleStatusVariants[article.status] ?? 'secondary'">
                                {{ articleStatusLabels[article.status] ?? article.status }}
                            </Badge>
                            <Link :href="route('participant.articles.show', article.id)">
                                <Button variant="outline" size="sm">Detail</Button>
                            </Link>
                        </div>
                    </div>

                    <Link
                        v-if="registration.status === 'pembayaran_terverifikasi' && registration.articles.length === 0"
                        :href="route('participant.articles.create', registration.id)"
                    >
                        <Button size="sm">Unggah Artikel</Button>
                    </Link>
                    <p v-else-if="registration.articles.length === 0" class="text-muted-foreground">
                        Artikel dapat diunggah setelah pembayaran terverifikasi.
                    </p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
