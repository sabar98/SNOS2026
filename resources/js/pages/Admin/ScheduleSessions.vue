<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';

interface Slot {
    id: number;
    order: number;
    article: { title: string; event_registration: { user: { name: string } } };
}

interface Session {
    id: number;
    session_number: string;
    room: string | null;
    date: string;
    start_time: string;
    end_time: string;
    moderator: { name: string } | null;
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

defineProps<{
    sessions: Session[];
    moderators: Moderator[];
    acceptedArticles: AcceptedArticle[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Jadwal Sesi', href: '/admin/schedule-sessions' }];
const selectClass = 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm';

const form = useForm({
    session_number: '',
    room: '',
    moderator_id: '',
    date: '',
    start_time: '',
    end_time: '',
    zoom_link: '',
    zoom_meeting_id: '',
    zoom_password: '',
});

function createSession() {
    form.post(route('admin.schedule-sessions.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

const slotSelections = reactive<Record<number, string>>({});

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function addSlot(sessionId: number) {
    router.post(route('admin.slots.store', sessionId), { article_id: slotSelections[sessionId], order: 1 }, { preserveScroll: true });
}
</script>

<template>
    <Head title="Jadwal Sesi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
            <Card>
                <CardHeader><CardTitle>Buat Sesi Baru</CardTitle></CardHeader>
                <CardContent>
                    <form class="grid grid-cols-2 gap-3" @submit.prevent="createSession">
                        <div
                            v-if="Object.keys(form.errors).length > 0"
                            class="col-span-2 rounded-md border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive"
                        >
                            Sesi gagal disimpan. Periksa kembali isian di bawah ini.
                        </div>
                        <div>
                            <Label for="session_number">Nomor Sesi</Label>
                            <Input id="session_number" v-model="form.session_number" required />
                            <p v-if="form.errors.session_number" class="text-sm text-destructive">{{ form.errors.session_number }}</p>
                        </div>
                        <div>
                            <Label for="session_room">Ruangan</Label>
                            <Input id="session_room" v-model="form.room" />
                            <p v-if="form.errors.room" class="text-sm text-destructive">{{ form.errors.room }}</p>
                        </div>
                        <div>
                            <Label for="session_moderator">Moderator</Label>
                            <select id="session_moderator" v-model="form.moderator_id" :class="selectClass">
                                <option value="">Belum ditentukan</option>
                                <option v-for="moderator in moderators" :key="moderator.id" :value="moderator.id">{{ moderator.name }}</option>
                            </select>
                            <p v-if="form.errors.moderator_id" class="text-sm text-destructive">{{ form.errors.moderator_id }}</p>
                        </div>
                        <div>
                            <Label for="session_date">Tanggal</Label>
                            <Input id="session_date" v-model="form.date" type="date" required />
                            <p v-if="form.errors.date" class="text-sm text-destructive">{{ form.errors.date }}</p>
                        </div>
                        <div>
                            <Label for="session_start_time">Mulai</Label>
                            <Input id="session_start_time" v-model="form.start_time" type="time" required />
                            <p v-if="form.errors.start_time" class="text-sm text-destructive">{{ form.errors.start_time }}</p>
                        </div>
                        <div>
                            <Label for="session_end_time">Selesai</Label>
                            <Input id="session_end_time" v-model="form.end_time" type="time" required />
                            <p v-if="form.errors.end_time" class="text-sm text-destructive">{{ form.errors.end_time }}</p>
                        </div>
                        <div class="col-span-2">
                            <Label for="session_zoom_link">Link Zoom</Label>
                            <Input id="session_zoom_link" v-model="form.zoom_link" />
                            <p v-if="form.errors.zoom_link" class="text-sm text-destructive">{{ form.errors.zoom_link }}</p>
                        </div>
                        <div>
                            <Label for="session_zoom_meeting_id">Meeting ID</Label>
                            <Input id="session_zoom_meeting_id" v-model="form.zoom_meeting_id" />
                            <p v-if="form.errors.zoom_meeting_id" class="text-sm text-destructive">{{ form.errors.zoom_meeting_id }}</p>
                        </div>
                        <div>
                            <Label for="session_zoom_password">Password Zoom</Label>
                            <Input id="session_zoom_password" v-model="form.zoom_password" />
                            <p v-if="form.errors.zoom_password" class="text-sm text-destructive">{{ form.errors.zoom_password }}</p>
                        </div>
                        <Button type="submit" class="col-span-2" :disabled="form.processing">Simpan Sesi</Button>
                    </form>
                </CardContent>
            </Card>

            <Card v-for="session in sessions" :key="session.id" class="border-sky-200 dark:border-sky-900">
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle class="text-base text-sky-800 dark:text-sky-300">
                        Sesi {{ session.session_number }} &mdash; {{ formatDate(session.date) }} {{ session.start_time }}-{{ session.end_time }}
                    </CardTitle>
                    <Badge variant="info">{{ session.presentation_slots.length }} presentasi</Badge>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <p>Ruangan: {{ session.room ?? '-' }} &middot; Moderator: {{ session.moderator?.name ?? '-' }}</p>
                    <ul class="space-y-1">
                        <li v-for="slot in session.presentation_slots" :key="slot.id">
                            #{{ slot.order }} {{ slot.article.title }} ({{ slot.article.event_registration.user.name }})
                        </li>
                    </ul>

                    <form class="flex items-center gap-2 pt-2" @submit.prevent="addSlot(session.id)">
                        <select v-model="slotSelections[session.id]" aria-label="Pilih artikel diterima" :class="selectClass">
                            <option value="" disabled>Pilih artikel diterima</option>
                            <option v-for="article in acceptedArticles" :key="article.id" :value="article.id">
                                {{ article.title }} &mdash; {{ article.event_registration.user.name }}
                            </option>
                        </select>
                        <Button type="submit" size="sm">Tambahkan ke Sesi</Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
