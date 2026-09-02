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
import { ArrowLeft, Building2, Eye, EyeOff, IdCard, LoaderCircle, Lock, Mail, Phone, Save, User } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface ParticipantAccount {
    id: number;
    name: string;
    email: string;
    nik: string | null;
    institution: string | null;
    whatsapp_number: string | null;
}

const props = defineProps<{
    participant?: ParticipantAccount;
}>();

const isEdit = computed(() => !!props.participant);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Akun Peserta', href: '/admin/participant-accounts' },
    { title: isEdit.value ? 'Ubah Akun' : 'Tambah Akun', href: '#' },
];

const form = useForm({
    name: props.participant?.name ?? '',
    nik: props.participant?.nik ?? '',
    institution: props.participant?.institution ?? '',
    whatsapp_number: props.participant?.whatsapp_number ?? '',
    email: props.participant?.email ?? '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

function submit() {
    if (isEdit.value && props.participant) {
        form.put(route('admin.participant-accounts.update', props.participant.id));
    } else {
        form.post(route('admin.participant-accounts.store'));
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Ubah Akun Peserta' : 'Tambah Akun Peserta'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-lg flex-col gap-6 p-4">
            <Link
                :href="route('admin.participant-accounts.index')"
                class="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="size-4" /> Kembali ke Daftar Akun Peserta
            </Link>

            <PageHeader
                :icon="IdCard"
                icon-class="bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"
                :title="isEdit ? 'Ubah Akun Peserta' : 'Tambah Akun Peserta'"
                :description="isEdit ? `Perbarui data akun ${participant?.name}.` : 'Buat akun peserta baru secara manual.'"
            />

            <Card class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
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
                            <Label for="nik">NIDN/NUPK/NPM</Label>
                            <div class="relative">
                                <IdCard class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="nik" v-model="form.nik" required placeholder="NIDN/NUPK/NPM" class="pl-10" />
                            </div>
                            <InputError :message="form.errors.nik" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="institution">Perguruan Tinggi / Instansi</Label>
                            <div class="relative">
                                <Building2 class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="institution" v-model="form.institution" required placeholder="Universitas Contoh" class="pl-10" />
                            </div>
                            <InputError :message="form.errors.institution" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="whatsapp_number">Nomor WhatsApp</Label>
                            <div class="relative">
                                <Phone class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="whatsapp_number" v-model="form.whatsapp_number" required placeholder="08xxxxxxxxxx" class="pl-10" />
                            </div>
                            <InputError :message="form.errors.whatsapp_number" />
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
                            <Link :href="route('admin.participant-accounts.index')">
                                <Button type="button" variant="outline">Batal</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
