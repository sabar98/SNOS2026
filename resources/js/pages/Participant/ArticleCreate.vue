<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle2, FileText, FileUp, Paperclip, Plus, ScrollText, Trash2, Users, X } from 'lucide-vue-next';
import { onBeforeUnmount, ref } from 'vue';

interface Journal {
    id: number;
    name: string;
    type: string;
}

interface Registration {
    id: number;
    registration_number: string;
}

const props = defineProps<{
    registration: Registration;
    journals: Journal[];
    submissionDeadline: string;
    deadlinePassed: boolean;
}>();

const formattedDeadline = new Date(props.submissionDeadline).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Unggah Artikel', href: '#' }];

const inputClass =
    'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';

const form = useForm({
    journal_id: '',
    title: '',
    abstract: '',
    keywords: '',
    field: '',
    file: null as File | null,
    statement_letter: null as File | null,
    authors: [{ name: '', email: '', affiliation: '', is_corresponding: true }],
});

function addAuthor() {
    form.authors.push({ name: '', email: '', affiliation: '', is_corresponding: false });
}

function removeAuthor(index: number) {
    form.authors.splice(index, 1);
}

// Artikel PDF: preview di dalam halaman menggunakan object URL, tanpa membuka tab baru.
const articlePreviewUrl = ref<string | null>(null);
const articleFileError = ref('');
const articleFileInput = ref<HTMLInputElement | null>(null);

function revokeArticlePreview() {
    if (articlePreviewUrl.value) {
        URL.revokeObjectURL(articlePreviewUrl.value);
        articlePreviewUrl.value = null;
    }
}

function onArticleFileChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    revokeArticlePreview();
    articleFileError.value = '';

    if (!file) {
        form.file = null;
        return;
    }

    if (file.type !== 'application/pdf') {
        articleFileError.value = 'Berkas harus berformat PDF.';
        form.file = null;
        if (articleFileInput.value) articleFileInput.value.value = '';
        return;
    }

    form.file = file;
    articlePreviewUrl.value = URL.createObjectURL(file);
}

function clearArticleFile() {
    form.file = null;
    revokeArticlePreview();
    articleFileError.value = '';
    if (articleFileInput.value) articleFileInput.value.value = '';
}

function formatFileSize(bytes: number): string {
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

onBeforeUnmount(() => revokeArticlePreview());

const statementLetterName = ref('');

function onStatementLetterChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.statement_letter = file;
    statementLetterName.value = file?.name ?? '';
}

function submit() {
    form.post(route('participant.articles.store', props.registration.id), { forceFormData: true });
}
</script>

<template>
    <Head title="Unggah Artikel" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
            <PageHeader
                :icon="FileUp"
                icon-class="bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400"
                :title="`Unggah Artikel — ${registration.registration_number}`"
                description="Lengkapi data artikel, unggah berkas PDF, dan periksa pratinjaunya sebelum dikirim ke panitia."
            />

            <div
                :class="[
                    'flex items-start gap-3 rounded-xl border px-4 py-3 text-sm',
                    deadlinePassed
                        ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300'
                        : 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300',
                ]"
            >
                <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                <p>
                    Batas waktu pengumpulan artikel: <span class="font-medium">{{ formattedDeadline }}</span>
                    <span v-if="deadlinePassed"> &mdash; batas waktu telah berakhir, pengumpulan artikel baru sudah ditutup.</span>
                </p>
            </div>

            <div v-if="deadlinePassed" class="rounded-xl border border-dashed p-10 text-center text-muted-foreground">
                Batas waktu pengumpulan artikel sudah lewat. Hubungi panitia jika Anda memerlukan kelonggaran waktu.
            </div>
        </div>

        <form v-if="!deadlinePassed" class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 pt-0" @submit.prevent="submit">
            <Card class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <FileText class="size-4 text-violet-700 dark:text-violet-400" /> Informasi Artikel
                    </CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col gap-5">
                    <div class="grid gap-2">
                        <Label for="title">Judul Artikel</Label>
                        <Input id="title" v-model="form.title" required autofocus placeholder="Judul lengkap artikel" />
                        <InputError :message="form.errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="abstract">Abstrak</Label>
                        <textarea
                            id="abstract"
                            v-model="form.abstract"
                            rows="4"
                            placeholder="Ringkasan singkat isi artikel"
                            :class="inputClass"
                            required
                        ></textarea>
                        <InputError :message="form.errors.abstract" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="keywords">Kata Kunci</Label>
                            <Input id="keywords" v-model="form.keywords" placeholder="pisahkan dengan koma" required />
                            <InputError :message="form.errors.keywords" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="field">Bidang Artikel</Label>
                            <Input id="field" v-model="form.field" placeholder="mis. Teknologi Informasi" required />
                            <InputError :message="form.errors.field" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="journal_id">Jurnal / Prosiding Tujuan</Label>
                        <select id="journal_id" v-model="form.journal_id" :class="inputClass">
                            <option value="">Belum menentukan</option>
                            <option v-for="journal in journals" :key="journal.id" :value="journal.id">{{ journal.name }}</option>
                        </select>
                        <InputError :message="form.errors.journal_id" />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                <CardHeader>
                    <CardTitle class="flex flex-row flex-wrap items-center justify-between gap-3 text-base">
                        <span class="flex items-center gap-2"><Users class="size-4 text-violet-700 dark:text-violet-400" /> Penulis</span>
                        <Button type="button" variant="outline" size="sm" class="gap-1.5" @click="addAuthor">
                            <Plus class="size-3.5" /> Tambah Penulis
                        </Button>
                    </CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col gap-3">
                    <div v-for="(author, index) in form.authors" :key="index" class="rounded-lg border bg-background/60 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-sm font-medium text-muted-foreground">Penulis {{ index + 1 }}</p>
                            <Button
                                v-if="form.authors.length > 1"
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="gap-1.5 text-red-600 hover:text-red-700"
                                @click="removeAuthor(index)"
                            >
                                <Trash2 class="size-3.5" /> Hapus
                            </Button>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <Input v-model="author.name" placeholder="Nama penulis" required />
                            <Input v-model="author.email" type="email" placeholder="Email" required />
                            <Input v-model="author.affiliation" placeholder="Afiliasi" class="sm:col-span-2" />
                        </div>
                        <label class="mt-3 flex items-center gap-2 text-xs text-muted-foreground">
                            <input v-model="author.is_corresponding" type="checkbox" class="h-4 w-4 rounded border-input" />
                            Corresponding author
                        </label>
                    </div>
                </CardContent>
            </Card>

            <Card class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Paperclip class="size-4 text-violet-700 dark:text-violet-400" /> Berkas Artikel
                    </CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col gap-6">
                    <div class="grid gap-2">
                        <Label>File Artikel (PDF)</Label>

                        <div
                            v-if="!form.file"
                            class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-violet-200 bg-background/60 px-6 py-10 text-center dark:border-violet-900"
                        >
                            <span
                                class="flex size-11 items-center justify-center rounded-full bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400"
                            >
                                <FileUp class="size-5" />
                            </span>
                            <p class="text-sm font-medium">Pilih berkas PDF artikel Anda</p>
                            <p class="text-xs text-muted-foreground">Format PDF, maksimal 20 MB</p>
                            <label
                                for="file"
                                class="mt-1 inline-flex cursor-pointer items-center gap-1.5 rounded-md bg-violet-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-violet-700"
                            >
                                Pilih Berkas
                            </label>
                        </div>

                        <div v-else class="flex items-center justify-between gap-3 rounded-xl border bg-background/60 px-4 py-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400"
                                >
                                    <FileText class="size-5" />
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium">{{ form.file.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ formatFileSize(form.file.size) }}</p>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <Badge variant="success" class="gap-1"><CheckCircle2 class="size-3" /> Siap dikirim</Badge>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="gap-1.5 text-red-600 hover:text-red-700"
                                    @click="clearArticleFile"
                                >
                                    <X class="size-3.5" /> Ganti
                                </Button>
                            </div>
                        </div>

                        <input
                            id="file"
                            ref="articleFileInput"
                            type="file"
                            accept="application/pdf,.pdf"
                            class="sr-only"
                            @change="onArticleFileChange"
                        />
                        <InputError :message="articleFileError || form.errors.file" />

                        <div v-if="articlePreviewUrl" class="mt-2 overflow-hidden rounded-xl border">
                            <div class="flex items-center gap-2 border-b bg-muted/40 px-4 py-2 text-xs font-medium text-muted-foreground">
                                <FileText class="size-3.5" /> Pratinjau Berkas &mdash; periksa isi artikel sebelum mengirim
                            </div>
                            <iframe :src="articlePreviewUrl" class="h-[520px] w-full" title="Pratinjau artikel PDF"></iframe>
                        </div>
                    </div>

                    <div class="grid gap-2 border-t pt-6">
                        <Label for="statement_letter" class="flex items-center gap-1.5">
                            <ScrollText class="size-3.5 text-muted-foreground" /> Surat Pernyataan Keaslian (.doc, .docx, .pdf)
                        </Label>
                        <input id="statement_letter" type="file" accept=".doc,.docx,.pdf" :class="inputClass" @change="onStatementLetterChange" />
                        <InputError :message="form.errors.statement_letter" />
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-center gap-3">
                <Button type="submit" class="gap-2" :disabled="form.processing || !form.file"> <FileUp class="size-4" /> Kirim Artikel </Button>
                <Link :href="route('participant.registrations.show', registration.id)">
                    <Button type="button" variant="outline">Batal</Button>
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
