<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Award, Download, Mic } from 'lucide-vue-next';

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
            <div class="brand-hero-panel flex items-center gap-4 rounded-2xl p-6 text-white shadow-md">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-full bg-white/15">
                    <Mic class="size-7" />
                </span>
                <div>
                    <p class="text-sm text-white/80">Selamat datang,</p>
                    <h1 class="text-xl font-semibold">{{ page.props.auth.user.name }}</h1>
                    <p class="mt-1 text-sm text-white/80">Anda terdaftar sebagai narasumber pada Seminar Nasional Optimasi Sistem (SNOS) 2026.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Award class="size-4 text-muted-foreground" />
                <h2 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">Sertifikat Saya</h2>
            </div>

            <EmptyState
                v-if="certificates.length === 0"
                :icon="Award"
                title="Belum ada sertifikat"
                description="Sertifikat yang diterbitkan panitia untuk Anda akan tampil di sini."
            />

            <Card
                v-for="certificate in certificates"
                :key="certificate.id"
                class="border-emerald-200 bg-emerald-50 transition-shadow duration-200 hover:shadow-md dark:border-emerald-900 dark:bg-emerald-950/40"
            >
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base text-emerald-800 dark:text-emerald-300">
                        <Award class="size-4" /> Sertifikat Narasumber
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <p>
                        Nomor: <span class="font-mono">{{ certificate.certificate_number }}</span>
                    </p>
                    <p v-if="certificate.jp_hours">Jumlah JP: {{ certificate.jp_hours }}</p>
                    <a v-if="certificate.file_path" :href="`/storage/${certificate.file_path}`" target="_blank">
                        <Button size="sm"><Download class="size-4" /> Unduh Sertifikat</Button>
                    </a>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
