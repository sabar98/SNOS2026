<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { reviewRecommendationLabels, reviewRecommendationVariants } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, Download, FileText, MessageSquare, Star, Users } from 'lucide-vue-next';

interface Assignment {
    id: number;
    due_date: string | null;
    article: {
        title: string;
        abstract: string;
        keywords: string;
        field: string;
        file_path: string | null;
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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Artikel Ditugaskan', href: '/reviewer/articles' },
    { title: props.assignment.article.title, href: '#' },
];

const scoreFields: { key: keyof NonNullable<Assignment['review']>; label: string }[] = [
    { key: 'theme_suitability_score', label: 'Kesesuaian Tema' },
    { key: 'novelty_score', label: 'Kebaruan Penelitian' },
    { key: 'methodology_score', label: 'Metode Penelitian' },
    { key: 'results_discussion_score', label: 'Hasil dan Pembahasan' },
    { key: 'reference_quality_score', label: 'Kualitas Referensi' },
    { key: 'language_grammar_score', label: 'Tata Bahasa dan Penulisan' },
];

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
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4">
            <Link
                :href="route('reviewer.articles.index')"
                class="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="size-4" /> Kembali ke Daftar Artikel
            </Link>

            <PageHeader
                :icon="FileText"
                icon-class="bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"
                title="Review Artikel"
                description="Periksa berkas artikel dan berikan penilaian serta rekomendasi."
            >
                <template #actions>
                    <Badge v-if="assignment.review" variant="success" class="gap-1"><CheckCircle2 class="size-3" /> Sudah Direview</Badge>
                </template>
            </PageHeader>

            <Card class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                <CardHeader>
                    <CardTitle class="text-base">{{ assignment.article.title }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 text-sm">
                    <p class="text-muted-foreground">{{ assignment.article.abstract }}</p>
                    <div class="flex flex-wrap gap-2">
                        <Badge variant="secondary">Kata kunci: {{ assignment.article.keywords }}</Badge>
                        <Badge variant="secondary">Bidang: {{ assignment.article.field }}</Badge>
                    </div>
                    <div v-if="assignment.article.authors.length" class="flex items-start gap-2 pt-1">
                        <Users class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                        <p class="text-muted-foreground">
                            {{ assignment.article.authors.map((a) => a.name).join(', ') }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card class="overflow-hidden border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <FileText class="size-4 text-emerald-700 dark:text-emerald-400" /> Berkas Artikel (PDF)
                    </CardTitle>
                    <a v-if="assignment.article.file_path" :href="`/storage/${assignment.article.file_path}`" download>
                        <Button variant="outline" size="sm" class="gap-1.5"><Download class="size-3.5" /> Unduh</Button>
                    </a>
                </CardHeader>
                <CardContent class="p-0">
                    <div v-if="assignment.article.file_path" class="overflow-hidden rounded-b-xl border-t bg-background">
                        <iframe :src="`/storage/${assignment.article.file_path}`" class="h-[650px] w-full" title="Berkas artikel PDF"></iframe>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center gap-2 border-t px-6 py-16 text-center text-muted-foreground">
                        <FileText class="size-8" />
                        <p class="text-sm">Belum ada berkas PDF yang diunggah untuk artikel ini.</p>
                    </div>
                </CardContent>
            </Card>

            <Card class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Star class="size-4 text-emerald-700 dark:text-emerald-400" /> Form Penilaian
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-col gap-5" @submit.prevent="submit">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label
                                v-for="field in scoreFields"
                                :key="field.key"
                                class="flex items-center justify-between gap-3 rounded-lg border bg-background/70 px-3 py-2.5 text-sm"
                            >
                                {{ field.label }}
                                <input
                                    v-model.number="form[field.key as keyof typeof form] as number"
                                    type="number"
                                    min="1"
                                    max="5"
                                    class="w-16 shrink-0 rounded-md border border-input bg-background px-2 py-1 text-center"
                                />
                            </label>
                        </div>

                        <div class="grid gap-1.5">
                            <label for="comments" class="flex items-center gap-1.5 text-sm font-medium">
                                <MessageSquare class="size-3.5 text-muted-foreground" /> Komentar
                            </label>
                            <textarea
                                id="comments"
                                v-model="form.comments"
                                rows="3"
                                placeholder="Catatan untuk penulis artikel"
                                class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                            ></textarea>
                        </div>

                        <div class="grid gap-1.5">
                            <label for="recommendation" class="text-sm font-medium">Rekomendasi</label>
                            <select
                                id="recommendation"
                                v-model="form.recommendation"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            >
                                <option value="diterima_tanpa_revisi">
                                    {{ reviewRecommendationLabels.diterima_tanpa_revisi }}
                                </option>
                                <option value="diterima_revisi_minor">
                                    {{ reviewRecommendationLabels.diterima_revisi_minor }}
                                </option>
                                <option value="revisi_mayor">{{ reviewRecommendationLabels.revisi_mayor }}</option>
                                <option value="ditolak">{{ reviewRecommendationLabels.ditolak }}</option>
                            </select>
                            <Badge :variant="reviewRecommendationVariants[form.recommendation] ?? 'secondary'" class="w-fit">
                                {{ reviewRecommendationLabels[form.recommendation] ?? form.recommendation }}
                            </Badge>
                        </div>

                        <Button type="submit" class="w-fit gap-2" :disabled="form.processing">
                            <CheckCircle2 class="size-4" /> Kirim Penilaian
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
