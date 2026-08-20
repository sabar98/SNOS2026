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
import { ArrowLeft, Eye, EyeOff, LoaderCircle, Lock, Mail, Presentation, Save, User } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Speaker {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    narasumber?: Speaker;
}>();

const isEdit = computed(() => !!props.narasumber);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Narasumber', href: '/admin/narasumber' },
    { title: isEdit.value ? 'Ubah Akun' : 'Tambah Akun', href: '#' },
];

const form = useForm({
    name: props.narasumber?.name ?? '',
    email: props.narasumber?.email ?? '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

function submit() {
    if (isEdit.value && props.narasumber) {
        form.put(route('admin.narasumber.update', props.narasumber.id));
    } else {
        form.post(route('admin.narasumber.store'));
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Ubah Narasumber' : 'Tambah Narasumber'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-lg flex-col gap-6 p-4">
            <Link
                :href="route('admin.narasumber.index')"
                class="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="size-4" /> Kembali ke Daftar Narasumber
            </Link>

            <PageHeader
                :icon="Presentation"
                icon-class="bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400"
                :title="isEdit ? 'Ubah Akun Narasumber' : 'Tambah Akun Narasumber'"
                :description="isEdit ? `Perbarui data akun ${narasumber?.name}.` : 'Buat akun baru untuk narasumber / pembicara tamu SNOS 2026.'"
            />

            <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
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
                            <Link :href="route('admin.narasumber.index')">
                                <Button type="button" variant="outline">Batal</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
