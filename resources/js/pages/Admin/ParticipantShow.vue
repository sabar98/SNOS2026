<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    articleStatusLabels,
    articleStatusVariants,
    attendanceStatusLabels,
    attendanceStatusVariants,
    certificateRoleLabels,
    participantTypeLabels,
    paymentStatusLabels,
    paymentStatusVariants,
    paymentTypeLabels,
    registrationStatusLabels,
    registrationStatusVariants,
} from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { UserRound } from 'lucide-vue-next';

interface Registration {
    id: number;
    registration_number: string;
    participant_type: string;
    attendance_method: string;
    status: string;
    user: { name: string; email: string; whatsapp_number: string | null; profile: { institution: string; study_program: string } | null };
    payments: { id: number; type: string; amount: string; status: string }[];
    articles: { id: number; title: string; status: string; journal: { name: string } | null }[];
    attendances: { id: number; type: string; status: string }[];
    evaluation: unknown | null;
    certificates: { id: number; certificate_number: string; role: string }[];
}

defineProps<{
    registration: Registration;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Detail Peserta', href: '#' }];
</script>

<template>
    <Head title="Detail Peserta" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
            <PageHeader :icon="UserRound" title="Detail Peserta" description="Riwayat pendaftaran, pembayaran, artikel, dan sertifikat peserta." />

            <Card class="border-sky-200 bg-sky-50 dark:border-sky-900 dark:bg-sky-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-2 space-y-0">
                    <CardTitle class="text-sky-800 dark:text-sky-300">{{ registration.user.name }}</CardTitle>
                    <Badge :variant="registrationStatusVariants[registration.status] ?? 'secondary'">
                        {{ registrationStatusLabels[registration.status] ?? registration.status }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-1 text-sm">
                    <p>Email: {{ registration.user.email }}</p>
                    <p v-if="registration.user.whatsapp_number">WhatsApp: {{ registration.user.whatsapp_number }}</p>
                    <p v-if="registration.user.profile">
                        {{ registration.user.profile.institution }} &middot; {{ registration.user.profile.study_program }}
                    </p>
                    <p>
                        Nomor registrasi: <span class="font-mono">{{ registration.registration_number }}</span>
                    </p>
                    <p>
                        Jenis: {{ participantTypeLabels[registration.participant_type] ?? registration.participant_type }} ({{
                            registration.attendance_method
                        }})
                    </p>
                </CardContent>
            </Card>

            <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                <CardHeader><CardTitle class="text-base">Pembayaran</CardTitle></CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <div v-for="payment in registration.payments" :key="payment.id" class="flex items-center justify-between">
                        <span
                            >{{ paymentTypeLabels[payment.type] ?? payment.type }} &mdash; Rp{{
                                Number(payment.amount).toLocaleString('id-ID')
                            }}</span
                        >
                        <Badge :variant="paymentStatusVariants[payment.status] ?? 'secondary'">
                            {{ paymentStatusLabels[payment.status] ?? payment.status }}
                        </Badge>
                    </div>
                    <p v-if="registration.payments.length === 0" class="text-muted-foreground">Belum ada tagihan.</p>
                </CardContent>
            </Card>

            <Card v-if="registration.articles.length" class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                <CardHeader><CardTitle class="text-base">Artikel</CardTitle></CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <div v-for="article in registration.articles" :key="article.id" class="flex items-center justify-between">
                        <span
                            >{{ article.title }}<span v-if="article.journal"> &mdash; {{ article.journal.name }}</span></span
                        >
                        <Badge :variant="articleStatusVariants[article.status] ?? 'secondary'">
                            {{ articleStatusLabels[article.status] ?? article.status }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>

            <Card class="border-teal-100 bg-teal-50 dark:border-border dark:bg-teal-950/40">
                <CardHeader><CardTitle class="text-base">Kehadiran &amp; Evaluasi</CardTitle></CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <div v-for="attendance in registration.attendances" :key="attendance.id" class="flex items-center justify-between">
                        <span>{{ attendance.type }}</span>
                        <Badge :variant="attendanceStatusVariants[attendance.status] ?? 'secondary'">
                            {{ attendanceStatusLabels[attendance.status] ?? attendance.status }}
                        </Badge>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Evaluasi</span>
                        <Badge :variant="registration.evaluation ? 'success' : 'secondary'">
                            {{ registration.evaluation ? 'Sudah diisi' : 'Belum diisi' }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="registration.certificates.length" class="border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40">
                <CardHeader><CardTitle class="text-base text-emerald-800 dark:text-emerald-300">Sertifikat</CardTitle></CardHeader>
                <CardContent class="space-y-1 text-sm">
                    <p v-for="certificate in registration.certificates" :key="certificate.id">
                        {{ certificateRoleLabels[certificate.role] ?? certificate.role }} &mdash;
                        <span class="font-mono">{{ certificate.certificate_number }}</span>
                    </p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
