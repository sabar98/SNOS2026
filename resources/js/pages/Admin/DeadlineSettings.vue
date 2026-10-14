<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { CalendarClock } from 'lucide-vue-next';

interface DeadlineSetting {
    article_submission_deadline: string;
    presentation_material_deadline: string;
}

const props = defineProps<{
    setting: DeadlineSetting;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Batas Waktu Upload', href: '/admin/deadline-settings' }];

const form = useForm({
    article_submission_deadline: props.setting.article_submission_deadline,
    presentation_material_deadline: props.setting.presentation_material_deadline,
});

function save() {
    form.put(route('admin.deadline-settings.update'), { preserveScroll: true });
}
</script>

<template>
    <Head title="Batas Waktu Upload" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <PageHeader
                :icon="CalendarClock"
                icon-class="bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400"
                title="Batas Waktu Upload"
                description="Atur rentang waktu peserta boleh mengunggah artikel/jurnal dan materi presentasi. Setelah batas waktu lewat, peserta tidak bisa mengunggah lagi."
            />

            <Card class="border-rose-100 bg-rose-50 dark:border-border dark:bg-rose-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2"><CalendarClock class="size-4 text-muted-foreground" /> Batas Waktu</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="grid gap-5" @submit.prevent="save">
                        <div class="grid gap-1.5">
                            <Label for="article_submission_deadline">Batas Waktu Upload Artikel/Jurnal</Label>
                            <Input
                                id="article_submission_deadline"
                                v-model="form.article_submission_deadline"
                                type="datetime-local"
                                required
                            />
                            <p class="text-xs text-muted-foreground">
                                Setelah tanggal dan jam ini, peserta tidak bisa lagi mengunggah artikel/jurnal baru.
                            </p>
                            <p v-if="form.errors.article_submission_deadline" class="text-sm text-destructive">
                                {{ form.errors.article_submission_deadline }}
                            </p>
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="presentation_material_deadline">Batas Waktu Upload Materi Presentasi</Label>
                            <Input
                                id="presentation_material_deadline"
                                v-model="form.presentation_material_deadline"
                                type="datetime-local"
                                required
                            />
                            <p class="text-xs text-muted-foreground">
                                Berlaku untuk slide, video, biodata singkat, dan foto resmi pemakalah yang sudah diterima (LoA).
                            </p>
                            <p v-if="form.errors.presentation_material_deadline" class="text-sm text-destructive">
                                {{ form.errors.presentation_material_deadline }}
                            </p>
                        </div>

                        <Button type="submit" size="sm" class="w-fit" :disabled="form.processing">Simpan</Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
