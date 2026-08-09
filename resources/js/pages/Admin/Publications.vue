<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { publicationStatusLabels, publicationStatusVariants } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

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
const selectClass = 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm';

const publishedCount = computed(() => props.articles.filter((article) => article.publication?.status === 'terbit').length);

const forms: Record<number, ReturnType<typeof useForm>> = {};

function formFor(article: Article) {
    if (!forms[article.id]) {
        forms[article.id] = useForm({
            journal_id: article.publication?.journal_id ?? article.journal?.id ?? '',
            status: article.publication?.status ?? 'diproses',
            volume: article.publication?.volume ?? '',
            issue_number: article.publication?.issue_number ?? '',
            doi: article.publication?.doi ?? '',
            article_url: article.publication?.article_url ?? '',
        });
    }
    return forms[article.id];
}

function save(articleId: number) {
    formFor(props.articles.find((a) => a.id === articleId)!).post(route('admin.publications.store', articleId), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Publikasi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
            <div class="grid gap-4 md:grid-cols-2">
                <Card class="border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/40">
                    <CardHeader
                        ><CardTitle class="text-sm font-medium text-amber-800/80 dark:text-amber-300/80">Artikel Diterima</CardTitle></CardHeader
                    >
                    <CardContent class="text-2xl font-bold text-amber-800 dark:text-amber-300">{{ articles.length }}</CardContent>
                </Card>
                <Card class="border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40">
                    <CardHeader
                        ><CardTitle class="text-sm font-medium text-emerald-800/80 dark:text-emerald-300/80">Sudah Terbit</CardTitle></CardHeader
                    >
                    <CardContent class="text-2xl font-bold text-emerald-800 dark:text-emerald-300">{{ publishedCount }}</CardContent>
                </Card>
            </div>

            <Card v-for="article in articles" :key="article.id">
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle class="text-base">{{ article.title }}</CardTitle>
                    <Badge :variant="publicationStatusVariants[article.publication?.status ?? 'diproses'] ?? 'secondary'">
                        {{ publicationStatusLabels[article.publication?.status ?? 'diproses'] ?? 'Belum Diproses' }}
                    </Badge>
                </CardHeader>
                <CardContent>
                    <p class="mb-3 text-sm text-muted-foreground">{{ article.event_registration.user.name }}</p>

                    <form class="grid grid-cols-2 gap-3" @submit.prevent="save(article.id)">
                        <div class="col-span-2">
                            <select v-model="formFor(article).journal_id" aria-label="Pilih jurnal / prosiding" :class="selectClass" required>
                                <option value="" disabled>Pilih jurnal / prosiding</option>
                                <option v-for="journal in journals" :key="journal.id" :value="journal.id">{{ journal.name }}</option>
                            </select>
                        </div>
                        <select v-model="formFor(article).status" aria-label="Status publikasi" :class="selectClass">
                            <option value="diproses">Diproses</option>
                            <option value="terbit">Terbit</option>
                        </select>
                        <Input v-model="formFor(article).volume" placeholder="Volume" />
                        <Input v-model="formFor(article).issue_number" placeholder="Nomor Terbitan" />
                        <Input v-model="formFor(article).doi" placeholder="DOI" />
                        <Input v-model="formFor(article).article_url" placeholder="Link Artikel" class="col-span-2" />
                        <Button type="submit" size="sm" class="col-span-2" :disabled="formFor(article).processing">Simpan</Button>
                    </form>
                </CardContent>
            </Card>

            <div v-if="articles.length === 0" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                Belum ada artikel yang diterima untuk dipublikasikan.
            </div>
        </div>
    </AppLayout>
</template>
