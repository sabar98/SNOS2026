<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { attendanceMethodLabels } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Coins, Pencil, Plus, Trash2, TrendingUp } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface RegistrationFeeRow {
    id: number;
    participant_type: string;
    attendance_method: string;
    amount: number;
}

interface ParticipantCategoryOption {
    key: string;
    label: string;
    is_active: boolean;
}

const props = defineProps<{
    registrationFees: RegistrationFeeRow[];
    participantCategories: ParticipantCategoryOption[];
    attendanceMethods: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Biaya Pendaftaran', href: '/admin/registration-fees' }];

const selectClass =
    'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';

function formatRupiah(amount: number): string {
    return `Rp${amount.toLocaleString('id-ID')}`;
}

function categoryLabel(key: string): string {
    return props.participantCategories.find((category: ParticipantCategoryOption) => category.key === key)?.label ?? key;
}

const highestFee = computed(() => (props.registrationFees.length ? Math.max(...props.registrationFees.map((fee) => fee.amount)) : 0));

const dialogOpen = ref(false);
const dialogMode = ref<'create' | 'edit'>('create');
const activeFee = ref<RegistrationFeeRow | null>(null);

const dialogForm = useForm({
    participant_type: '',
    attendance_method: '',
    amount: 0,
});

function openCreateDialog() {
    dialogMode.value = 'create';
    activeFee.value = null;
    dialogForm.reset();
    dialogForm.clearErrors();
    dialogOpen.value = true;
}

function openEditDialog(fee: RegistrationFeeRow) {
    dialogMode.value = 'edit';
    activeFee.value = fee;
    dialogForm.clearErrors();
    dialogForm.participant_type = fee.participant_type;
    dialogForm.attendance_method = fee.attendance_method;
    dialogForm.amount = fee.amount;
    dialogOpen.value = true;
}

function submitDialog() {
    if (dialogMode.value === 'create') {
        dialogForm.post(route('admin.registration-fees.store'), {
            preserveScroll: true,
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    } else if (activeFee.value) {
        dialogForm.put(route('admin.registration-fees.update', activeFee.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    }
}

function destroy(fee: RegistrationFeeRow) {
    const label = `${categoryLabel(fee.participant_type)} - ${attendanceMethodLabels[fee.attendance_method] ?? fee.attendance_method}`;
    if (!confirm(`Hapus aturan biaya "${label}"?`)) {
        return;
    }
    router.delete(route('admin.registration-fees.destroy', fee.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Biaya Pendaftaran" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="Coins"
                icon-class="bg-lime-100 text-lime-700 dark:bg-lime-950/60 dark:text-lime-400"
                title="Biaya Pendaftaran"
                description="Atur biaya pendaftaran berdasarkan jenis kepesertaan dan metode kehadiran yang dipilih peserta."
            >
                <template #actions>
                    <Button class="gap-2" @click="openCreateDialog"><Plus class="size-4" /> Tambah Aturan Biaya</Button>
                </template>
            </PageHeader>

            <div class="grid gap-4 sm:grid-cols-2">
                <Card class="border-lime-100 bg-lime-50 dark:border-border dark:bg-lime-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-lime-100 text-lime-700 dark:bg-lime-950/60 dark:text-lime-400"
                        >
                            <Coins class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ registrationFees.length }}</p>
                            <p class="text-sm text-muted-foreground">Total Aturan Biaya</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-lime-100 bg-lime-50 dark:border-border dark:bg-lime-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-lime-100 text-lime-700 dark:bg-lime-950/60 dark:text-lime-400"
                        >
                            <TrendingUp class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ formatRupiah(highestFee) }}</p>
                            <p class="text-sm text-muted-foreground">Biaya Tertinggi</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card class="border-lime-100 bg-lime-50 dark:border-border dark:bg-lime-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2"><Coins class="size-4 text-muted-foreground" /> Daftar Biaya</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="w-10 py-2">No</th>
                                    <th>Jenis Kepesertaan</th>
                                    <th>Metode Kehadiran</th>
                                    <th>Biaya</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(fee, index) in registrationFees"
                                    :key="fee.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-3 text-muted-foreground">{{ index + 1 }}</td>
                                    <td class="font-medium">{{ categoryLabel(fee.participant_type) }}</td>
                                    <td>{{ attendanceMethodLabels[fee.attendance_method] ?? fee.attendance_method }}</td>
                                    <td class="font-semibold text-lime-700 dark:text-lime-400">{{ formatRupiah(fee.amount) }}</td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="outline" size="sm" class="gap-1.5" @click="openEditDialog(fee)">
                                                <Pencil class="size-3.5" /> Edit
                                            </Button>
                                            <Button variant="destructive" size="sm" class="gap-1.5" @click="destroy(fee)">
                                                <Trash2 class="size-3.5" /> Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="registrationFees.length === 0">
                                    <td colspan="5" class="py-10 text-center text-muted-foreground">Belum ada aturan biaya pendaftaran.</td>
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
                    <DialogTitle>{{ dialogMode === 'create' ? 'Tambah Aturan Biaya' : 'Ubah Aturan Biaya' }}</DialogTitle>
                    <DialogDescription>
                        Tentukan biaya pendaftaran untuk kombinasi jenis kepesertaan dan metode kehadiran tertentu.
                    </DialogDescription>
                </DialogHeader>

                <form class="grid gap-4" @submit.prevent="submitDialog">
                    <div class="grid gap-1.5">
                        <Label for="participant_type">Jenis Kepesertaan</Label>
                        <select id="participant_type" v-model="dialogForm.participant_type" required :class="selectClass">
                            <option value="" disabled>Pilih jenis kepesertaan</option>
                            <option v-for="category in participantCategories" :key="category.key" :value="category.key">
                                {{ category.label }}{{ category.is_active ? '' : ' (nonaktif)' }}
                            </option>
                        </select>
                        <p v-if="dialogForm.errors.participant_type" class="text-sm text-destructive">{{ dialogForm.errors.participant_type }}</p>
                        <p v-if="participantCategories.length === 0" class="text-sm text-muted-foreground">
                            Belum ada kategori peserta. Tambahkan dahulu di menu Kategori Peserta.
                        </p>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="attendance_method">Metode Kehadiran</Label>
                        <select id="attendance_method" v-model="dialogForm.attendance_method" required :class="selectClass">
                            <option value="" disabled>Pilih metode kehadiran</option>
                            <option v-for="method in attendanceMethods" :key="method" :value="method">
                                {{ attendanceMethodLabels[method] ?? method }}
                            </option>
                        </select>
                        <p v-if="dialogForm.errors.attendance_method" class="text-sm text-destructive">{{ dialogForm.errors.attendance_method }}</p>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="amount">Biaya (Rp)</Label>
                        <Input id="amount" v-model.number="dialogForm.amount" type="number" min="0" step="1000" required placeholder="100000" />
                        <p v-if="dialogForm.errors.amount" class="text-sm text-destructive">{{ dialogForm.errors.amount }}</p>
                    </div>

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
