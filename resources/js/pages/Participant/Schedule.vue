<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { registrationStatusLabels, registrationStatusVariants, slotStatusLabels, slotStatusVariants } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { CalendarClock, Video } from 'lucide-vue-next';
import { computed } from 'vue';

interface Slot {
    id: number;
    order: number;
    status: string;
    schedule_session: {
        session_number: string;
        room: string | null;
        date: string;
        start_time: string;
        end_time: string;
        zoom_link: string | null;
        zoom_meeting_id: string | null;
        zoom_password: string | null;
    };
    article: {
        title: string;
    };
}

interface Ticket {
    registration_number: string;
    status: string;
    qr_svg: string;
}

interface Session {
    id: number;
    session_number: string;
    room: string | null;
    date: string;
    start_time: string;
    end_time: string;
    zoom_link: string | null;
    moderator: { name: string } | null;
}

const props = defineProps<{
    slots: Slot[];
    tickets: Ticket[];
    sessions: Session[];
    attendedSessionIds: number[];
    attendanceMethod: string | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Jadwal Saya', href: '/participant/schedule' }];

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

const checkInForm = useForm({ registration_number: '' });

function checkIn() {
    checkInForm.post(route('participant.check-in'), { preserveScroll: true });
}

const attendanceForm = useForm({});

function markAttendance(sessionId: number) {
    attendanceForm.post(route('participant.sessions.attendance', sessionId), { preserveScroll: true });
}

const nextZoomSession = computed(() => props.sessions.find((session) => session.zoom_link) ?? null);
</script>

<template>
    <Head title="Jadwal Saya" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <PageHeader :icon="CalendarClock" title="Jadwal Saya" description="Tiket digital, agenda, dan jadwal presentasi Anda." />

            <Card v-if="attendanceMethod === 'daring'" class="border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-emerald-800 dark:text-emerald-300">
                        <Video class="size-4" /> Anda Terdaftar Daring
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 text-sm">
                    <p class="text-emerald-800/80 dark:text-emerald-300/80">
                        Ikuti kegiatan dari mana saja lewat Zoom. Buka link Zoom sesi Anda di bawah, lalu isi absensi daring setelah sesi dimulai.
                    </p>
                    <a
                        v-if="nextZoomSession"
                        :href="nextZoomSession.zoom_link ?? '#'"
                        target="_blank"
                        class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700"
                    >
                        <Video class="size-4" /> Buka Zoom &mdash; Sesi {{ nextZoomSession.session_number }}
                    </a>
                    <p v-else class="text-emerald-800/80 dark:text-emerald-300/80">Link Zoom akan muncul di sini setelah jadwal sesi ditetapkan.</p>
                </CardContent>
            </Card>

            <Card v-for="ticket in tickets" :key="ticket.registration_number" class="border-sky-200 bg-sky-50 dark:border-sky-900 dark:bg-sky-950/40">
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle class="text-sky-800 dark:text-sky-300">{{
                        attendanceMethod === 'daring' ? 'Nomor Registrasi' : 'Tiket Digital'
                    }}</CardTitle>
                    <Badge :variant="registrationStatusVariants[ticket.status] ?? 'secondary'">
                        {{ registrationStatusLabels[ticket.status] ?? ticket.status }}
                    </Badge>
                </CardHeader>
                <CardContent class="flex flex-col items-center gap-3">
                    <div v-if="attendanceMethod !== 'daring'" class="w-40 rounded-lg bg-white p-3 shadow-sm" v-html="ticket.qr_svg" />
                    <p class="font-mono text-sm">{{ ticket.registration_number }}</p>
                    <p class="text-center text-xs text-muted-foreground">
                        {{
                            attendanceMethod === 'daring'
                                ? 'Gunakan nomor ini untuk konfirmasi kehadiran daring di bawah.'
                                : 'Tunjukkan kode ini kepada panitia untuk registrasi ulang di lokasi kegiatan.'
                        }}
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base">
                        {{ attendanceMethod === 'daring' ? 'Konfirmasi Kehadiran Daring' : 'Registrasi Ulang / Check-in' }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-wrap gap-2" @submit.prevent="checkIn">
                        <input
                            v-model="checkInForm.registration_number"
                            type="text"
                            placeholder="Nomor registrasi"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        />
                        <Button type="submit" :disabled="checkInForm.processing">Check-in</Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Agenda Kegiatan</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 text-sm">
                    <div v-for="session in sessions" :key="session.id" class="flex items-center justify-between border-b pb-2 last:border-0">
                        <div>
                            <p class="font-medium">Sesi {{ session.session_number }}</p>
                            <p class="text-muted-foreground">
                                {{ formatDate(session.date) }} &middot; {{ session.start_time }}-{{ session.end_time }}
                                <span v-if="session.room">&middot; {{ session.room }}</span>
                                <span v-if="session.moderator">&middot; Moderator: {{ session.moderator.name }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a
                                v-if="attendanceMethod === 'daring' && session.zoom_link"
                                :href="session.zoom_link"
                                target="_blank"
                                class="inline-flex items-center gap-1 rounded-md border border-emerald-300 px-2.5 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-300 dark:hover:bg-emerald-950/40"
                            >
                                <Video class="size-3.5" /> Buka Zoom
                            </a>
                            <Badge v-if="attendedSessionIds.includes(session.id)" variant="success">Hadir</Badge>
                            <Button v-else size="sm" variant="outline" :disabled="attendanceForm.processing" @click="markAttendance(session.id)">
                                Tandai Hadir
                            </Button>
                        </div>
                    </div>
                    <p v-if="sessions.length === 0" class="text-muted-foreground">Agenda sesi belum ditetapkan panitia.</p>
                </CardContent>
            </Card>

            <EmptyState
                v-if="slots.length === 0"
                :icon="CalendarClock"
                title="Belum ada jadwal presentasi"
                description="Jadwal presentasi Anda akan tampil di sini setelah ditetapkan panitia."
            />

            <Card v-for="slot in slots" :key="slot.id" class="transition-shadow duration-200 hover:shadow-md">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-2 space-y-0">
                    <CardTitle class="text-base">{{ slot.article.title }}</CardTitle>
                    <Badge :variant="slotStatusVariants[slot.status] ?? 'secondary'">
                        {{ slotStatusLabels[slot.status] ?? slot.status }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-1 text-sm">
                    <p>Sesi: {{ slot.schedule_session.session_number }} &middot; Urutan {{ slot.order }}</p>
                    <p>
                        {{ formatDate(slot.schedule_session.date) }} &middot; {{ slot.schedule_session.start_time }}-{{
                            slot.schedule_session.end_time
                        }}
                    </p>
                    <p v-if="slot.schedule_session.room">Ruang: {{ slot.schedule_session.room }}</p>
                    <p v-if="slot.schedule_session.zoom_link">
                        Zoom:
                        <a :href="slot.schedule_session.zoom_link" target="_blank" class="text-primary underline">{{
                            slot.schedule_session.zoom_link
                        }}</a>
                    </p>
                    <p v-if="slot.schedule_session.zoom_meeting_id">Meeting ID: {{ slot.schedule_session.zoom_meeting_id }}</p>
                    <p v-if="slot.schedule_session.zoom_password">Password: {{ slot.schedule_session.zoom_password }}</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
