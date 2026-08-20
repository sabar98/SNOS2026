<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Crown, Eye, EyeOff, LoaderCircle, Lock, Mail, Save, User } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Leader {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    pimpinan?: Leader;
}>();

const isEdit = computed(() => !!props.pimpinan);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pimpinan', href: '/admin/pimpinan' },
    { title: isEdit.value ? 'Ubah Akun' : 'Tambah Akun', href: '#' },
];

const form = useForm({
    name: props.pimpinan?.name ?? '',
    email: props.pimpinan?.email ?? '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

function submit() {
    if (isEdit.value && props.pimpinan) {
        form.put(route('admin.pimpinan.update', props.pimpinan.id));
    } else {
        form.post(route('admin.pimpinan.store'));
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Ubah Pimpinan' : 'Tambah Pimpinan'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-lg flex-col gap-6 p-4">
            <Link
                :href="route('admin.pimpinan.index')"
                class="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="size-4" /> Kembali ke Daftar Pimpinan
            </Link>

            <PageHeader
                :icon="Crown"
                icon-class="bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400"
                :title="isEdit ? 'Ubah Akun Pimpinan' : 'Tambah Akun Pimpinan'"
                :description="isEdit ? `Perbarui data akun ${pimpinan?.name}.` : 'Buat akun baru dengan akses dashboard statistik SNOS 2026.'"
            />

            <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                <CardContent class="pt-6">
                    <form class="flex flex-col gap-5" @submit.prevent="submit">
                        <div class="grid gap-2">
                            <Label for="name">Nama</Label>
                            <div class="relative">
                                <User class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="name" v-model="form.name" required autofocus placeholder="Nama lengkap" class="pl-10" />
                            </div>
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <div class="relative">
                                <Mail class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="email" v-model="form.email" type="email" required placeholder="nama@email.com" class="pl-10" />
                            </div>
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password">{{ isEdit ? 'Kata Sandi Baru (opsional)' : 'Kata Sandi' }}</Label>
                            <div class="relative">
                                <Lock class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    :required="!isEdit"
                                    :placeholder="isEdit ? 'Kosongkan jika tidak diubah' : 'Buat kata sandi'"
                                    class="px-10"
                                />
                                <button
                                    type="button"
                                    tabindex="-1"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
                                    :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                    @click="showPassword = !showPassword"
                                >
                                    <EyeOff v-if="showPassword" class="size-4" />
                                    <Eye v-else class="size-4" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation">Konfirmasi Kata Sandi</Label>
                            <div class="relative">
                                <Lock class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    :required="!isEdit"
                                    placeholder="Ulangi kata sandi"
                                    class="px-10"
                                />
                                <button
                                    type="button"
                                    tabindex="-1"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
                                    :aria-label="showPasswordConfirmation ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                >
                                    <EyeOff v-if="showPasswordConfirmation" class="size-4" />
                                    <Eye v-else class="size-4" />
                                </button>
                            </div>
                        </div>

                        <div class="mt-2 flex items-center gap-3">
                            <Button type="submit" class="gap-2" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                                <Save v-else class="size-4" />
                                {{ isEdit ? 'Simpan Perubahan' : 'Buat Akun' }}
                            </Button>
                            <Link :href="route('admin.pimpinan.index')">
                                <Button type="button" variant="outline">Batal</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
