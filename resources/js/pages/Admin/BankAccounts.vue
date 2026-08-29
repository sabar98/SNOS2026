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
import { Banknote, CheckCircle2, Landmark, Pencil, Plus, Search, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface BankAccount {
    id: number;
    bank_name: string;
    account_number: string;
    account_holder: string;
    is_active: boolean;
}

const props = defineProps<{
    bankAccounts: BankAccount[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Bank Rekening', href: '/admin/bank-accounts' }];

const search = ref('');

const filteredBankAccounts = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return props.bankAccounts;
    return props.bankAccounts.filter(
        (bank) =>
            bank.bank_name.toLowerCase().includes(query) ||
            bank.account_number.toLowerCase().includes(query) ||
            bank.account_holder.toLowerCase().includes(query),
    );
});

const totalAktif = computed(() => props.bankAccounts.filter((bank) => bank.is_active).length);

const dialogOpen = ref(false);
const dialogMode = ref<'create' | 'edit'>('create');
const activeBankAccount = ref<BankAccount | null>(null);

const dialogForm = useForm({
    bank_name: '',
    account_number: '',
    account_holder: '',
    is_active: true,
});

function openCreateDialog() {
    dialogMode.value = 'create';
    activeBankAccount.value = null;
    dialogForm.reset();
    dialogForm.clearErrors();
    dialogOpen.value = true;
}

function openEditDialog(bank: BankAccount) {
    dialogMode.value = 'edit';
    activeBankAccount.value = bank;
    dialogForm.clearErrors();
    dialogForm.bank_name = bank.bank_name;
    dialogForm.account_number = bank.account_number;
    dialogForm.account_holder = bank.account_holder;
    dialogForm.is_active = bank.is_active;
    dialogOpen.value = true;
}

function submitDialog() {
    if (dialogMode.value === 'create') {
        dialogForm.post(route('admin.bank-accounts.store'), {
            preserveScroll: true,
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    } else if (activeBankAccount.value) {
        dialogForm.put(route('admin.bank-accounts.update', activeBankAccount.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    }
}

function destroy(bank: BankAccount) {
    if (!confirm(`Hapus rekening "${bank.bank_name} - ${bank.account_number}"?`)) {
        return;
    }
    router.delete(route('admin.bank-accounts.destroy', bank.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Bank Rekening" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="Landmark"
                icon-class="bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400"
                title="Bank Rekening"
                description="Kelola rekening tujuan pembayaran yang dapat dipilih peserta saat mendaftar."
            >
                <template #actions>
                    <Button class="gap-2" @click="openCreateDialog"><Plus class="size-4" /> Tambah Rekening</Button>
                </template>
            </PageHeader>

            <div class="grid gap-4 sm:grid-cols-2">
                <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400"
                        >
                            <Landmark class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ bankAccounts.length }}</p>
                            <p class="text-sm text-muted-foreground">Total Rekening</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400"
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

            <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <CardTitle class="flex items-center gap-2"><Banknote class="size-4 text-muted-foreground" /> Daftar Rekening</CardTitle>
                    <div class="relative w-full max-w-xs">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" placeholder="Cari nama bank, no. rekening, atau pemilik..." class="pl-9" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="w-10 py-2">No</th>
                                    <th>Nama Bank</th>
                                    <th>No. Rekening</th>
                                    <th>Nama Pemilik</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(bank, index) in filteredBankAccounts"
                                    :key="bank.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-3 text-muted-foreground">{{ index + 1 }}</td>
                                    <td class="font-medium">{{ bank.bank_name }}</td>
                                    <td class="font-mono text-muted-foreground">{{ bank.account_number }}</td>
                                    <td>{{ bank.account_holder }}</td>
                                    <td>
                                        <Badge :variant="bank.is_active ? 'success' : 'secondary'">
                                            {{ bank.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="outline" size="sm" class="gap-1.5" @click="openEditDialog(bank)">
                                                <Pencil class="size-3.5" /> Edit
                                            </Button>
                                            <Button variant="destructive" size="sm" class="gap-1.5" @click="destroy(bank)">
                                                <Trash2 class="size-3.5" /> Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredBankAccounts.length === 0">
                                    <td colspan="6" class="py-10 text-center text-muted-foreground">
                                        {{ search ? 'Tidak ada rekening yang cocok dengan pencarian.' : 'Belum ada rekening bank.' }}
                                    </td>
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
                    <DialogTitle>{{ dialogMode === 'create' ? 'Tambah Rekening' : 'Ubah Rekening' }}</DialogTitle>
                    <DialogDescription>
                        {{ dialogMode === 'create' ? 'Tambahkan rekening tujuan pembayaran baru.' : `${activeBankAccount?.bank_name}` }}
                    </DialogDescription>
                </DialogHeader>

                <form class="grid gap-4" @submit.prevent="submitDialog">
                    <div class="grid gap-1.5">
                        <Label for="bank_name">Nama Bank</Label>
                        <Input id="bank_name" v-model="dialogForm.bank_name" required placeholder="BCA, BNI, Mandiri, dsb." />
                        <p v-if="dialogForm.errors.bank_name" class="text-sm text-destructive">{{ dialogForm.errors.bank_name }}</p>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="account_number">No. Rekening</Label>
                        <Input id="account_number" v-model="dialogForm.account_number" required placeholder="1234567890" />
                        <p v-if="dialogForm.errors.account_number" class="text-sm text-destructive">{{ dialogForm.errors.account_number }}</p>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="account_holder">Nama Pemilik</Label>
                        <Input id="account_holder" v-model="dialogForm.account_holder" required placeholder="Panitia SNOS 2026" />
                        <p v-if="dialogForm.errors.account_holder" class="text-sm text-destructive">{{ dialogForm.errors.account_holder }}</p>
                    </div>

                    <Label v-if="dialogMode === 'edit'" for="bank_is_active" class="flex w-fit items-center gap-2.5 text-sm font-normal">
                        <Checkbox id="bank_is_active" v-model:checked="dialogForm.is_active" />
                        <span>Aktif &mdash; tampil sebagai pilihan rekening bagi peserta</span>
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
