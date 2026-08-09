<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';

interface Speaker {
    name: string;
    title: string;
}

interface TimelineItem {
    label: string;
    date: string;
}

interface Journal {
    id: number;
    name: string;
    type: string;
    publisher: string | null;
}

interface Seminar {
    name: string;
    theme: string;
    date_range: string;
    location: string;
    scope: string[];
    speakers: Speaker[];
    timeline: TimelineItem[];
    fees: Record<string, number>;
}

defineProps<{
    seminar: Seminar;
    journals: Journal[];
}>();

const feeLabels: Record<string, string> = {
    presenter_luring: 'Presenter Luring',
    presenter_daring: 'Presenter Daring',
    peserta_umum: 'Peserta Umum',
    peserta_mahasiswa: 'Peserta Mahasiswa',
};

function formatRupiah(value: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
}
</script>

<template>
    <Head :title="seminar.name" />

    <div class="min-h-screen bg-background text-foreground">
        <header class="sticky top-0 z-30 border-b bg-background/80 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <span class="flex items-center gap-2 font-semibold">
                    <span
                        class="flex size-8 items-center justify-center rounded-md bg-gradient-to-br from-sky-500 to-blue-700 text-sm text-white shadow-sm"
                    >
                        S
                    </span>
                    SNOS 2026
                </span>
                <nav class="flex items-center gap-3">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')">
                        <Button size="sm">Dashboard</Button>
                    </Link>
                    <template v-else>
                        <Link :href="route('login')">
                            <Button variant="ghost" size="sm">Masuk</Button>
                        </Link>
                        <Link :href="route('register')">
                            <Button size="sm">Daftar Sekarang</Button>
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <section class="brand-hero-panel relative overflow-hidden">
            <div class="relative mx-auto max-w-5xl px-6 py-24 text-center text-white">
                <span class="inline-flex items-center rounded-full bg-white/15 px-4 py-1 text-xs font-medium uppercase tracking-wide backdrop-blur">
                    {{ seminar.date_range }}
                </span>
                <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl">{{ seminar.name }}</h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-sky-50/90">{{ seminar.theme }}</p>
                <p class="mt-6 text-sm text-sky-100/80">{{ seminar.date_range }} &middot; {{ seminar.location }}</p>
                <div class="mt-8 flex items-center justify-center gap-3">
                    <Link :href="route('register')">
                        <Button size="lg" class="bg-white text-sky-700 shadow-lg hover:bg-sky-50">Daftar Sekarang</Button>
                    </Link>
                    <a href="#detail">
                        <Button size="lg" variant="outline" class="border-white/40 bg-white/5 text-white hover:bg-white/15 hover:text-white">
                            Lihat Detail
                        </Button>
                    </a>
                </div>
            </div>
            <svg class="relative block w-full text-background" viewBox="0 0 1440 48" preserveAspectRatio="none">
                <path fill="currentColor" d="M0,32 C360,0 1080,64 1440,16 L1440,48 L0,48 Z" />
            </svg>
        </section>

        <section id="detail" class="mx-auto max-w-6xl px-6 py-16">
            <div class="grid gap-6 md:grid-cols-2">
                <Card class="border-sky-100 shadow-sm transition-shadow hover:shadow-md dark:border-border">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-sky-700 dark:text-sky-400">Ruang Lingkup Artikel</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="list-inside list-disc space-y-1 text-sm text-muted-foreground">
                            <li v-for="item in seminar.scope" :key="item">{{ item }}</li>
                        </ul>
                    </CardContent>
                </Card>

                <Card class="border-sky-100 shadow-sm transition-shadow hover:shadow-md dark:border-border">
                    <CardHeader>
                        <CardTitle class="text-sky-700 dark:text-sky-400">Narasumber</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-2 text-sm">
                            <li v-for="speaker in seminar.speakers" :key="speaker.name">
                                <span class="font-medium">{{ speaker.name }}</span>
                                <span class="block text-muted-foreground">{{ speaker.title }}</span>
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <Card class="border-sky-100 shadow-sm transition-shadow hover:shadow-md dark:border-border">
                    <CardHeader>
                        <CardTitle class="text-sky-700 dark:text-sky-400">Biaya Pendaftaran</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-1 text-sm">
                            <li v-for="(fee, key) in seminar.fees" :key="key" class="flex justify-between">
                                <span class="text-muted-foreground">{{ feeLabels[key] ?? key }}</span>
                                <span class="font-medium">{{ formatRupiah(fee) }}</span>
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <Card class="border-sky-100 shadow-sm transition-shadow hover:shadow-md dark:border-border">
                    <CardHeader>
                        <CardTitle class="text-sky-700 dark:text-sky-400">Jurnal &amp; Prosiding Tujuan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-2 text-sm">
                            <li v-for="journal in journals" :key="journal.id">
                                <span class="font-medium">{{ journal.name }}</span>
                                <span v-if="journal.publisher" class="block text-muted-foreground">{{ journal.publisher }}</span>
                            </li>
                            <li v-if="journals.length === 0" class="text-muted-foreground">Belum ada jurnal terdaftar.</li>
                        </ul>
                    </CardContent>
                </Card>
            </div>

            <Card class="mt-6 border-sky-100 shadow-sm dark:border-border">
                <CardHeader>
                    <CardTitle class="text-sky-700 dark:text-sky-400">Timeline Kegiatan</CardTitle>
                </CardHeader>
                <CardContent>
                    <ol class="space-y-3 text-sm">
                        <li
                            v-for="item in seminar.timeline"
                            :key="item.label"
                            class="flex items-center justify-between border-b pb-2 last:border-b-0"
                        >
                            <span>{{ item.label }}</span>
                            <span class="font-medium text-muted-foreground">{{ item.date }}</span>
                        </li>
                    </ol>
                </CardContent>
            </Card>
        </section>

        <footer class="border-t bg-muted/40 py-8 text-center text-sm text-muted-foreground">
            &copy; {{ new Date().getFullYear() }} {{ seminar.name }}
        </footer>
    </div>
</template>
