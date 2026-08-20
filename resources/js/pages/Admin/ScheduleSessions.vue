<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarClock, CalendarPlus, ListVideo, Pencil, Presentation, Search, Trash2, Users } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Slot {
    id: number;
    order: number;
    article: { id: number; title: string; event_registration: { user: { name: string } } };
}

interface Session {
    id: number;
    session_number: string;
    room: string | null;
    date: string;
    start_time: string;
    end_time: string;
    zoom_link: string | null;
    zoom_meeting_id: string | null;
    zoom_password: string | null;
    moderator: { id: number; name: string } | null;
    presentation_slots: Slot[];
}

interface Moderator {
    id: number;
    name: string;
}

interface AcceptedArticle {
    id: number;
    title: string;
    event_registration: { user: { name: string } };
}

const props = defineProps<{
    sessions: Session[];
    moderators: Moderator[];
    acceptedArticles: AcceptedArticle[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Jadwal Sesi', href: '/admin/schedule-sessions' }];

const search = ref('');

const filteredSessions = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return props.sessions;
    return props.sessions.filter(
        (session) =>
            session.session_number.toLowerCase().includes(query) ||
            (session.room?.toLowerCase().includes(query) ?? false) ||
            (session.moderator?.name.toLowerCase().includes(query) ?? false),
    );
});

const totalSlots = computed(() => props.sessions.reduce((sum, session) => sum + session.presentation_slots.length, 0));

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

const dialogOpen = ref(false);
const dialogMode = ref<'create' | 'edit'>('create');
const activeSession = ref<Session | null>(null);

// The sessions prop is replaced wholesale after every Inertia reload (e.g. adding/removing
// a slot while the edit dialog is open), so re-point activeSession at the fresh copy.
watch(
    () => props.sessions,
    (sessions) => {
        if (activeSession.value) {
            activeSession.value = sessions.find((session) => session.id === activeSession.value!.id) ?? null;
        }
    },
);

const dialogForm = useForm({
    session_number: '',
    room: '',
    moderator_id: '' as number | string,
    date: '',
    start_time: '',
    end_time: '',
    zoom_link: '',
    zoom_meeting_id: '',
    zoom_password: '',
});

function openCreateDialog() {
    dialogMode.value = 'create';
    activeSession.value = null;
    dialogForm.reset();
    dialogForm.clearErrors();
    dialogOpen.value = true;
}

function openEditDialog(session: Session) {
    dialogMode.value = 'edit';
    activeSession.value = session;
    dialogForm.clearErrors();
    dialogForm.session_number = session.session_number;
    dialogForm.room = session.room ?? '';
    dialogForm.moderator_id = session.moderator?.id ?? '';
    dialogForm.date = session.date.slice(0, 10);
    dialogForm.start_time = session.start_time;
    dialogForm.end_time = session.end_time;
    dialogForm.zoom_link = session.zoom_link ?? '';
    dialogForm.zoom_meeting_id = session.zoom_meeting_id ?? '';
    dialogForm.zoom_password = session.zoom_password ?? '';
    dialogOpen.value = true;
}

function submitDialog() {
    if (dialogMode.value === 'create') {
        dialogForm.post(route('admin.schedule-sessions.store'), {
            preserveScroll: true,
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    } else if (activeSession.value) {
        dialogForm.put(route('admin.schedule-sessions.update', activeSession.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    }
}

function destroySession(session: Session) {
    if (!confirm(`Hapus sesi "${session.session_number}"? Semua presentasi terjadwal di sesi ini juga akan terhapus.`)) {
        return;
    }
    router.delete(route('admin.schedule-sessions.destroy', session.id), { preserveScroll: true });
}

const slotForm = useForm({
    article_id: '',
    order: 1,
});

function addSlot() {
    if (!activeSession.value) return;
    slotForm.order = activeSession.value.presentation_slots.length + 1;
    slotForm.post(route('admin.slots.store', activeSession.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            slotForm.reset();
        },
    });
}

function removeSlot(slot: Slot) {
    if (!confirm(`Hapus "${slot.article.title}" dari sesi ini?`)) {
        return;
    }
    router.delete(route('admin.slots.destroy', slot.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Jadwal Sesi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="CalendarClock"
                icon-class="bg-cyan-100 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-400"
                title="Jadwal Sesi"
                description="Susun sesi presentasi dan atur moderatornya."
            >
                <template #actions>
                    <Button class="gap-2" @click="openCreateDialog"><CalendarPlus class="size-4" /> Buat Sesi Baru</Button>
                </template>
            </PageHeader>

            <div class="grid gap-4 sm:grid-cols-3">
                <Card class="border-cyan-100 bg-cyan-50 dark:border-border dark:bg-cyan-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-400"
                        >
                            <CalendarClock class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ sessions.length }}</p>
                            <p class="text-sm text-muted-foreground">Total Sesi</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"
                        >
                            <Presentation class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ totalSlots }}</p>
                            <p class="text-sm text-muted-foreground">Presentasi Terjadwal</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400"
                        >
                            <ListVideo class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ acceptedArticles.length }}</p>
                            <p class="text-sm text-muted-foreground">Artikel Menunggu Jadwal</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <EmptyState
                v-if="sessions.length === 0"
                :icon="CalendarClock"
                title="Belum ada sesi terjadwal"
                description="Buat sesi baru menggunakan tombol di atas."
            />

            <Card v-else class="border-cyan-100 bg-cyan-50 dark:border-border dark:bg-cyan-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <CardTitle class="flex items-center gap-2"><CalendarClock class="size-4 text-muted-foreground" /> Daftar Sesi</CardTitle>
                    <div class="relative w-full max-w-xs">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" placeholder="Cari nomor sesi, ruangan, atau moderator..." class="pl-9" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[820px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="w-10 py-2">No</th>
                                    <th>Sesi</th>
                                    <th>Jadwal</th>
                                    <th>Ruangan</th>
                                    <th>Moderator</th>
                                    <th>Presentasi</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(session, index) in filteredSessions"
                                    :key="session.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-3 text-muted-foreground">{{ index + 1 }}</td>
                                    <td class="font-medium">Sesi {{ session.session_number }}</td>
                                    <td class="text-muted-foreground">
                                        {{ formatDate(session.date) }}<br />
                                        <span class="text-xs">{{ session.start_time }}&ndash;{{ session.end_time }}</span>
                                    </td>
                                    <td class="text-muted-foreground">{{ session.room ?? '—' }}</td>
                                    <td class="text-muted-foreground">{{ session.moderator?.name ?? '—' }}</td>
                                    <td>
                                        <Badge variant="info">{{ session.presentation_slots.length }}</Badge>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="outline" size="sm" class="gap-1.5" @click="openEditDialog(session)">
                                                <Pencil class="size-3.5" /> Edit
                                            </Button>
                                            <Button variant="destructive" size="sm" class="gap-1.5" @click="destroySession(session)">
                                                <Trash2 class="size-3.5" /> Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredSessions.length === 0">
                                    <td colspan="7" class="py-10 text-center text-muted-foreground">Tidak ada sesi yang cocok dengan pencarian.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent class="max-h-[85vh] overflow-y-auto sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle>{{ dialogMode === 'create' ? 'Buat Sesi Baru' : 'Ubah Sesi' }}</DialogTitle>
                    <DialogDescription>
                        {{ dialogMode === 'create' ? 'Susun sesi presentasi baru untuk SNOS 2026.' : `Sesi ${activeSession?.session_number}` }}
                    </DialogDescription>
                </DialogHeader>

                <form class="grid gap-4" @submit.prevent="submitDialog">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="session_number">Nomor Sesi</Label>
                            <Input id="session_number" v-model="dialogForm.session_number" required />
                            <p v-if="dialogForm.errors.session_number" class="text-sm text-destructive">{{ dialogForm.errors.session_number }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="session_room">Ruangan</Label>
                            <Input id="session_room" v-model="dialogForm.room" />
                            <p v-if="dialogForm.errors.room" class="text-sm text-destructive">{{ dialogForm.errors.room }}</p>
                        </div>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="session_moderator">Moderator</Label>
                        <select
                            id="session_moderator"
                            v-model="dialogForm.moderator_id"
                            class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="">Belum ditentukan</option>
                            <option v-for="moderator in moderators" :key="moderator.id" :value="moderator.id">{{ moderator.name }}</option>
                        </select>
                        <p v-if="dialogForm.errors.moderator_id" class="text-sm text-destructive">{{ dialogForm.errors.moderator_id }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="grid gap-1.5">
                            <Label for="session_date">Tanggal</Label>
                            <Input id="session_date" v-model="dialogForm.date" type="date" required />
                            <p v-if="dialogForm.errors.date" class="text-sm text-destructive">{{ dialogForm.errors.date }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="session_start_time">Mulai</Label>
                            <Input id="session_start_time" v-model="dialogForm.start_time" type="time" required />
                            <p v-if="dialogForm.errors.start_time" class="text-sm text-destructive">{{ dialogForm.errors.start_time }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="session_end_time">Selesai</Label>
                            <Input id="session_end_time" v-model="dialogForm.end_time" type="time" required />
                            <p v-if="dialogForm.errors.end_time" class="text-sm text-destructive">{{ dialogForm.errors.end_time }}</p>
                        </div>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="session_zoom_link">Link Zoom</Label>
                        <Input id="session_zoom_link" v-model="dialogForm.zoom_link" placeholder="https://" />
                        <p v-if="dialogForm.errors.zoom_link" class="text-sm text-destructive">{{ dialogForm.errors.zoom_link }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="session_zoom_meeting_id">Meeting ID</Label>
                            <Input id="session_zoom_meeting_id" v-model="dialogForm.zoom_meeting_id" />
                            <p v-if="dialogForm.errors.zoom_meeting_id" class="text-sm text-destructive">{{ dialogForm.errors.zoom_meeting_id }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="session_zoom_password">Password Zoom</Label>
                            <Input id="session_zoom_password" v-model="dialogForm.zoom_password" />
                            <p v-if="dialogForm.errors.zoom_password" class="text-sm text-destructive">{{ dialogForm.errors.zoom_password }}</p>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="submit" :disabled="dialogForm.processing">
                            {{ dialogMode === 'create' ? 'Simpan Sesi' : 'Simpan Perubahan' }}
                        </Button>
                    </DialogFooter>
                </form>

                <div v-if="dialogMode === 'edit' && activeSession" class="mt-2 border-t pt-4">
                    <p class="mb-3 flex items-center gap-2 text-sm font-semibold">
                        <Users class="size-4 text-muted-foreground" /> Presentasi dalam Sesi Ini
                    </p>

                    <ul v-if="activeSession.presentation_slots.length > 0" class="mb-3 space-y-2">
                        <li
                            v-for="slot in activeSession.presentation_slots"
                            :key="slot.id"
                            class="flex items-center justify-between gap-3 rounded-md border p-2.5 text-sm"
                        >
                            <span class="truncate"
                                >#{{ slot.order }} {{ slot.article.title }}
                                <span class="text-muted-foreground">({{ slot.article.event_registration.user.name }})</span></span
                            >
                            <Button variant="ghost" size="icon" class="size-7 shrink-0 text-destructive" @click="removeSlot(slot)">
                                <Trash2 class="size-3.5" />
                            </Button>
                        </li>
                    </ul>
                    <p v-else class="mb-3 text-sm text-muted-foreground">Belum ada artikel dijadwalkan di sesi ini.</p>

                    <form class="flex flex-col gap-2 sm:flex-row sm:items-center" @submit.prevent="addSlot">
                        <select
                            v-model="slotForm.article_id"
                            aria-label="Pilih artikel diterima"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="" disabled>Pilih artikel diterima</option>
                            <option v-for="article in acceptedArticles" :key="article.id" :value="article.id">
                                {{ article.title }} &mdash; {{ article.event_registration.user.name }}
                            </option>
                        </select>
                        <Button type="submit" size="sm" class="w-fit shrink-0" :disabled="slotForm.processing || !slotForm.article_id">
                            Tambahkan
                        </Button>
                    </form>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
