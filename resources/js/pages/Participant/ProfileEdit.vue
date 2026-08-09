<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

interface Profile {
    title_prefix?: string;
    title_suffix?: string;
    gender?: string;
    address?: string;
    institution?: string;
    study_program?: string;
}

const props = defineProps<{
    profile: Profile | null;
    whatsappNumber: string | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Lengkapi Profil', href: '/participant/profile' }];

const form = useForm({
    title_prefix: props.profile?.title_prefix ?? '',
    title_suffix: props.profile?.title_suffix ?? '',
    gender: props.profile?.gender ?? '',
    address: props.profile?.address ?? '',
    institution: props.profile?.institution ?? '',
    study_program: props.profile?.study_program ?? '',
    whatsapp_number: props.whatsappNumber ?? '',
    avatar: null as File | null,
    student_card: null as File | null,
});

const selectClass =
    'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';

function submit() {
    form.transform((data) => ({ ...data, _method: 'put' })).post(route('participant.profile.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Lengkapi Profil" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4" @submit.prevent="submit">
            <h1 class="text-xl font-semibold">Lengkapi Profil</h1>

            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="title_prefix">Gelar Depan</Label>
                    <Input id="title_prefix" v-model="form.title_prefix" placeholder="Dr." />
                    <InputError :message="form.errors.title_prefix" />
                </div>
                <div class="grid gap-2">
                    <Label for="title_suffix">Gelar Belakang</Label>
                    <Input id="title_suffix" v-model="form.title_suffix" placeholder="M.Kom." />
                    <InputError :message="form.errors.title_suffix" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="gender">Jenis Kelamin</Label>
                <select id="gender" v-model="form.gender" :class="selectClass">
                    <option value="" disabled>Pilih jenis kelamin</option>
                    <option value="laki_laki">Laki-laki</option>
                    <option value="perempuan">Perempuan</option>
                </select>
                <InputError :message="form.errors.gender" />
            </div>

            <div class="grid gap-2">
                <Label for="address">Alamat</Label>
                <textarea id="address" v-model="form.address" rows="3" :class="selectClass"></textarea>
                <InputError :message="form.errors.address" />
            </div>

            <div class="grid gap-2">
                <Label for="institution">Perguruan Tinggi / Instansi</Label>
                <Input id="institution" v-model="form.institution" />
                <InputError :message="form.errors.institution" />
            </div>

            <div class="grid gap-2">
                <Label for="study_program">Program Studi / Unit Kerja</Label>
                <Input id="study_program" v-model="form.study_program" />
                <InputError :message="form.errors.study_program" />
            </div>

            <div class="grid gap-2">
                <Label for="whatsapp_number">Nomor WhatsApp</Label>
                <Input id="whatsapp_number" v-model="form.whatsapp_number" placeholder="08xxxxxxxxxx" />
                <InputError :message="form.errors.whatsapp_number" />
            </div>

            <div class="grid gap-2">
                <Label for="avatar">Foto Profil</Label>
                <input
                    id="avatar"
                    type="file"
                    accept="image/*"
                    :class="selectClass"
                    @change="form.avatar = ($event.target as HTMLInputElement).files?.[0] ?? null"
                />
                <InputError :message="form.errors.avatar" />
            </div>

            <div class="grid gap-2">
                <Label for="student_card">Kartu Mahasiswa (jika berlaku)</Label>
                <input
                    id="student_card"
                    type="file"
                    accept="image/*,.pdf"
                    :class="selectClass"
                    @change="form.student_card = ($event.target as HTMLInputElement).files?.[0] ?? null"
                />
                <InputError :message="form.errors.student_card" />
            </div>

            <Button type="submit" :disabled="form.processing">Simpan Profil</Button>
        </form>
    </AppLayout>
</template>
