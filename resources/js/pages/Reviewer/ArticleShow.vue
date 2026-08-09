<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

interface Assignment {
    id: number;
    due_date: string | null;
    article: {
        title: string;
        abstract: string;
        keywords: string;
        field: string;
        file_path: string;
        authors: { name: string; affiliation: string | null }[];
    };
    review: {
        theme_suitability_score: number;
        novelty_score: number;
        methodology_score: number;
        results_discussion_score: number;
        reference_quality_score: number;
        language_grammar_score: number;
        comments: string | null;
        recommendation: string;
    } | null;
}

const props = defineProps<{
    assignment: Assignment;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: props.assignment.article.title, href: '#' }];
const selectClass = 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm';

const form = useForm({
    theme_suitability_score: props.assignment.review?.theme_suitability_score ?? 5,
    novelty_score: props.assignment.review?.novelty_score ?? 5,
    methodology_score: props.assignment.review?.methodology_score ?? 5,
    results_discussion_score: props.assignment.review?.results_discussion_score ?? 5,
    reference_quality_score: props.assignment.review?.reference_quality_score ?? 5,
    language_grammar_score: props.assignment.review?.language_grammar_score ?? 5,
    comments: props.assignment.review?.comments ?? '',
    recommendation: props.assignment.review?.recommendation ?? 'diterima_tanpa_revisi',
});

function submit() {
    form.post(route('reviewer.articles.review', props.assignment.id));
}
</script>

<template>
    <Head :title="assignment.article.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <Card>
                <CardHeader
                    ><CardTitle>{{ assignment.article.title }}</CardTitle></CardHeader
                >
                <CardContent class="space-y-2 text-sm">
                    <p class="text-muted-foreground">{{ assignment.article.abstract }}</p>
                    <p>Kata kunci: {{ assignment.article.keywords }}</p>
                    <p>Bidang: {{ assignment.article.field }}</p>
                    <a :href="`/storage/${assignment.article.file_path}`" target="_blank" class="text-primary underline">Unduh Artikel</a>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle class="text-base">Form Penilaian</CardTitle></CardHeader>
                <CardContent>
                    <form class="grid gap-3" @submit.prevent="submit">
                        <label class="flex items-center justify-between text-sm">
                            Kesesuaian Tema
                            <input
                                v-model.number="form.theme_suitability_score"
                                type="number"
                                min="1"
                                max="5"
                                class="w-16 rounded-md border border-input px-2 py-1"
                            />
                        </label>
                        <label class="flex items-center justify-between text-sm">
                            Kebaruan Penelitian
                            <input
                                v-model.number="form.novelty_score"
                                type="number"
                                min="1"
                                max="5"
                                class="w-16 rounded-md border border-input px-2 py-1"
                            />
                        </label>
                        <label class="flex items-center justify-between text-sm">
                            Metode Penelitian
                            <input
                                v-model.number="form.methodology_score"
                                type="number"
                                min="1"
                                max="5"
                                class="w-16 rounded-md border border-input px-2 py-1"
                            />
                        </label>
                        <label class="flex items-center justify-between text-sm">
                            Hasil dan Pembahasan
                            <input
                                v-model.number="form.results_discussion_score"
                                type="number"
                                min="1"
                                max="5"
                                class="w-16 rounded-md border border-input px-2 py-1"
                            />
                        </label>
                        <label class="flex items-center justify-between text-sm">
                            Kualitas Referensi
                            <input
                                v-model.number="form.reference_quality_score"
                                type="number"
                                min="1"
                                max="5"
                                class="w-16 rounded-md border border-input px-2 py-1"
                            />
                        </label>
                        <label class="flex items-center justify-between text-sm">
                            Tata Bahasa dan Penulisan
                            <input
                                v-model.number="form.language_grammar_score"
                                type="number"
                                min="1"
                                max="5"
                                class="w-16 rounded-md border border-input px-2 py-1"
                            />
                        </label>

                        <textarea v-model="form.comments" rows="3" placeholder="Komentar" aria-label="Komentar" :class="selectClass"></textarea>

                        <select v-model="form.recommendation" aria-label="Rekomendasi" :class="selectClass">
                            <option value="diterima_tanpa_revisi">Diterima tanpa revisi</option>
                            <option value="diterima_revisi_minor">Diterima dengan revisi minor</option>
                            <option value="revisi_mayor">Revisi mayor</option>
                            <option value="ditolak">Ditolak</option>
                        </select>

                        <Button type="submit" :disabled="form.processing">Kirim Penilaian</Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
