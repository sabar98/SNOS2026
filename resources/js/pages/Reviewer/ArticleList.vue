<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { reviewerAssignmentStatusLabels, reviewerAssignmentStatusVariants } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

interface Assignment {
    id: number;
    status: string;
    due_date: string | null;
    article: { title: string };
    review: { recommendation: string } | null;
}

defineProps<{
    assignments: Assignment[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Artikel Ditugaskan', href: '/reviewer/articles' }];
</script>

<template>
    <Head title="Artikel Ditugaskan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-4 p-4">
            <Card v-for="assignment in assignments" :key="assignment.id">
                <CardHeader>
                    <CardTitle class="flex items-center justify-between text-base">
                        <span>{{ assignment.article.title }}</span>
                        <Link :href="route('reviewer.articles.show', assignment.id)">
                            <Button variant="outline" size="sm">{{ assignment.status === 'selesai' ? 'Lihat' : 'Review' }}</Button>
                        </Link>
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-2 text-sm text-muted-foreground">
                    <Badge :variant="reviewerAssignmentStatusVariants[assignment.status] ?? 'secondary'">
                        {{ reviewerAssignmentStatusLabels[assignment.status] ?? assignment.status }}
                    </Badge>
                    <p v-if="assignment.due_date">Batas waktu: {{ assignment.due_date }}</p>
                </CardContent>
            </Card>

            <div v-if="assignments.length === 0" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                Belum ada artikel yang ditugaskan kepada Anda.
            </div>
        </div>
    </AppLayout>
</template>
