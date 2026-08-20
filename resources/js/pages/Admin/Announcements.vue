<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import RichTextEditor from '@/components/RichTextEditor.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { announcementTypeLabels, announcementTypeVariants } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Megaphone, PenSquare } from 'lucide-vue-next';
import { ref } from 'vue';

interface Announcement {
    id: number;
    type: string;
    title: string;
    body: string | null;
    published_at: string | null;
    file_path: string | null;
}

defineProps<{
    announcements: Announcement[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Pengumuman', href: '/admin/announcements' }];
const selectClass = 'flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm';

const form = useForm({
    type: 'pengumuman',
    title: '',
    body: '',
    link_url: '',
    published_at: new Date().toISOString().slice(0, 10),
    thumbnail: null as File | null,
});

const thumbnailPreview = ref<string | null>(null);
const thumbnailInput = ref<HTMLInputElement>();

function onThumbnailChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.thumbnail = file;

    if (thumbnailPreview.value) {
        URL.revokeObjectURL(thumbnailPreview.value);
    }
    thumbnailPreview.value = file ? URL.createObjectURL(file) : null;
}

function submit() {
    form.post(route('admin.announcements.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            thumbnailPreview.value = null;
            if (thumbnailInput.value) {
                thumbnailInput.value.value = '';
            }
        },
    });
}
</script>

<template>
    <Head title="Pengumuman" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <PageHeader :icon="Megaphone" title="Pengumuman" description="Publikasikan berita dan pengumuman untuk peserta SNOS 2026." />

            <Card class="border-rose-100 bg-rose-50 dark:border-border dark:bg-rose-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2"><PenSquare class="size-4 text-muted-foreground" /> Buat Pengumuman</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="grid gap-3" @submit.prevent="submit">
                        <select v-model="form.type" aria-label="Jenis pengumuman" :class="selectClass">
                            <option value="pengumuman">Pengumuman</option>
                            <option value="dokumentasi">Dokumentasi</option>
                            <option value="best_paper">Best Paper</option>
                            <option value="best_presenter">Best Presenter</option>
                            <option value="best_poster">Best Poster</option>
                        </select>
                        <Input v-model="form.title" placeholder="Judul" required />

                        <RichTextEditor v-model="form.body" placeholder="Isi pengumuman..." />

                        <div class="grid gap-1.5">
                            <Label for="published_at">Tanggal Pengumuman</Label>
                            <Input id="published_at" v-model="form.published_at" type="date" required />
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="thumbnail">Thumbnail (opsional)</Label>
                            <input
                                id="thumbnail"
                                ref="thumbnailInput"
                                type="file"
                                accept="image/*"
                                class="text-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-secondary-foreground"
                                @change="onThumbnailChange"
                            />
                            <img
                                v-if="thumbnailPreview"
                                :src="thumbnailPreview"
                                alt="Pratinjau thumbnail"
                                class="mt-1 h-32 w-full rounded-md object-cover"
                            />
                            <p v-if="form.errors.thumbnail" class="text-sm text-destructive">{{ form.errors.thumbnail }}</p>
                        </div>

                        <Input v-model="form.link_url" placeholder="Link (opsional)" />
                        <Button type="submit" :disabled="form.processing">Publikasikan</Button>
                    </form>
                </CardContent>
            </Card>

            <EmptyState
                v-if="announcements.length === 0"
                :icon="Megaphone"
                title="Belum ada pengumuman"
                description="Pengumuman yang dipublikasikan akan tampil di sini."
            />

            <Card
                v-for="announcement in announcements"
                :key="announcement.id"
                class="overflow-hidden border-rose-100 bg-rose-50 transition-shadow duration-200 hover:shadow-md dark:border-border dark:bg-rose-950/40"
            >
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
                <CardContent v-if="announcement.body">
                    <div class="announcement-body text-sm text-muted-foreground" v-html="announcement.body" />
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
