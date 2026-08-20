<script setup lang="ts">
import DonutChart from '@/components/charts/DonutChart.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { articleStatusLabels, articleStatusVariants, toChartData } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { FileText, ListChecks } from 'lucide-vue-next';
import { computed } from 'vue';

interface Article {
    id: number;
    title: string;
    status: string;
    event_registration: { user: { name: string } };
}

const props = defineProps<{
    articles: { data: Article[] };
    articlesByStatus: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Artikel', href: '/admin/articles' }];

const statusChartData = computed(() => toChartData(props.articlesByStatus, articleStatusLabels));
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
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle class="flex items-center gap-2"><ListChecks class="size-4 text-muted-foreground" /> Daftar Artikel</CardTitle>
                    <Badge variant="secondary">{{ articles.data.length }} artikel</Badge>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[520px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="py-2">Judul</th>
                                    <th>Penulis</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="article in articles.data"
                                    :key="article.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-2">{{ article.title }}</td>
                                    <td>{{ article.event_registration.user.name }}</td>
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
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
