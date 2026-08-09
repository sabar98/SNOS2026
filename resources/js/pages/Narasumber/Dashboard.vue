<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

interface Certificate {
    id: number;
    certificate_number: string;
    jp_hours: number | null;
    file_path: string | null;
}

defineProps<{
    certificates: Certificate[];
}>();

const page = usePage<SharedData>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/narasumber/dashboard' }];
</script>

<template>
    <Head title="Dashboard Narasumber" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Selamat datang, {{ page.props.auth.user.name }}</CardTitle>
                </CardHeader>
                <CardContent class="text-sm text-muted-foreground">
                    Anda terdaftar sebagai narasumber pada Seminar Nasional Optimasi Sistem (SNOS) 2026. Sertifikat yang diterbitkan panitia untuk
                    Anda akan tampil di bawah ini.
                </CardContent>
            </Card>

            <Card
                v-for="certificate in certificates"
                :key="certificate.id"
                class="border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40"
            >
                <CardHeader>
                    <CardTitle class="text-base text-emerald-800 dark:text-emerald-300">Sertifikat Narasumber</CardTitle>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <p>
                        Nomor: <span class="font-mono">{{ certificate.certificate_number }}</span>
                    </p>
                    <p v-if="certificate.jp_hours">Jumlah JP: {{ certificate.jp_hours }}</p>
                    <a v-if="certificate.file_path" :href="`/storage/${certificate.file_path}`" target="_blank">
                        <Button size="sm">Unduh Sertifikat</Button>
                    </a>
                </CardContent>
            </Card>

            <div v-if="certificates.length === 0" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                Belum ada sertifikat yang diterbitkan untuk Anda.
            </div>
        </div>
    </AppLayout>
</template>
