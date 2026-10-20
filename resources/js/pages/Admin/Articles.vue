<script setup lang="ts">
import DonutChart from '@/components/charts/DonutChart.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { articleStatusLabels, articleStatusVariants, toChartData } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { FileSpreadsheet, FileText, ListChecks, Pencil, Printer, Search, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Article {
    id: number;
    title: string;
    abstract: string;
    keywords: string;
    field: string;
    journal_id: number | null;
    status: string;
    created_at: string;
    event_registration: { user: { name: string } };
}

interface Journal {
    id: number;
    name: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    articles: { data: Article[]; links: PaginationLink[]; total: number };
    journals: Journal[];
    articlesByStatus: Record<string, number>;
    filters: { search: string; date_from: string | null; date_to: string | null };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Artikel', href: '/admin/articles' }];

const statusChartData = computed(() => toChartData(props.articlesByStatus, articleStatusLabels));

// Server-side search + date filter (the list is paginated, so filtering only the current
// page would hide matches on other pages) — debounced so it doesn't fire on every keystroke.
const search = ref(props.filters.search);
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
let filterTimeout: ReturnType<typeof setTimeout> | undefined;

watch([search, dateFrom, dateTo], ([searchValue, dateFromValue, dateToValue]) => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get(
            route('admin.articles.index'),
            {
                search: searchValue || undefined,
                date_from: dateFromValue || undefined,
                date_to: dateToValue || undefined,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 400);
});

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Export/print always reflect whatever is currently searched and filtered.
const exportParams = computed(() => ({
    search: search.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
}));
const exportExcelUrl = computed(() => route('admin.articles.export', exportParams.value));
const exportPdfUrl = computed(() => route('admin.articles.export-pdf', exportParams.value));

const editDialogOpen = ref(false);
const activeArticle = ref<Article | null>(null);

const editForm = useForm({
    journal_id: null as number | null,
    title: '',
    abstract: '',
    keywords: '',
    field: '',
});

function openEditDialog(article: Article) {
    activeArticle.value = article;
    editForm.clearErrors();
    editForm.journal_id = article.journal_id;
    editForm.title = article.title;
    editForm.abstract = article.abstract;
    editForm.keywords = article.keywords;
    editForm.field = article.field;
    editDialogOpen.value = true;
}

function submitEdit() {
    if (!activeArticle.value) return;
    editForm.put(route('admin.articles.update-details', activeArticle.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            editDialogOpen.value = false;
        },
    });
}

function destroy(article: Article) {
    if (!confirm(`Hapus artikel "${article.title}"? Tindakan ini tidak bisa dibatalkan.`)) {
        return;
    }
    router.delete(route('admin.articles.destroy', article.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Kelola Artikel" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader :icon="FileText" title="Artikel" description="Kelola seluruh artikel yang masuk pada SNOS 2026." />

            <Card class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                <CardContent class="pt-6">
                    <DonutChart title="Artikel per Status" :data="statusChartData" />
                </CardContent>
            </Card>

            <Card class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <div class="flex items-center gap-2">
                        <CardTitle class="flex items-center gap-2"><ListChecks class="size-4 text-muted-foreground" /> Daftar Artikel</CardTitle>
                        <Badge variant="secondary">{{ articles.total }} artikel</Badge>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="relative w-full max-w-xs">
                            <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="search" placeholder="Cari judul atau nama penulis..." class="pl-9" />
                        </div>
                        <Input v-model="dateFrom" type="date" class="w-auto" aria-label="Dari tanggal" />
                        <span class="text-sm text-muted-foreground">s.d.</span>
                        <Input v-model="dateTo" type="date" class="w-auto" aria-label="Sampai tanggal" />
                        <a :href="exportExcelUrl">
                            <Button variant="outline" size="sm" class="gap-2"><FileSpreadsheet class="size-4" /> Export Excel</Button>
                        </a>
                        <a :href="exportPdfUrl" target="_blank">
                            <Button variant="outline" size="sm" class="gap-2"><Printer class="size-4" /> Cetak PDF</Button>
                        </a>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="py-2">Judul</th>
                                    <th>Penulis</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="articles.data.length === 0">
                                    <td colspan="5" class="py-6 text-center text-muted-foreground">
                                        Tidak ada artikel yang cocok dengan pencarian.
                                    </td>
                                </tr>
                                <tr
                                    v-for="article in articles.data"
                                    :key="article.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-2">{{ article.title }}</td>
                                    <td>{{ article.event_registration.user.name }}</td>
                                    <td>{{ formatDate(article.created_at) }}</td>
                                    <td>
                                        <Badge :variant="articleStatusVariants[article.status] ?? 'secondary'">
                                            {{ articleStatusLabels[article.status] ?? article.status }}
                                        </Badge>
                                    </td>
                                    <td class="py-2 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('admin.articles.show', article.id)">
                                                <Button variant="outline" size="sm">Detail</Button>
                                            </Link>
                                            <Button variant="outline" size="sm" class="gap-1.5" @click="openEditDialog(article)">
                                                <Pencil class="size-3.5" /> Edit
                                            </Button>
                                            <Button variant="destructive" size="sm" class="gap-1.5" @click="destroy(article)">
                                                <Trash2 class="size-3.5" /> Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <Link
                            v-for="link in articles.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            :class="[
                                'rounded px-3 py-1 text-sm',
                                link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground',
                                !link.url && 'pointer-events-none opacity-50',
                            ]"
                        >
                            <span v-html="link.label" />
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="editDialogOpen">
            <DialogContent class="max-h-[85vh] overflow-y-auto sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Ubah Artikel</DialogTitle>
                    <DialogDescription>{{ activeArticle?.title }}</DialogDescription>
                </DialogHeader>

                <form class="grid gap-4" @submit.prevent="submitEdit">
                    <div class="grid gap-1.5">
                        <Label for="article_title">Judul</Label>
                        <Input id="article_title" v-model="editForm.title" required />
                        <p v-if="editForm.errors.title" class="text-sm text-destructive">{{ editForm.errors.title }}</p>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="article_abstract">Abstrak</Label>
                        <textarea
                            id="article_abstract"
                            v-model="editForm.abstract"
                            rows="4"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                        ></textarea>
                        <p v-if="editForm.errors.abstract" class="text-sm text-destructive">{{ editForm.errors.abstract }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="article_keywords">Kata Kunci</Label>
                            <Input id="article_keywords" v-model="editForm.keywords" />
                            <p v-if="editForm.errors.keywords" class="text-sm text-destructive">{{ editForm.errors.keywords }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="article_field">Bidang</Label>
                            <Input id="article_field" v-model="editForm.field" />
                            <p v-if="editForm.errors.field" class="text-sm text-destructive">{{ editForm.errors.field }}</p>
                        </div>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="article_journal">Jurnal / Prosiding Tujuan</Label>
                        <select
                            id="article_journal"
                            v-model="editForm.journal_id"
                            class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option :value="null">— Belum dipilih —</option>
                            <option v-for="journal in journals" :key="journal.id" :value="journal.id">{{ journal.name }}</option>
                        </select>
                        <p v-if="editForm.errors.journal_id" class="text-sm text-destructive">{{ editForm.errors.journal_id }}</p>
                    </div>

                    <DialogFooter>
                        <Button type="submit" :disabled="editForm.processing">Simpan Perubahan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
