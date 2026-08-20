<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { slotStatusLabels, slotStatusVariants } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarClock } from 'lucide-vue-next';
import { reactive } from 'vue';

interface Slot {
    id: number;
    order: number;
    status: string;
    execution_notes: string | null;
    article: { title: string; event_registration: { user: { name: string } } };
    assessments: { id: number }[];
}

interface Session {
    id: number;
    session_number: string;
    date: string;
    presentation_slots: Slot[];
}

const props = defineProps<{
    sessions: Session[];
}>();

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Sesi Saya', href: '/moderator/sessions' }];

const executionNotes = reactive<Record<number, string>>({});

for (const session of props.sessions) {
    for (const slot of session.presentation_slots) {
        executionNotes[slot.id] = slot.execution_notes ?? '';
    }
}

function setStatus(slot: Slot, status: string) {
    router.put(route('moderator.slots.attendance', slot.id), { status, execution_notes: executionNotes[slot.id] ?? '' }, { preserveScroll: true });
}

function saveNotes(slot: Slot) {
    router.put(
        route('moderator.slots.attendance', slot.id),
        { status: slot.status, execution_notes: executionNotes[slot.id] ?? '' },
        { preserveScroll: true },
    );
}

const assessmentForms: Record<number, ReturnType<typeof useForm>> = {};

function assessmentFor(slotId: number) {
    if (!assessmentForms[slotId]) {
        assessmentForms[slotId] = useForm({
            mastery_score: 5,
            presentation_quality_score: 5,
            timeliness_score: 5,
            qa_score: 5,
            content_alignment_score: 5,
            notes: '',
        });
    }
    return assessmentForms[slotId];
}

function submitAssessment(slotId: number) {
    assessmentFor(slotId).post(route('moderator.slots.assessment', slotId), { preserveScroll: true });
}
</script>

<template>
    <Head title="Sesi Saya" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
            <PageHeader
                :icon="CalendarClock"
                title="Sesi Saya"
                description="Kelola kehadiran dan penilaian presentasi pada sesi yang Anda moderatori."
            />

            <EmptyState
                v-if="sessions.length === 0"
                :icon="CalendarClock"
                title="Belum ada sesi ditugaskan"
                description="Anda belum ditugaskan sebagai moderator sesi manapun."
            />

            <Card v-for="session in sessions" :key="session.id">
                <CardHeader>
                    <CardTitle class="text-base">Sesi {{ session.session_number }} &mdash; {{ formatDate(session.date) }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div v-for="slot in session.presentation_slots" :key="slot.id" class="rounded-md border p-3 text-sm">
                        <div class="flex items-center justify-between">
                            <p class="font-medium">#{{ slot.order }} {{ slot.article.title }}</p>
                            <Badge :variant="slotStatusVariants[slot.status] ?? 'secondary'">
                                {{ slotStatusLabels[slot.status] ?? slot.status }}
                            </Badge>
                        </div>
                        <p class="text-muted-foreground">{{ slot.article.event_registration.user.name }}</p>

                        <div class="mt-2 flex gap-2">
                            <Button size="sm" variant="outline" @click="setStatus(slot, 'hadir')">Hadir</Button>
                            <Button size="sm" variant="outline" @click="setStatus(slot, 'tidak_hadir')">Tidak Hadir</Button>
                            <Button size="sm" variant="outline" @click="setStatus(slot, 'selesai')">Selesai</Button>
                        </div>

                        <div class="mt-3 grid gap-2 border-t pt-3">
                            <label :for="`execution_notes_${slot.id}`" class="text-xs font-medium text-muted-foreground">
                                Catatan Pelaksanaan / Berita Acara
                            </label>
                            <textarea
                                :id="`execution_notes_${slot.id}`"
                                v-model="executionNotes[slot.id]"
                                placeholder="Tulis catatan pelaksanaan sesi ini..."
                                rows="2"
                                class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                            ></textarea>
                            <Button size="sm" variant="outline" class="w-fit" @click="saveNotes(slot)">Simpan Catatan</Button>
                        </div>

                        <form v-if="slot.assessments.length === 0" class="mt-3 grid gap-2 border-t pt-3" @submit.prevent="submitAssessment(slot.id)">
                            <label class="flex items-center justify-between">
                                Penguasaan Materi
                                <input
                                    v-model.number="assessmentFor(slot.id).mastery_score"
                                    type="number"
                                    min="1"
                                    max="5"
                                    class="w-16 rounded border px-2"
                                />
                            </label>
                            <label class="flex items-center justify-between">
                                Kualitas Presentasi
                                <input
                                    v-model.number="assessmentFor(slot.id).presentation_quality_score"
                                    type="number"
                                    min="1"
                                    max="5"
                                    class="w-16 rounded border px-2"
                                />
                            </label>
                            <label class="flex items-center justify-between">
                                Ketepatan Waktu
                                <input
                                    v-model.number="assessmentFor(slot.id).timeliness_score"
                                    type="number"
                                    min="1"
                                    max="5"
                                    class="w-16 rounded border px-2"
                                />
                            </label>
                            <label class="flex items-center justify-between">
                                Kemampuan Menjawab
                                <input
                                    v-model.number="assessmentFor(slot.id).qa_score"
                                    type="number"
                                    min="1"
                                    max="5"
                                    class="w-16 rounded border px-2"
                                />
                            </label>
                            <label class="flex items-center justify-between">
                                Kesesuaian Materi
                                <input
                                    v-model.number="assessmentFor(slot.id).content_alignment_score"
                                    type="number"
                                    min="1"
                                    max="5"
                                    class="w-16 rounded border px-2"
                                />
                            </label>
                            <Button type="submit" size="sm">Simpan Penilaian</Button>
                        </form>
                        <p v-else class="mt-2 text-xs text-muted-foreground">Penilaian sudah tersimpan.</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
