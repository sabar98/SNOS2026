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
import { publicationStatusLabels, publicationStatusVariants } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { BookOpen, CheckCircle2, Clock, ExternalLink, Pencil, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Journal {
    id: number;
    name: string;
}

interface Publication {
    id: number;
    journal_id: number;
    status: string;
    volume: string | null;
    issue_number: string | null;
    doi: string | null;
    article_url: string | null;
    published_at: string | null;
}

interface Article {
    id: number;
    title: string;
    event_registration: { user: { name: string } };
    journal: Journal | null;
    publication: Publication | null;
}

const props = defineProps<{
    articles: Article[];
    journals: Journal[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Publikasi', href: '/admin/publications' }];

const search = ref('');

const filteredArticles = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return props.articles;
    return props.articles.filter(
        (article) =>
            article.title.toLowerCase().includes(query) ||
            article.event_registration.user.name.toLowerCase().includes(query) ||
            (article.journal?.name.toLowerCase().includes(query) ?? false),
    );
});

const publishedCount = computed(() => props.articles.filter((article) => article.publication?.status === 'terbit').length);
const processingCount = computed(() => props.articles.length - publishedCount.value);

function formatDate(date: string | null): string {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

const dialogOpen = ref(false);
const activeArticle = ref<Article | null>(null);

const dialogForm = useForm({
    journal_id: '' as number | string,
    status: 'diproses',
    volume: '',
    issue_number: '',
    doi: '',
    article_url: '',
});

function openDialog(article: Article) {
    activeArticle.value = article;
    dialogForm.clearErrors();
    dialogForm.journal_id = article.publication?.journal_id ?? article.journal?.id ?? '';
    dialogForm.status = article.publication?.status ?? 'diproses';
    dialogForm.volume = article.publication?.volume ?? '';
    dialogForm.issue_number = article.publication?.issue_number ?? '';
    dialogForm.doi = article.publication?.doi ?? '';
    dialogForm.article_url = article.publication?.article_url ?? '';
    dialogOpen.value = true;
}

function submitDialog() {
    if (!activeArticle.value) return;
    dialogForm.post(route('admin.publications.store', activeArticle.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            dialogOpen.value = false;
        },
    });
}
</script>

<template>
    <Head title="Publikasi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="BookOpen"
                icon-class="bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400"
                title="Publikasi"
                description="Catat status publikasi artikel yang telah diterima."
            />

            <div class="grid gap-4 sm:grid-cols-3">
                <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400"
                        >
                            <BookOpen class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ articles.length }}</p>
                            <p class="text-sm text-muted-foreground">Artikel Diterima</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"
                        >
                            <CheckCircle2 class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ publishedCount }}</p>
                            <p class="text-sm text-muted-foreground">Sudah Terbit</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400"
                        >
                            <Clock class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ processingCount }}</p>
                            <p class="text-sm text-muted-foreground">Dalam Proses</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <EmptyState
                v-if="articles.length === 0"
                :icon="BookOpen"
                title="Belum ada artikel untuk dipublikasikan"
                description="Artikel yang sudah diterima akan muncul di sini untuk dicatat status publikasinya."
            />

            <Card v-else class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <CardTitle class="flex items-center gap-2"><BookOpen class="size-4 text-muted-foreground" /> Daftar Artikel Diterima</CardTitle>
                    <div class="relative w-full max-w-xs">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" placeholder="Cari artikel, peserta, atau jurnal..." class="pl-9" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[880px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="w-10 py-2">No</th>
                                    <th>Judul Artikel</th>
                                    <th>Nama Peserta</th>
                                    <th>Jurnal / Prosiding</th>
                                    <th>Status</th>
                                    <th>Tanggal Terbit</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(article, index) in filteredArticles"
                                    :key="article.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-3 text-muted-foreground">{{ index + 1 }}</td>
                                    <td class="max-w-[240px]">
                                        <p class="font-medium">{{ article.title }}</p>
                                        <a
                                            v-if="article.publication?.article_url"
                                            :href="article.publication.article_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
                                        >
                                            <ExternalLink class="size-3" /> Lihat artikel
                                        </a>
                                    </td>
                                    <td class="text-muted-foreground">{{ article.event_registration.user.name }}</td>
                                    <td class="text-muted-foreground">{{ article.journal?.name ?? '—' }}</td>
                                    <td>
                                        <Badge :variant="publicationStatusVariants[article.publication?.status ?? 'diproses'] ?? 'secondary'">
                                            {{ publicationStatusLabels[article.publication?.status ?? 'diproses'] ?? 'Belum Diproses' }}
                                        </Badge>
                                    </td>
                                    <td class="text-muted-foreground">{{ formatDate(article.publication?.published_at ?? null) }}</td>
                                    <td class="text-right">
                                        <Button variant="outline" size="sm" class="gap-1.5" @click="openDialog(article)">
                                            <Pencil class="size-3.5" /> Kelola
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="filteredArticles.length === 0">
                                    <td colspan="7" class="py-10 text-center text-muted-foreground">
                                        Tidak ada artikel yang cocok dengan pencarian.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent class="max-h-[85vh] overflow-y-auto sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Kelola Publikasi</DialogTitle>
                    <DialogDescription>{{ activeArticle?.title }}</DialogDescription>
                </DialogHeader>

                <form class="grid gap-4" @submit.prevent="submitDialog">
                    <div class="grid gap-1.5">
                        <Label for="publication_journal">Jurnal / Prosiding</Label>
                        <select
                            id="publication_journal"
                            v-model="dialogForm.journal_id"
                            class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                            required
                        >
                            <option value="" disabled>Pilih jurnal / prosiding</option>
                            <option v-for="journal in journals" :key="journal.id" :value="journal.id">{{ journal.name }}</option>
                        </select>
                        <p v-if="dialogForm.errors.journal_id" class="text-sm text-destructive">{{ dialogForm.errors.journal_id }}</p>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="publication_status">Status Publikasi</Label>
                        <select
                            id="publication_status"
                            v-model="dialogForm.status"
                            class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="diproses">Diproses</option>
                            <option value="terbit">Terbit</option>
                        </select>
                        <p v-if="dialogForm.errors.status" class="text-sm text-destructive">{{ dialogForm.errors.status }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="publication_volume">Volume</Label>
                            <Input id="publication_volume" v-model="dialogForm.volume" placeholder="Mis. 12" />
                            <p v-if="dialogForm.errors.volume" class="text-sm text-destructive">{{ dialogForm.errors.volume }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="publication_issue">Nomor Terbitan</Label>
                            <Input id="publication_issue" v-model="dialogForm.issue_number" placeholder="Mis. 3" />
                            <p v-if="dialogForm.errors.issue_number" class="text-sm text-destructive">{{ dialogForm.errors.issue_number }}</p>
                        </div>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="publication_doi">DOI</Label>
                        <Input id="publication_doi" v-model="dialogForm.doi" placeholder="10.xxxx/xxxxx" />
                        <p v-if="dialogForm.errors.doi" class="text-sm text-destructive">{{ dialogForm.errors.doi }}</p>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="publication_url">Link Artikel</Label>
                        <Input id="publication_url" v-model="dialogForm.article_url" type="url" placeholder="https://" />
                        <p v-if="dialogForm.errors.article_url" class="text-sm text-destructive">{{ dialogForm.errors.article_url }}</p>
                    </div>

                    <DialogFooter>
                        <Button type="submit" :disabled="dialogForm.processing">Simpan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
