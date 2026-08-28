<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useInitials } from '@/composables/useInitials';
import { Head, Link } from '@inertiajs/vue3';
import {
    BookOpenText,
    CalendarClock,
    ChevronLeft,
    ChevronRight,
    Facebook,
    Handshake,
    Instagram,
    Mail,
    MapPin,
    Menu,
    Newspaper,
    Phone,
    Quote,
    Wallet,
    X,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, type Ref } from 'vue';

const mobileMenuOpen = ref(false);

interface Speaker {
    name: string;
    title: string;
    topic?: string | null;
    photo_path?: string | null;
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

interface Contact {
    email: string;
    phone: string;
    facebook: string;
    instagram: string;
    address: string;
}

interface LeaderMessage {
    name: string;
    title: string;
    message: string;
    photo_path?: string | null;
}

interface Partner {
    name: string;
    logo_path?: string | null;
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
    organizer: string;
    contact: Contact;
    leader_message: LeaderMessage;
    partners: Partner[];
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

const { getInitials } = useInitials();

function useCarousel(track: Ref<HTMLElement | null>, cardSelector: string) {
    function scroll(direction: number) {
        const el = track.value;
        if (!el) return;
        const card = el.querySelector<HTMLElement>(cardSelector);
        const step = (card?.offsetWidth ?? 280) + 20;

        if (direction > 0 && el.scrollLeft + el.clientWidth >= el.scrollWidth - 4) {
            el.scrollTo({ left: 0, behavior: 'smooth' });
            return;
        }

        el.scrollBy({ left: direction * step, behavior: 'smooth' });
    }

    let autoplay: ReturnType<typeof setInterval> | null = null;

    function start() {
        stop();
        autoplay = setInterval(() => scroll(1), 2000);
    }

    function stop() {
        if (autoplay) {
            clearInterval(autoplay);
            autoplay = null;
        }
    }

    return { scroll, start, stop };
}

const speakerTrack = ref<HTMLElement | null>(null);
const speakerCarousel = useCarousel(speakerTrack, '[data-speaker-card]');

const partnerTrack = ref<HTMLElement | null>(null);
const partnerCarousel = useCarousel(partnerTrack, '[data-partner-card]');

onMounted(() => {
    speakerCarousel.start();
    partnerCarousel.start();
});
onUnmounted(() => {
    speakerCarousel.stop();
    partnerCarousel.stop();
});
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
                    <span class="hidden sm:inline">SNOS 2026</span>
                </span>

                <nav class="hidden items-center gap-3 sm:flex">
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

                <button
                    type="button"
                    class="inline-flex size-9 items-center justify-center rounded-md border text-foreground transition-colors hover:bg-muted sm:hidden"
                    :aria-expanded="mobileMenuOpen"
                    aria-label="Buka menu navigasi"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <X v-if="mobileMenuOpen" class="size-5" />
                    <Menu v-else class="size-5" />
                </button>
            </div>

            <div v-if="mobileMenuOpen" class="border-t bg-background px-6 py-4 sm:hidden">
                <nav class="flex flex-col gap-2">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" @click="mobileMenuOpen = false">
                        <Button size="sm" class="w-full">Dashboard</Button>
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" @click="mobileMenuOpen = false">
                            <Button variant="ghost" size="sm" class="w-full">Masuk</Button>
                        </Link>
                        <Link :href="route('register')" @click="mobileMenuOpen = false">
                            <Button size="sm" class="w-full">Daftar Sekarang</Button>
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

        <section class="overflow-hidden bg-background py-20">
            <div class="mx-auto max-w-6xl px-6">
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-5">
                    <div class="relative mx-auto w-full max-w-sm lg:col-span-2">
                        <div
                            class="absolute -inset-4 -z-10 rounded-[2.5rem] bg-gradient-to-br from-sky-200 via-violet-100 to-transparent opacity-70 blur-2xl dark:from-sky-950/60 dark:via-violet-950/40"
                        ></div>
                        <div
                            class="aspect-[4/5] w-full overflow-hidden rounded-3xl border-4 border-background bg-gradient-to-br from-sky-100 to-violet-100 shadow-xl dark:from-sky-950/50 dark:to-violet-950/50"
                        >
                            <img
                                v-if="seminar.leader_message.photo_path"
                                :src="`/storage/${seminar.leader_message.photo_path}`"
                                :alt="seminar.leader_message.name"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-7xl font-bold text-sky-400 dark:text-sky-500">
                                {{ getInitials(seminar.leader_message.name) }}
                            </div>
                        </div>
                        <div
                            class="absolute -bottom-5 left-1/2 flex w-[85%] -translate-x-1/2 flex-col items-center rounded-2xl border bg-background px-4 py-3 text-center shadow-lg"
                        >
                            <p class="text-sm font-semibold">{{ seminar.leader_message.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ seminar.leader_message.title }}</p>
                        </div>
                    </div>

                    <div class="lg:col-span-3">
                        <div
                            class="flex size-11 items-center justify-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400"
                        >
                            <Quote class="size-5" />
                        </div>
                        <span class="mt-4 block text-xs font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400">Kata Sambutan</span>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Sambutan Pimpinan</h2>
                        <p class="mt-6 max-w-2xl whitespace-pre-line text-lg leading-relaxed text-muted-foreground">
                            {{ seminar.leader_message.message }}
                        </p>
                        <div class="mt-8 flex items-center gap-3 border-t pt-6">
                            <div class="flex size-1.5 items-center justify-center rounded-full bg-sky-500"></div>
                            <p class="text-sm font-semibold">{{ seminar.leader_message.name }}</p>
                            <span class="text-sm text-muted-foreground">&middot; {{ seminar.leader_message.title }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="detail" class="bg-muted/20 py-16">
            <div class="mx-auto max-w-6xl px-6">
                <div class="relative mb-10">
                    <div class="mx-auto max-w-2xl text-center">
                        <span class="text-xs font-semibold uppercase tracking-widest text-violet-600 dark:text-violet-400">Pembicara</span>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Narasumber Kegiatan</h2>
                        <p class="mt-2 text-sm text-muted-foreground">Para ahli dan praktisi yang akan berbagi wawasan di SNOS 2026.</p>
                    </div>
                    <div class="mt-4 hidden justify-center gap-2 sm:absolute sm:right-0 sm:top-1/2 sm:mt-0 sm:flex sm:-translate-y-1/2">
                        <button
                            type="button"
                            aria-label="Sebelumnya"
                            class="flex size-10 items-center justify-center rounded-full border bg-background text-foreground shadow-sm transition-colors hover:bg-muted"
                            @click="
                                speakerCarousel.scroll(-1);
                                speakerCarousel.start();
                            "
                        >
                            <ChevronLeft class="size-5" />
                        </button>
                        <button
                            type="button"
                            aria-label="Berikutnya"
                            class="flex size-10 items-center justify-center rounded-full border bg-background text-foreground shadow-sm transition-colors hover:bg-muted"
                            @click="
                                speakerCarousel.scroll(1);
                                speakerCarousel.start();
                            "
                        >
                            <ChevronRight class="size-5" />
                        </button>
                    </div>
                </div>

                <div
                    ref="speakerTrack"
                    class="speaker-track -mx-6 flex snap-x snap-mandatory scroll-px-6 gap-5 overflow-x-auto px-6 pb-4"
                    @mouseenter="speakerCarousel.stop"
                    @mouseleave="speakerCarousel.start"
                >
                    <div
                        v-for="speaker in seminar.speakers"
                        :key="speaker.name"
                        data-speaker-card
                        class="w-64 shrink-0 snap-start overflow-hidden rounded-xl border bg-background shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg sm:w-72"
                    >
                        <div
                            class="aspect-square w-full overflow-hidden bg-gradient-to-br from-violet-100 to-sky-100 dark:from-violet-950/50 dark:to-sky-950/50"
                        >
                            <img
                                v-if="speaker.photo_path"
                                :src="`/storage/${speaker.photo_path}`"
                                :alt="speaker.name"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center text-5xl font-bold text-violet-400 dark:text-violet-500"
                            >
                                {{ getInitials(speaker.name) }}
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="font-semibold">{{ speaker.name }}</p>
                            <p class="text-sm text-muted-foreground">{{ speaker.title }}</p>
                            <span
                                v-if="speaker.topic"
                                class="mt-3 inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700 dark:bg-violet-950/50 dark:text-violet-300"
                            >
                                {{ speaker.topic }}
                            </span>
                        </div>
                    </div>

                    <div v-if="seminar.speakers.length === 0" class="py-8 text-sm text-muted-foreground">Belum ada narasumber diumumkan.</div>
                </div>
            </div>
        </section>

        <section id="informasi" class="mx-auto max-w-6xl px-6 py-16">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <span class="text-xs font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400">Informasi Kegiatan</span>
                <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Semua yang perlu Anda ketahui</h2>
                <p class="mt-2 text-sm text-muted-foreground">Ruang lingkup, biaya, tujuan publikasi, dan jadwal kegiatan.</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="border-sky-100 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-border">
                    <CardHeader class="flex flex-row items-center gap-3 space-y-0">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400"
                        >
                            <BookOpenText class="size-5" />
                        </span>
                        <CardTitle class="text-base">Ruang Lingkup Artikel</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-2 text-sm">
                            <li v-for="item in seminar.scope" :key="item" class="flex items-start gap-2">
                                <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-sky-500"></span>
                                <span class="text-muted-foreground">{{ item }}</span>
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <Card class="border-emerald-100 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-border">
                    <CardHeader class="flex flex-row items-center gap-3 space-y-0">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"
                        >
                            <Wallet class="size-5" />
                        </span>
                        <CardTitle class="text-base">Biaya Pendaftaran</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-1 text-sm">
                            <li
                                v-for="(fee, key) in seminar.fees"
                                :key="key"
                                class="flex items-center justify-between border-b border-dashed py-1.5 last:border-b-0"
                            >
                                <span class="text-muted-foreground">{{ feeLabels[key] ?? key }}</span>
                                <span class="font-semibold text-emerald-700 dark:text-emerald-400">{{ formatRupiah(fee) }}</span>
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <Card class="border-amber-100 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-border">
                    <CardHeader class="flex flex-row items-center gap-3 space-y-0">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400"
                        >
                            <Newspaper class="size-5" />
                        </span>
                        <CardTitle class="text-base">Jurnal &amp; Prosiding Tujuan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-3 text-sm">
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
                <CardHeader class="flex flex-row items-center gap-3 space-y-0">
                    <span
                        class="flex size-10 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400"
                    >
                        <CalendarClock class="size-5" />
                    </span>
                    <CardTitle class="text-base">Timeline Kegiatan</CardTitle>
                </CardHeader>
                <CardContent>
                    <ol class="relative ml-2 space-y-6 border-l-2 border-sky-100 pl-6 dark:border-border">
                        <li v-for="(item, index) in seminar.timeline" :key="item.label" class="relative">
                            <span
                                class="absolute -left-[1.94rem] top-0.5 flex size-4 items-center justify-center rounded-full border-2 border-sky-500 bg-background"
                            >
                                <span v-if="index === 0" class="size-1.5 rounded-full bg-sky-500"></span>
                            </span>
                            <div class="flex flex-col gap-0.5 sm:flex-row sm:items-center sm:justify-between">
                                <span class="text-sm font-medium">{{ item.label }}</span>
                                <span class="text-sm text-muted-foreground">{{ item.date }}</span>
                            </div>
                        </li>
                    </ol>
                </CardContent>
            </Card>
        </section>

        <section class="bg-muted/20 py-16">
            <div class="mx-auto max-w-6xl px-6">
                <div class="mx-auto mb-10 max-w-2xl text-center">
                    <span class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Kolaborasi</span>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Mitra &amp; Kerjasama</h2>
                    <p class="mt-2 text-sm text-muted-foreground">Didukung oleh institusi dan mitra strategis SNOS 2026.</p>
                </div>

                <div
                    ref="partnerTrack"
                    class="partner-track -mx-6 overflow-x-auto px-6 pb-2"
                    @mouseenter="partnerCarousel.stop"
                    @mouseleave="partnerCarousel.start"
                >
                    <div class="mx-auto flex w-max gap-5">
                        <div
                            v-for="partner in seminar.partners"
                            :key="partner.name"
                            data-partner-card
                            class="flex w-44 shrink-0 flex-col items-center gap-3 rounded-xl border bg-background px-6 py-8 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md sm:w-52"
                        >
                            <div class="flex h-14 w-full items-center justify-center">
                                <img
                                    v-if="partner.logo_path"
                                    :src="`/storage/${partner.logo_path}`"
                                    :alt="partner.name"
                                    class="max-h-14 max-w-full object-contain"
                                />
                                <div
                                    v-else
                                    class="flex size-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"
                                >
                                    <Handshake class="size-6" />
                                </div>
                            </div>
                            <p class="text-center text-sm font-medium text-foreground">{{ partner.name }}</p>
                        </div>

                        <div v-if="seminar.partners.length === 0" class="py-8 text-sm text-muted-foreground">Belum ada mitra diumumkan.</div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-white/5 bg-[#071229] text-slate-300">
            <div class="mx-auto max-w-6xl px-6 py-14">
                <div class="grid grid-cols-1 gap-10 md:grid-cols-3">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm">
                                <AppLogoIcon class="size-6 fill-current text-sky-700" />
                            </div>
                            <div>
                                <p class="font-semibold text-white">{{ seminar.name }}</p>
                                <p class="text-xs text-slate-400">{{ seminar.organizer }}</p>
                            </div>
                        </div>
                        <p class="mt-4 max-w-xs text-sm leading-relaxed text-slate-400">
                            Diselenggarakan oleh {{ seminar.organizer }} sebagai wadah publikasi dan diskusi ilmiah nasional.
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Jelajahi</p>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li>
                                <a href="#detail" class="text-slate-300 transition-colors hover:text-white">Narasumber</a>
                            </li>
                            <li>
                                <a href="#informasi" class="text-slate-300 transition-colors hover:text-white">Informasi Kegiatan</a>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Kontak Kami</p>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li class="flex items-start gap-2.5">
                                <Mail class="mt-0.5 size-4 shrink-0 text-sky-400" />
                                <a :href="`mailto:${seminar.contact.email}`" class="text-slate-300 transition-colors hover:text-white">{{
                                    seminar.contact.email
                                }}</a>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <Phone class="mt-0.5 size-4 shrink-0 text-sky-400" />
                                <a
                                    :href="`tel:${seminar.contact.phone.replace(/[^+\d]/g, '')}`"
                                    class="text-slate-300 transition-colors hover:text-white"
                                    >{{ seminar.contact.phone }}</a
                                >
                            </li>
                            <li class="flex items-start gap-2.5">
                                <Facebook class="mt-0.5 size-4 shrink-0 text-sky-400" />
                                <a
                                    :href="seminar.contact.facebook"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-slate-300 transition-colors hover:text-white"
                                    >Facebook</a
                                >
                            </li>
                            <li class="flex items-start gap-2.5">
                                <Instagram class="mt-0.5 size-4 shrink-0 text-sky-400" />
                                <a
                                    :href="seminar.contact.instagram"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-slate-300 transition-colors hover:text-white"
                                    >Instagram</a
                                >
                            </li>
                            <li class="flex items-start gap-2.5">
                                <MapPin class="mt-0.5 size-4 shrink-0 text-sky-400" />
                                <span class="text-slate-300">{{ seminar.contact.address }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-white/10 pt-6 text-xs text-slate-500 sm:flex-row">
                    <p>&copy; {{ new Date().getFullYear() }} {{ seminar.name }}. Seluruh hak cipta dilindungi.</p>
                    <p>Diselenggarakan oleh {{ seminar.organizer }}</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.speaker-track,
.partner-track {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.speaker-track::-webkit-scrollbar,
.partner-track::-webkit-scrollbar {
    display: none;
}
</style>
