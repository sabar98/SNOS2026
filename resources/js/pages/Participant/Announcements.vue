<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { announcementTypeLabels, announcementTypeVariants } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Megaphone } from 'lucide-vue-next';

interface Announcement {
    id: number;
    type: string;
    title: string;
    body: string | null;
    link_url: string | null;
    published_at: string | null;
    file_path: string | null;
}

defineProps<{
    announcements: Announcement[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Pengumuman', href: '/participant/announcements' }];
</script>

<template>
    <Head title="Pengumuman" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <PageHeader :icon="Megaphone" title="Pengumuman" description="Berita dan pengumuman terbaru seputar SNOS 2026." />

            <EmptyState v-if="announcements.length === 0" :icon="Megaphone" title="Belum ada pengumuman" />

            <Card v-for="announcement in announcements" :key="announcement.id" class="overflow-hidden transition-shadow duration-200 hover:shadow-md">
                <img
                    v-if="announcement.file_path"
                    :src="`/storage/${announcement.file_path}`"
                    :alt="announcement.title"
                    class="h-40 w-full object-cover"
                />
                <CardHeader class="flex flex-row items-start justify-between space-y-0 pb-2">
                    <div>
                        <CardTitle class="text-base">{{ announcement.title }}</CardTitle>
                        <p v-if="announcement.published_at" class="text-xs text-muted-foreground">
                            {{ new Date(announcement.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                        </p>
                    </div>
                    <Badge :variant="announcementTypeVariants[announcement.type] ?? 'secondary'">
                        {{ announcementTypeLabels[announcement.type] ?? announcement.type }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-2">
                    <div v-if="announcement.body" class="announcement-body text-sm text-muted-foreground" v-html="announcement.body" />
                    <a v-if="announcement.link_url" :href="announcement.link_url" target="_blank" class="inline-block text-sm text-primary underline">
                        Selengkapnya
                    </a>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style>
.announcement-body p {
    margin-bottom: 0.5rem;
}
.announcement-body ul,
.announcement-body ol {
    margin: 0.5rem 0 0.5rem 1.25rem;
}
.announcement-body ul {
    list-style: disc;
}
.announcement-body ol {
    list-style: decimal;
}
.announcement-body a {
    color: hsl(var(--primary));
    text-decoration: underline;
}
</style>
