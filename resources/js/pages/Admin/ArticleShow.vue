<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { articleStatusLabels, articleStatusVariants, reviewerAssignmentStatusLabels, reviewerAssignmentStatusVariants } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';

interface ReviewerAssignment {
    id: number;
    status: string;
    reviewer: { name: string };
}

interface Review {
    id: number;
    recommendation: string;
}

interface PresentationMaterial {
    slide_path: string | null;
    video_path: string | null;
    short_bio: string | null;
    official_photo_path: string | null;
    consent_confirmed_at: string | null;
}

interface Article {
    id: number;
    title: string;
    abstract: string;
    status: string;
    similarity_score: string | null;
    admin_notes: string | null;
    event_registration: { user: { name: string; email: string } };
    authors: { name: string; email: string }[];
    reviewer_assignments: ReviewerAssignment[];
    reviews: Review[];
    letter_of_acceptance: { loa_number: string; file_path: string | null } | null;
    presentation_material: PresentationMaterial | null;
}

const props = defineProps<{
    article: Article;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: props.article.title, href: '#' }];

const selectClass = 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm';

const decisionForm = useForm({
    decision: 'proses_review',
    similarity_score: props.article.similarity_score ?? '',
    admin_notes: props.article.admin_notes ?? '',
});

function submitDecision() {
    decisionForm.put(route('admin.articles.update', props.article.id), { preserveScroll: true });
}

function issueLoa() {
    router.post(route('admin.articles.loa', props.article.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="article.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
            <Card class="border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/40">
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle class="text-amber-800 dark:text-amber-300">{{ article.title }}</CardTitle>
                    <Badge :variant="articleStatusVariants[article.status] ?? 'secondary'">
                        {{ articleStatusLabels[article.status] ?? article.status }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-1 text-sm">
                    <p>Pengaju: {{ article.event_registration.user.name }} ({{ article.event_registration.user.email }})</p>
                    <p class="text-muted-foreground">{{ article.abstract }}</p>
                    <p v-if="article.letter_of_acceptance">
                        LoA: <span class="font-mono">{{ article.letter_of_acceptance.loa_number }}</span>
                        <a
                            v-if="article.letter_of_acceptance.file_path"
                            :href="`/storage/${article.letter_of_acceptance.file_path}`"
                            target="_blank"
                            class="ml-2 text-primary underline"
                        >
                            Unduh LoA
                        </a>
                    </p>
                    <Button v-else size="sm" @click="issueLoa">Terbitkan Letter of Acceptance</Button>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle class="text-base">Pemeriksaan Administrasi</CardTitle></CardHeader>
                <CardContent>
                    <form class="grid gap-3" @submit.prevent="submitDecision">
                        <Label for="decision">Keputusan</Label>
                        <select id="decision" v-model="decisionForm.decision" :class="selectClass">
                            <option value="proses_review">Diterima untuk direview</option>
                            <option value="perlu_perbaikan_administrasi">Perlu perbaikan administrasi</option>
                            <option value="ditolak_administrasi">Ditolak</option>
                        </select>
                        <Label for="similarity_score">Skor Similarity (%)</Label>
                        <Input id="similarity_score" v-model="decisionForm.similarity_score" type="number" min="0" max="100" />
                        <Label for="admin_notes">Catatan Admin</Label>
                        <textarea id="admin_notes" v-model="decisionForm.admin_notes" rows="3" :class="selectClass"></textarea>
                        <Button type="submit" :disabled="decisionForm.processing">Simpan Keputusan</Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle class="text-base">Reviewer</CardTitle></CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <div v-for="assignment in article.reviewer_assignments" :key="assignment.id" class="flex items-center justify-between">
                        <span>{{ assignment.reviewer.name }}</span>
                        <Badge :variant="reviewerAssignmentStatusVariants[assignment.status] ?? 'secondary'">
                            {{ reviewerAssignmentStatusLabels[assignment.status] ?? assignment.status }}
                        </Badge>
                    </div>
                    <p v-if="article.reviewer_assignments.length === 0" class="text-muted-foreground">
                        Belum ada reviewer ditugaskan. Kelola di menu Penugasan Reviewer.
                    </p>
                </CardContent>
            </Card>

            <Card v-if="article.presentation_material" class="border-violet-200 bg-violet-50 dark:border-violet-900 dark:bg-violet-950/40">
                <CardHeader><CardTitle class="text-base text-violet-800 dark:text-violet-300">Materi Presentasi</CardTitle></CardHeader>
                <CardContent class="space-y-1 text-sm">
                    <p v-if="article.presentation_material.slide_path">
                        <a :href="`/storage/${article.presentation_material.slide_path}`" target="_blank" class="text-primary underline">
                            File PowerPoint
                        </a>
                    </p>
                    <p v-if="article.presentation_material.video_path">
                        <a :href="`/storage/${article.presentation_material.video_path}`" target="_blank" class="text-primary underline">
                            Video Presentasi
                        </a>
                    </p>
                    <p v-if="article.presentation_material.official_photo_path">
                        <a :href="`/storage/${article.presentation_material.official_photo_path}`" target="_blank" class="text-primary underline">
                            Foto Resmi
                        </a>
                    </p>
                    <p v-if="article.presentation_material.short_bio" class="text-muted-foreground">
                        {{ article.presentation_material.short_bio }}
                    </p>
                    <Badge v-if="article.presentation_material.consent_confirmed_at" variant="success">Kesediaan Dikonfirmasi</Badge>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
