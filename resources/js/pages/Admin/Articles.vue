<script setup lang="ts">
import BarChart from '@/components/charts/BarChart.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { articleStatusLabels, articleStatusVariants, toChartData } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
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
        <div class="flex flex-1 flex-col gap-4 p-4">
            <Card>
                <CardContent class="pt-6">
                    <BarChart title="Artikel per Status" :data="statusChartData" />
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle>Daftar Artikel</CardTitle></CardHeader>
                <CardContent>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="py-2">Judul</th>
                                <th>Penulis</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="article in articles.data" :key="article.id" class="border-b last:border-0">
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
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
