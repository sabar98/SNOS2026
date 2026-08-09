<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';

interface Certificate {
    certificate_number: string;
    role: string;
    jp_hours: number | null;
    signed_at: string | null;
    user: { name: string };
}

defineProps<{
    certificate: Certificate | null;
    seminarName: string;
}>();

const roleLabels: Record<string, string> = {
    peserta: 'Peserta',
    presenter: 'Presenter',
    moderator: 'Moderator',
    reviewer: 'Reviewer',
    narasumber: 'Narasumber',
    panitia: 'Panitia',
};
</script>

<template>
    <Head title="Verifikasi Sertifikat" />

    <div class="brand-hero-panel relative flex min-h-screen items-center justify-center p-6">
        <Card class="relative w-full max-w-md shadow-xl">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <span
                        class="flex size-8 items-center justify-center rounded-md bg-gradient-to-br from-sky-500 to-blue-700 text-sm text-white shadow-sm"
                    >
                        S
                    </span>
                    Verifikasi Sertifikat
                </CardTitle>
            </CardHeader>
            <CardContent>
                <template v-if="certificate">
                    <div
                        class="mb-4 inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-sm text-sky-800 dark:bg-sky-950 dark:text-sky-300"
                    >
                        Sertifikat Valid
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Nama</dt>
                            <dd class="font-medium">{{ certificate.user.name }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Peran</dt>
                            <dd class="font-medium">{{ roleLabels[certificate.role] ?? certificate.role }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Nomor Sertifikat</dt>
                            <dd class="font-mono">{{ certificate.certificate_number }}</dd>
                        </div>
                        <div v-if="certificate.jp_hours">
                            <dt class="text-muted-foreground">Jumlah JP</dt>
                            <dd class="font-medium">{{ certificate.jp_hours }}</dd>
                        </div>
                        <div v-if="certificate.signed_at">
                            <dt class="text-muted-foreground">Diterbitkan</dt>
                            <dd class="font-medium">{{ new Date(certificate.signed_at).toLocaleDateString('id-ID') }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Kegiatan</dt>
                            <dd class="font-medium">{{ seminarName }}</dd>
                        </div>
                    </dl>
                </template>
                <template v-else>
                    <div
                        class="mb-4 inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1 text-sm text-red-800 dark:bg-red-950 dark:text-red-300"
                    >
                        Sertifikat Tidak Ditemukan
                    </div>
                    <p class="text-sm text-muted-foreground">Kode verifikasi tidak valid atau sertifikat telah dicabut.</p>
                </template>

                <Link :href="route('home')" class="mt-6 block text-sm text-primary underline">Kembali ke beranda</Link>
            </CardContent>
        </Card>
    </div>
</template>
