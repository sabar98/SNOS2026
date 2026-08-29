<script setup lang="ts">
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
import { CheckCircle2, Mic2, Pencil, Plus, Tags, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface ParticipantCategoryRow {
    id: number;
    key: string;
    label: string;
    golongan: string;
    is_presenter: boolean;
    is_active: boolean;
}

const props = defineProps<{
    participantCategories: ParticipantCategoryRow[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Kategori Peserta', href: '/admin/participant-categories' }];

const golonganOptions = [
    { value: 'umum', label: 'Umum' },
    { value: 'dosen', label: 'Dosen' },
    { value: 'mahasiswa', label: 'Mahasiswa' },
];
const golonganLabels: Record<string, string> = Object.fromEntries(golonganOptions.map((option) => [option.value, option.label]));

const totalPresenter = computed(() => props.participantCategories.filter((category) => category.is_presenter).length);
const totalAktif = computed(() => props.participantCategories.filter((category) => category.is_active).length);

const dialogOpen = ref(false);
const dialogMode = ref<'create' | 'edit'>('create');
const activeCategory = ref<ParticipantCategoryRow | null>(null);

const dialogForm = useForm({
    key: '',
    label: '',
    golongan: 'umum',
    is_presenter: false,
    is_active: true,
});

function openCreateDialog() {
    dialogMode.value = 'create';
    activeCategory.value = null;
    dialogForm.reset();
    dialogForm.clearErrors();
    dialogOpen.value = true;
}

function openEditDialog(category: ParticipantCategoryRow) {
    dialogMode.value = 'edit';
    activeCategory.value = category;
    dialogForm.clearErrors();
    dialogForm.key = category.key;
    dialogForm.label = category.label;
    dialogForm.golongan = category.golongan;
    dialogForm.is_presenter = category.is_presenter;
    dialogForm.is_active = category.is_active;
    dialogOpen.value = true;
}

function submitDialog() {
    if (dialogMode.value === 'create') {
        dialogForm.post(route('admin.participant-categories.store'), {
            preserveScroll: true,
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    } else if (activeCategory.value) {
        dialogForm.put(route('admin.participant-categories.update', activeCategory.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    }
}

function destroy(category: ParticipantCategoryRow) {
    if (!confirm(`Hapus kategori "${category.label}"? Kategori yang masih dipakai tidak dapat dihapus.`)) {
        return;
    }
    router.delete(route('admin.participant-categories.destroy', category.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Kategori Peserta" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="Tags"
                icon-class="bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-950/60 dark:text-fuchsia-400"
                title="Kategori Peserta"
                description="Kelola jenis kepesertaan yang bisa dipilih saat pendaftaran, penetapan biaya, dan kewajiban unggah artikel."
            >
                <template #actions>
                    <Button class="gap-2" @click="openCreateDialog"><Plus class="size-4" /> Tambah Kategori</Button>
                </template>
            </PageHeader>

            <div class="grid gap-4 sm:grid-cols-3">
                <Card class="border-fuchsia-100 bg-fuchsia-50 dark:border-border dark:bg-fuchsia-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-950/60 dark:text-fuchsia-400"
                        >
                            <Tags class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ participantCategories.length }}</p>
                            <p class="text-sm text-muted-foreground">Total Kategori</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-fuchsia-100 bg-fuchsia-50 dark:border-border dark:bg-fuchsia-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-950/60 dark:text-fuchsia-400"
                        >
                            <Mic2 class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ totalPresenter }}</p>
                            <p class="text-sm text-muted-foreground">Kategori Presenter</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-fuchsia-100 bg-fuchsia-50 dark:border-border dark:bg-fuchsia-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-950/60 dark:text-fuchsia-400"
                        >
                            <CheckCircle2 class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ totalAktif }}</p>
                            <p class="text-sm text-muted-foreground">Aktif (Tampil ke Peserta)</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card class="border-fuchsia-100 bg-fuchsia-50 dark:border-border dark:bg-fuchsia-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2"><Tags class="size-4 text-muted-foreground" /> Daftar Kategori</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="w-10 py-2">No</th>
                                    <th>Kode</th>
                                    <th>Label</th>
                                    <th>Golongan</th>
                                    <th>Presenter</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(category, index) in participantCategories"
                                    :key="category.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-3 text-muted-foreground">{{ index + 1 }}</td>
                                    <td class="font-mono text-muted-foreground">{{ category.key }}</td>
                                    <td class="font-medium">{{ category.label }}</td>
                                    <td>{{ golonganLabels[category.golongan] ?? category.golongan }}</td>
                                    <td>
                                        <Badge :variant="category.is_presenter ? 'info' : 'secondary'">
                                            {{ category.is_presenter ? 'Ya' : 'Tidak' }}
                                        </Badge>
                                    </td>
                                    <td>
                                        <Badge :variant="category.is_active ? 'success' : 'secondary'">
                                            {{ category.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="outline" size="sm" class="gap-1.5" @click="openEditDialog(category)">
                                                <Pencil class="size-3.5" /> Edit
                                            </Button>
                                            <Button variant="destructive" size="sm" class="gap-1.5" @click="destroy(category)">
                                                <Trash2 class="size-3.5" /> Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="participantCategories.length === 0">
                                    <td colspan="7" class="py-10 text-center text-muted-foreground">Belum ada kategori peserta.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ dialogMode === 'create' ? 'Tambah Kategori' : 'Ubah Kategori' }}</DialogTitle>
                    <DialogDescription>
                        {{ dialogMode === 'create' ? 'Tambahkan jenis kepesertaan baru.' : `${activeCategory?.label}` }}
                    </DialogDescription>
                </DialogHeader>

                <form class="grid gap-4" @submit.prevent="submitDialog">
                    <div class="grid gap-1.5">
                        <Label for="key">Kode</Label>
                        <Input id="key" v-model="dialogForm.key" required :disabled="dialogMode === 'edit'" placeholder="mis. peserta_alumni" />
                        <p class="text-xs text-muted-foreground">
                            Huruf kecil, angka, dan garis bawah saja. Dipakai sebagai kode teknis dan tidak dapat diubah setelah dibuat.
                        </p>
                        <p v-if="dialogForm.errors.key" class="text-sm text-destructive">{{ dialogForm.errors.key }}</p>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="label">Label</Label>
                        <Input id="label" v-model="dialogForm.label" required placeholder="Peserta Alumni" />
                        <p v-if="dialogForm.errors.label" class="text-sm text-destructive">{{ dialogForm.errors.label }}</p>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="golongan">Golongan</Label>
                        <select
                            id="golongan"
                            v-model="dialogForm.golongan"
                            required
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            <option v-for="option in golonganOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <p v-if="dialogForm.errors.golongan" class="text-sm text-destructive">{{ dialogForm.errors.golongan }}</p>
                    </div>

                    <Label for="cat_is_presenter" class="flex w-fit items-center gap-2.5 text-sm font-normal">
                        <Checkbox id="cat_is_presenter" v-model:checked="dialogForm.is_presenter" />
                        <span>Kategori presenter &mdash; wajib mengunggah artikel dan materi presentasi</span>
                    </Label>

                    <Label v-if="dialogMode === 'edit'" for="cat_is_active" class="flex w-fit items-center gap-2.5 text-sm font-normal">
                        <Checkbox id="cat_is_active" v-model:checked="dialogForm.is_active" />
                        <span>Aktif &mdash; tampil sebagai pilihan bagi peserta baru</span>
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
