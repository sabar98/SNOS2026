<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { BadgeCheck, CalendarCheck, FileText } from 'lucide-vue-next';

const page = usePage();
const name = page.props.name as string;

defineProps<{
    title?: string;
    description?: string;
}>();

const highlights = [
    { icon: FileText, text: 'Submit &amp; review artikel ilmiah secara daring' },
    { icon: CalendarCheck, text: 'Jadwal sesi &amp; presentasi yang terorganisir' },
    { icon: BadgeCheck, text: 'Sertifikat digital terverifikasi dengan QR code' },
];
</script>

<template>
    <div class="relative grid min-h-svh flex-col lg:grid-cols-2">
        <div class="brand-hero-panel relative hidden h-full flex-col justify-between p-10 text-white lg:flex">
            <Link :href="route('home')" class="relative z-20 flex items-center gap-3 text-lg font-semibold">
                <span class="flex size-10 items-center justify-center rounded-lg bg-white shadow-sm">
                    <AppLogoIcon class="size-6 fill-current text-sky-700" />
                </span>
                {{ name }}
            </Link>

            <div class="relative z-20 max-w-md">
                <h2 class="text-3xl font-bold leading-tight">Seminar Nasional Optimasi Sistem 2026</h2>
                <p class="mt-3 text-sky-50/90">
                    Transformasi Digital dan Inovasi Berkelanjutan &mdash; satu platform untuk pendaftaran, publikasi artikel, hingga sertifikasi
                    peserta.
                </p>

                <ul class="mt-8 space-y-4">
                    <li v-for="item in highlights" :key="item.text" class="flex items-center gap-3">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-white/15">
                            <component :is="item.icon" class="size-4" />
                        </span>
                        <span class="text-sm text-sky-50/90" v-html="item.text" />
                    </li>
                </ul>
            </div>

            <p class="relative z-20 text-xs text-sky-100/70">&copy; {{ new Date().getFullYear() }} {{ name }}</p>
        </div>

        <div class="flex flex-col justify-center bg-background px-6 py-12 sm:px-10 lg:px-16">
            <Link :href="route('home')" class="mb-8 flex items-center gap-2 font-semibold lg:hidden">
                <span class="flex size-9 items-center justify-center rounded-lg bg-gradient-to-br from-sky-500 to-blue-700 shadow-sm">
                    <AppLogoIcon class="size-5 fill-current text-white" />
                </span>
                {{ name }}
            </Link>

            <div class="mx-auto w-full max-w-md space-y-6">
                <div class="space-y-2" v-if="title || description">
                    <h1 class="text-2xl font-semibold tracking-tight" v-if="title">{{ title }}</h1>
                    <p class="text-sm text-muted-foreground" v-if="description">{{ description }}</p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
