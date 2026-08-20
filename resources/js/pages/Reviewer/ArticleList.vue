<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { reviewerAssignmentStatusLabels, reviewerAssignmentStatusVariants } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarClock, CheckCircle2, ClipboardList, FileSearch, FileText, Hourglass, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Assignment {
    id: number;
    status: string;
    due_date: string | null;
    article: { title: string; field: string | null };
    review: { recommendation: string } | null;
}

const props = defineProps<{
    assignments: Assignment[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Artikel Ditugaskan', href: '/reviewer/articles' }];

const search = ref('');

const filteredAssignments = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return props.assignments;
    return props.assignments.filter((a) => a.article.title.toLowerCase().includes(query));
});

const totalCount = computed(() => props.assignments.length);
const pendingCount = computed(() => props.assignments.filter((a) => a.status !== 'selesai').length);
const doneCount = computed(() => props.assignments.filter((a) => a.status === 'selesai').length);

function formatDate(date: string | null): string {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}
</script>

<template>
    <Head title="Artikel Ditugaskan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="FileSearch"
                icon-class="bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"
                title="Artikel Ditugaskan"
                description="Artikel yang perlu Anda review untuk SNOS 2026."
            />

            <div class="grid gap-4 sm:grid-cols-3">
                <Card class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"
                        >
                            <ClipboardList class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ totalCount }}</p>
                            <p class="text-sm text-muted-foreground">Total Ditugaskan</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400"
                        >
                            <Hourglass class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ pendingCount }}</p>
                            <p class="text-sm text-muted-foreground">Menunggu Review</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400"
                        >
                            <CheckCircle2 class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ doneCount }}</p>
                            <p class="text-sm text-muted-foreground">Selesai Direview</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <CardTitle class="flex items-center gap-2"><FileText class="size-4 text-muted-foreground" /> Daftar Artikel</CardTitle>
                    <div class="relative w-full max-w-xs">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" placeholder="Cari judul artikel..." class="pl-9" />
                    </div>
                </CardHeader>
                <CardContent class="flex flex-col gap-3">
                    <EmptyState
                        v-if="filteredAssignments.length === 0"
                        :icon="FileSearch"
                        :title="search ? 'Tidak ada artikel yang cocok' : 'Belum ada artikel ditugaskan'"
                        :description="search ? 'Coba kata kunci lain.' : 'Artikel yang ditugaskan panitia kepada Anda akan muncul di sini.'"
                    />

                    <div
                        v-for="assignment in filteredAssignments"
                        :key="assignment.id"
                        class="flex flex-col gap-3 rounded-xl border bg-background/70 p-4 transition-shadow duration-200 hover:shadow-md sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-start gap-3">
                            <span
                                class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"
                            >
                                <FileText class="size-4" />
                            </span>
                            <div class="space-y-1">
                                <p class="font-medium leading-snug">{{ assignment.article.title }}</p>
                                <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                    <span v-if="assignment.article.field">{{ assignment.article.field }}</span>
                                    <span v-if="assignment.due_date" class="flex items-center gap-1">
                                        <CalendarClock class="size-3" /> {{ formatDate(assignment.due_date) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2 sm:pl-4">
                            <Badge :variant="reviewerAssignmentStatusVariants[assignment.status] ?? 'secondary'">
                                {{ reviewerAssignmentStatusLabels[assignment.status] ?? assignment.status }}
                            </Badge>
                            <Link :href="route('reviewer.articles.show', assignment.id)">
                                <Button variant="outline" size="sm">{{ assignment.status === 'selesai' ? 'Lihat' : 'Review' }}</Button>
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
