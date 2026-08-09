<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { reviewerAssignmentStatusLabels, reviewerAssignmentStatusVariants } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

interface Reviewer {
    id: number;
    name: string;
}

interface Article {
    id: number;
    title: string;
    event_registration: { user: { name: string } };
    reviewer_assignments: { id: number; reviewer: { name: string }; status: string }[];
}

defineProps<{
    articles: Article[];
    reviewers: Reviewer[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Penugasan Reviewer', href: '/admin/reviewer-assignments' }];

const selectClass = 'flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm';

const forms: Record<number, ReturnType<typeof useForm>> = {};

function formFor(articleId: number) {
    if (!forms[articleId]) {
        forms[articleId] = useForm({ article_id: articleId, reviewer_id: '', due_date: '' });
    }
    return forms[articleId];
}

function assign(articleId: number) {
    const form = formFor(articleId);
    form.post(route('admin.reviewer-assignments.store'), { preserveScroll: true });
}
</script>

<template>
    <Head title="Penugasan Reviewer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-4 p-4">
            <Card v-for="article in articles" :key="article.id">
                <CardHeader>
                    <CardTitle class="text-base">{{ article.title }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 text-sm">
                    <p class="text-muted-foreground">{{ article.event_registration.user.name }}</p>

                    <ul class="space-y-2">
                        <li v-for="assignment in article.reviewer_assignments" :key="assignment.id" class="flex items-center justify-between">
                            <span>{{ assignment.reviewer.name }}</span>
                            <Badge :variant="reviewerAssignmentStatusVariants[assignment.status] ?? 'secondary'">
                                {{ reviewerAssignmentStatusLabels[assignment.status] ?? assignment.status }}
                            </Badge>
                        </li>
                    </ul>

                    <form class="flex items-center gap-2" @submit.prevent="assign(article.id)">
                        <select v-model="formFor(article.id).reviewer_id" aria-label="Pilih reviewer" :class="selectClass">
                            <option value="" disabled>Pilih reviewer</option>
                            <option v-for="reviewer in reviewers" :key="reviewer.id" :value="reviewer.id">{{ reviewer.name }}</option>
                        </select>
                        <input v-model="formFor(article.id).due_date" type="date" aria-label="Batas waktu review" :class="selectClass" />
                        <Button type="submit" size="sm">Tugaskan</Button>
                    </form>
                </CardContent>
            </Card>

            <div v-if="articles.length === 0" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                Tidak ada artikel yang menunggu penugasan reviewer.
            </div>
        </div>
    </AppLayout>
</template>
