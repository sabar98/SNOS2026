<script setup lang="ts">
import DonutChart from '@/components/charts/DonutChart.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { articleStatusLabels, articleStatusVariants, toChartData } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { FileText, ListChecks, Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Article {
    id: number;
    title: string;
    status: string;
    created_at: string;
    event_registration: { user: { name: string } };
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    articles: { data: Article[]; links: PaginationLink[]; total: number };
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
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="py-2">Judul</th>
                                    <th>Penulis</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th></th>
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
                                    <td>
                                        <Link :href="route('admin.articles.show', article.id)">
                                            <Button variant="outline" size="sm">Detail</Button>
                                        </Link>
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
    </AppLayout>
</template>
