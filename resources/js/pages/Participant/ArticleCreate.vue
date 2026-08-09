<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

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

const selectClass =
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

function submit() {
    form.post(route('participant.articles.store', props.registration.id), { forceFormData: true });
}
</script>

<template>
    <Head title="Unggah Artikel" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-4 p-4">
            <h1 class="text-xl font-semibold">Unggah Artikel &mdash; {{ registration.registration_number }}</h1>

            <div
                :class="[
                    'rounded-md border px-4 py-3 text-sm',
                    deadlinePassed
                        ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300'
                        : 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300',
                ]"
            >
                Batas waktu pengumpulan artikel: <span class="font-medium">{{ formattedDeadline }}</span>
                <span v-if="deadlinePassed"> &mdash; batas waktu telah berakhir, pengumpulan artikel baru sudah ditutup.</span>
            </div>

            <div v-if="deadlinePassed" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                Batas waktu pengumpulan artikel sudah lewat. Hubungi panitia jika Anda memerlukan kelonggaran waktu.
            </div>
        </div>

        <form v-if="!deadlinePassed" class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4 pt-0" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="title">Judul Artikel</Label>
                <Input id="title" v-model="form.title" required />
                <InputError :message="form.errors.title" />
            </div>

            <div class="grid gap-2">
                <Label for="abstract">Abstrak</Label>
                <textarea id="abstract" v-model="form.abstract" rows="4" :class="selectClass" required></textarea>
                <InputError :message="form.errors.abstract" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="keywords">Kata Kunci</Label>
                    <Input id="keywords" v-model="form.keywords" placeholder="pisahkan dengan koma" required />
                    <InputError :message="form.errors.keywords" />
                </div>
                <div class="grid gap-2">
                    <Label for="field">Bidang Artikel</Label>
                    <Input id="field" v-model="form.field" required />
                    <InputError :message="form.errors.field" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="journal_id">Jurnal / Prosiding Tujuan</Label>
                <select id="journal_id" v-model="form.journal_id" :class="selectClass">
                    <option value="">Belum menentukan</option>
                    <option v-for="journal in journals" :key="journal.id" :value="journal.id">{{ journal.name }}</option>
                </select>
                <InputError :message="form.errors.journal_id" />
            </div>

            <div class="grid gap-2">
                <Label for="file">File Artikel (.doc, .docx, .pdf)</Label>
                <input
                    id="file"
                    type="file"
                    accept=".doc,.docx,.pdf"
                    :class="selectClass"
                    @change="form.file = ($event.target as HTMLInputElement).files?.[0] ?? null"
                />
                <InputError :message="form.errors.file" />
            </div>

            <div class="grid gap-2">
                <Label for="statement_letter">Surat Pernyataan Keaslian</Label>
                <input
                    id="statement_letter"
                    type="file"
                    accept=".doc,.docx,.pdf"
                    :class="selectClass"
                    @change="form.statement_letter = ($event.target as HTMLInputElement).files?.[0] ?? null"
                />
                <InputError :message="form.errors.statement_letter" />
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium leading-none">Penulis</p>
                    <Button type="button" variant="outline" size="sm" @click="addAuthor">Tambah Penulis</Button>
                </div>

                <div v-for="(author, index) in form.authors" :key="index" class="grid grid-cols-[1fr_1fr_auto] gap-2 rounded-md border p-3">
                    <Input v-model="author.name" placeholder="Nama penulis" required />
                    <Input v-model="author.email" type="email" placeholder="Email" required />
                    <Button v-if="form.authors.length > 1" type="button" variant="ghost" size="sm" @click="removeAuthor(index)">Hapus</Button>
                    <Input v-model="author.affiliation" placeholder="Afiliasi" class="col-span-2" />
                    <label class="flex items-center gap-2 text-xs">
                        <input v-model="author.is_corresponding" type="checkbox" class="h-4 w-4 rounded border-input" />
                        Corresponding
                    </label>
                </div>
            </div>

            <Button type="submit" :disabled="form.processing">Kirim Artikel</Button>
        </form>
    </AppLayout>
</template>
