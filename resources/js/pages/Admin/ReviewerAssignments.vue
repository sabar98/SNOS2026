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
import { reviewerAssignmentStatusLabels, reviewerAssignmentStatusVariants } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ClipboardCheck, ClipboardList, Pencil, Search, Trash2, UserCheck, UserPlus, UserX } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Reviewer {
    id: number;
    name: string;
}

interface Assignment {
    id: number;
    reviewer: { id: number; name: string };
    status: string;
    due_date: string | null;
}

interface Article {
    id: number;
    title: string;
    event_registration: { user: { name: string } };
    reviewer_assignments: Assignment[];
}

const props = defineProps<{
    articles: Article[];
    reviewers: Reviewer[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Penugasan Reviewer', href: '/admin/reviewer-assignments' }];

interface Row {
    article: Article;
    assignment: Assignment | null;
    isFirstForArticle: boolean;
}

const rows = computed<Row[]>(() => {
    const result: Row[] = [];
    for (const article of props.articles) {
        if (article.reviewer_assignments.length === 0) {
            result.push({ article, assignment: null, isFirstForArticle: true });
        } else {
            article.reviewer_assignments.forEach((assignment, index) => {
                result.push({ article, assignment, isFirstForArticle: index === 0 });
            });
        }
    }
    return result;
});

const search = ref('');

const filteredRows = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return rows.value;
    return rows.value.filter(
        (row) =>
            row.article.title.toLowerCase().includes(query) ||
            row.article.event_registration.user.name.toLowerCase().includes(query) ||
            (row.assignment?.reviewer.name.toLowerCase().includes(query) ?? false),
    );
});

const totalArticles = computed(() => props.articles.length);
const totalAssigned = computed(() => props.articles.filter((article) => article.reviewer_assignments.length > 0).length);
const totalUnassigned = computed(() => totalArticles.value - totalAssigned.value);

function formatDate(date: string | null): string {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

const dialogOpen = ref(false);
const dialogMode = ref<'assign' | 'edit'>('assign');
const activeArticle = ref<Article | null>(null);
const activeAssignment = ref<Assignment | null>(null);

const dialogForm = useForm({
    reviewer_id: '',
    due_date: '',
});

const availableReviewers = computed(() => {
    if (dialogMode.value !== 'assign' || !activeArticle.value) return props.reviewers;
    const assignedIds = new Set(activeArticle.value.reviewer_assignments.map((assignment) => assignment.reviewer.id));
    return props.reviewers.filter((reviewer) => !assignedIds.has(reviewer.id));
});

function openAssignDialog(article: Article) {
    dialogMode.value = 'assign';
    activeArticle.value = article;
    activeAssignment.value = null;
    dialogForm.reset();
    dialogForm.clearErrors();
    dialogOpen.value = true;
}

function openEditDialog(article: Article, assignment: Assignment) {
    dialogMode.value = 'edit';
    activeArticle.value = article;
    activeAssignment.value = assignment;
    dialogForm.clearErrors();
    dialogForm.reviewer_id = String(assignment.reviewer.id);
    dialogForm.due_date = assignment.due_date ?? '';
    dialogOpen.value = true;
}

function submitDialog() {
    if (dialogMode.value === 'assign' && activeArticle.value) {
        const articleId = activeArticle.value.id;
        dialogForm
            .transform((data) => ({ ...data, article_id: articleId }))
            .post(route('admin.reviewer-assignments.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    dialogOpen.value = false;
                },
            });
    } else if (dialogMode.value === 'edit' && activeAssignment.value) {
        dialogForm.put(route('admin.reviewer-assignments.update', activeAssignment.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    }
}

function destroy(assignment: Assignment) {
    if (!confirm(`Hapus penugasan reviewer "${assignment.reviewer.name}"?`)) {
        return;
    }
    router.delete(route('admin.reviewer-assignments.destroy', assignment.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Penugasan Reviewer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="ClipboardCheck"
                icon-class="bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"
                title="Penugasan Reviewer"
                description="Tugaskan reviewer untuk artikel yang menunggu penilaian."
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
                            <p class="text-2xl font-bold tracking-tight">{{ totalArticles }}</p>
                            <p class="text-sm text-muted-foreground">Total Artikel</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400"
                        >
                            <UserCheck class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ totalAssigned }}</p>
                            <p class="text-sm text-muted-foreground">Sudah Ditugaskan</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400"
                        >
                            <UserX class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ totalUnassigned }}</p>
                            <p class="text-sm text-muted-foreground">Belum Ditugaskan</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <EmptyState
                v-if="articles.length === 0"
                :icon="ClipboardCheck"
                title="Tidak ada artikel yang menunggu penugasan"
                description="Semua artikel sudah memiliki reviewer, atau belum ada artikel yang perlu direview."
            />

            <Card v-else class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <CardTitle class="flex items-center gap-2"><ClipboardList class="size-4 text-muted-foreground" /> Daftar Penugasan</CardTitle>
                    <div class="relative w-full max-w-xs">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" placeholder="Cari artikel, peserta, atau reviewer..." class="pl-9" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[860px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="w-10 py-2">No</th>
                                    <th>Nama Artikel</th>
                                    <th>Nama Peserta</th>
                                    <th>Nama Reviewer</th>
                                    <th>Tanggal</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(row, index) in filteredRows"
                                    :key="`${row.article.id}-${row.assignment?.id ?? 'none'}`"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-3 text-muted-foreground">{{ index + 1 }}</td>
                                    <td class="max-w-[240px]">
                                        <p class="font-medium">{{ row.article.title }}</p>
                                    </td>
                                    <td class="text-muted-foreground">{{ row.article.event_registration.user.name }}</td>
                                    <td>
                                        <div v-if="row.assignment" class="flex flex-wrap items-center gap-2">
                                            <span>{{ row.assignment.reviewer.name }}</span>
                                            <Badge :variant="reviewerAssignmentStatusVariants[row.assignment.status] ?? 'secondary'">
                                                {{ reviewerAssignmentStatusLabels[row.assignment.status] ?? row.assignment.status }}
                                            </Badge>
                                        </div>
                                        <span v-else class="text-muted-foreground">Belum ditugaskan</span>
                                    </td>
                                    <td class="text-muted-foreground">{{ formatDate(row.assignment?.due_date ?? null) }}</td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button
                                                v-if="row.assignment"
                                                variant="outline"
                                                size="sm"
                                                class="gap-1.5"
                                                @click="openEditDialog(row.article, row.assignment)"
                                            >
                                                <Pencil class="size-3.5" /> Edit
                                            </Button>
                                            <Button
                                                v-if="row.assignment"
                                                variant="destructive"
                                                size="sm"
                                                class="gap-1.5"
                                                @click="destroy(row.assignment)"
                                            >
                                                <Trash2 class="size-3.5" /> Hapus
                                            </Button>
                                            <Button v-if="row.isFirstForArticle" size="sm" class="gap-1.5" @click="openAssignDialog(row.article)">
                                                <UserPlus class="size-3.5" /> Tugaskan
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredRows.length === 0">
                                    <td colspan="6" class="py-10 text-center text-muted-foreground">
                                        Tidak ada penugasan yang cocok dengan pencarian.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ dialogMode === 'assign' ? 'Tugaskan Reviewer' : 'Ubah Penugasan Reviewer' }}</DialogTitle>
                    <DialogDescription>{{ activeArticle?.title }}</DialogDescription>
                </DialogHeader>

                <form class="grid gap-4" @submit.prevent="submitDialog">
                    <div class="grid gap-1.5">
                        <Label for="dialog_reviewer">Reviewer</Label>
                        <select
                            id="dialog_reviewer"
                            v-model="dialogForm.reviewer_id"
                            class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="" disabled>Pilih reviewer</option>
                            <option v-for="reviewer in availableReviewers" :key="reviewer.id" :value="reviewer.id">{{ reviewer.name }}</option>
                        </select>
                        <p v-if="dialogForm.errors.reviewer_id" class="text-sm text-destructive">{{ dialogForm.errors.reviewer_id }}</p>
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="dialog_due_date">Batas Waktu Review</Label>
                        <Input id="dialog_due_date" v-model="dialogForm.due_date" type="date" />
                        <p v-if="dialogForm.errors.due_date" class="text-sm text-destructive">{{ dialogForm.errors.due_date }}</p>
                    </div>

                    <DialogFooter>
                        <Button type="submit" :disabled="dialogForm.processing">
                            {{ dialogMode === 'assign' ? 'Tugaskan' : 'Simpan Perubahan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
