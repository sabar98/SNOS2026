<script setup lang="ts">
import DonutChart from '@/components/charts/DonutChart.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { BookOpen, ExternalLink, FilePlus, FileText, Newspaper, Pencil, Search, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Journal {
    id: number;
    name: string;
    type: string;
    publisher: string | null;
    issn: string | null;
    website_url: string | null;
    publication_fee: string;
    description: string | null;
    is_active: boolean;
}

const props = defineProps<{
    journals: Journal[];
    articlesByJournal: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Jurnal & Prosiding', href: '/admin/journals' }];

const journalChartData = computed(() => Object.entries(props.articlesByJournal).map(([label, value]) => ({ label, value })));

const search = ref('');

const filteredJournals = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return props.journals;
    return props.journals.filter(
        (journal) =>
            journal.name.toLowerCase().includes(query) ||
            journal.type.toLowerCase().includes(query) ||
            (journal.publisher?.toLowerCase().includes(query) ?? false),
    );
});

const totalJurnal = computed(() => props.journals.filter((journal) => journal.type === 'jurnal').length);
const totalProsiding = computed(() => props.journals.filter((journal) => journal.type === 'prosiding').length);

function formatFee(fee: string): string {
    return `Rp${Number(fee).toLocaleString('id-ID')}`;
}

const dialogOpen = ref(false);
const dialogMode = ref<'create' | 'edit'>('create');
const activeJournal = ref<Journal | null>(null);

const dialogForm = useForm({
    name: '',
    type: 'jurnal',
    publisher: '',
    issn: '',
    website_url: '',
    publication_fee: 0,
    description: '',
    is_active: true,
});

function openCreateDialog() {
    dialogMode.value = 'create';
    activeJournal.value = null;
    dialogForm.reset();
    dialogForm.clearErrors();
    dialogOpen.value = true;
}

function openEditDialog(journal: Journal) {
    dialogMode.value = 'edit';
    activeJournal.value = journal;
    dialogForm.clearErrors();
    dialogForm.name = journal.name;
    dialogForm.type = journal.type;
    dialogForm.publisher = journal.publisher ?? '';
    dialogForm.issn = journal.issn ?? '';
    dialogForm.website_url = journal.website_url ?? '';
    dialogForm.publication_fee = Number(journal.publication_fee);
    dialogForm.description = journal.description ?? '';
    dialogForm.is_active = journal.is_active;
    dialogOpen.value = true;
}

function submitDialog() {
    if (dialogMode.value === 'create') {
        dialogForm.post(route('admin.journals.store'), {
            preserveScroll: true,
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    } else if (activeJournal.value) {
        dialogForm.put(route('admin.journals.update', activeJournal.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    }
}

function destroy(journal: Journal) {
    if (!confirm(`Hapus "${journal.name}" dari daftar jurnal & prosiding?`)) {
        return;
    }
    router.delete(route('admin.journals.destroy', journal.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Jurnal & Prosiding" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="Newspaper"
                icon-class="bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400"
                title="Jurnal & Prosiding"
                description="Kelola daftar jurnal dan prosiding tujuan publikasi artikel."
            >
                <template #actions>
                    <Button class="gap-2" @click="openCreateDialog"><FilePlus class="size-4" /> Tambah Jurnal / Prosiding</Button>
                </template>
            </PageHeader>

            <div class="grid gap-4 sm:grid-cols-3">
                <Card class="border-indigo-100 bg-indigo-50 dark:border-border dark:bg-indigo-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400"
                        >
                            <Newspaper class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ journals.length }}</p>
                            <p class="text-sm text-muted-foreground">Total Entri</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400"
                        >
                            <BookOpen class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ totalJurnal }}</p>
                            <p class="text-sm text-muted-foreground">Jurnal</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400"
                        >
                            <FileText class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ totalProsiding }}</p>
                            <p class="text-sm text-muted-foreground">Prosiding</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card class="border-indigo-100 bg-indigo-50 dark:border-border dark:bg-indigo-950/40">
                <CardContent class="pt-6">
                    <DonutChart title="Artikel per Jurnal Tujuan" :data="journalChartData" />
                </CardContent>
            </Card>

            <Card class="border-indigo-100 bg-indigo-50 dark:border-border dark:bg-indigo-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <CardTitle class="flex items-center gap-2"
                        ><Newspaper class="size-4 text-muted-foreground" /> Daftar Jurnal & Prosiding</CardTitle
                    >
                    <div class="relative w-full max-w-xs">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" placeholder="Cari nama, tipe, atau penerbit..." class="pl-9" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="w-10 py-2">No</th>
                                    <th>Nama</th>
                                    <th>Tipe</th>
                                    <th>Penerbit</th>
                                    <th>Biaya</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(journal, index) in filteredJournals"
                                    :key="journal.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-3 text-muted-foreground">{{ index + 1 }}</td>
                                    <td class="max-w-[220px]">
                                        <p class="font-medium">{{ journal.name }}</p>
                                        <a
                                            v-if="journal.website_url"
                                            :href="journal.website_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
                                        >
                                            <ExternalLink class="size-3" /> Situs web
                                        </a>
                                    </td>
                                    <td>
                                        <Badge variant="secondary" class="capitalize">{{ journal.type }}</Badge>
                                    </td>
                                    <td class="text-muted-foreground">{{ journal.publisher ?? '—' }}</td>
                                    <td class="text-muted-foreground">{{ formatFee(journal.publication_fee) }}</td>
                                    <td>
                                        <Badge :variant="journal.is_active ? 'success' : 'secondary'">
                                            {{ journal.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="outline" size="sm" class="gap-1.5" @click="openEditDialog(journal)">
                                                <Pencil class="size-3.5" /> Edit
                                            </Button>
                                            <Button variant="destructive" size="sm" class="gap-1.5" @click="destroy(journal)">
                                                <Trash2 class="size-3.5" /> Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredJournals.length === 0">
                                    <td colspan="7" class="py-10 text-center text-muted-foreground">
                                        {{ search ? 'Tidak ada entri yang cocok dengan pencarian.' : 'Belum ada jurnal atau prosiding.' }}
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
                    <DialogTitle>{{ dialogMode === 'create' ? 'Tambah Jurnal / Prosiding' : 'Ubah Jurnal / Prosiding' }}</DialogTitle>
                    <DialogDescription>
                        {{ dialogMode === 'create' ? 'Tambahkan tujuan publikasi baru untuk artikel peserta.' : activeJournal?.name }}
                    </DialogDescription>
                </DialogHeader>

                <form class="grid gap-4" @submit.prevent="submitDialog">
                    <div class="grid gap-1.5">
                        <Label for="journal_name">Nama</Label>
                        <Input id="journal_name" v-model="dialogForm.name" required placeholder="Jurnal Ilmiah Teknologi Terapan" />
                        <p v-if="dialogForm.errors.name" class="text-sm text-destructive">{{ dialogForm.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="journal_type">Tipe</Label>
                            <select
                                id="journal_type"
                                v-model="dialogForm.type"
                                class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                            >
                                <option value="jurnal">Jurnal</option>
                                <option value="prosiding">Prosiding</option>
                            </select>
                            <p v-if="dialogForm.errors.type" class="text-sm text-destructive">{{ dialogForm.errors.type }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="journal_fee">Biaya Publikasi (Rp)</Label>
                            <Input id="journal_fee" v-model.number="dialogForm.publication_fee" type="number" min="0" required />
                            <p v-if="dialogForm.errors.publication_fee" class="text-sm text-destructive">{{ dialogForm.errors.publication_fee }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="journal_publisher">Penerbit</Label>
                            <Input id="journal_publisher" v-model="dialogForm.publisher" placeholder="Nama penerbit" />
                            <p v-if="dialogForm.errors.publisher" class="text-sm text-destructive">{{ dialogForm.errors.publisher }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="journal_issn">ISSN</Label>
                            <Input id="journal_issn" v-model="dialogForm.issn" placeholder="xxxx-xxxx" />
                            <p v-if="dialogForm.errors.issn" class="text-sm text-destructive">{{ dialogForm.errors.issn }}</p>
                        </div>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="journal_website">Situs Web</Label>
                        <Input id="journal_website" v-model="dialogForm.website_url" type="url" placeholder="https://" />
                        <p v-if="dialogForm.errors.website_url" class="text-sm text-destructive">{{ dialogForm.errors.website_url }}</p>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="journal_description">Deskripsi</Label>
                        <textarea
                            id="journal_description"
                            v-model="dialogForm.description"
                            rows="3"
                            placeholder="Ruang lingkup atau keterangan singkat..."
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                        ></textarea>
                        <p v-if="dialogForm.errors.description" class="text-sm text-destructive">{{ dialogForm.errors.description }}</p>
                    </div>

                    <Label v-if="dialogMode === 'edit'" for="journal_is_active" class="flex w-fit items-center gap-2.5 text-sm font-normal">
                        <Checkbox id="journal_is_active" v-model:checked="dialogForm.is_active" />
                        <span>Aktif &mdash; tampil sebagai pilihan tujuan publikasi</span>
                    </Label>

                    <DialogFooter>
                        <Button type="submit" :disabled="dialogForm.processing">
                            {{ dialogMode === 'create' ? 'Simpan' : 'Simpan Perubahan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
