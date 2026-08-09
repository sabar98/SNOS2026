<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

interface Seminar {
    fees: Record<string, number>;
}

defineProps<{
    seminar: Seminar;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Daftar Kegiatan', href: '/participant/registrations/create' }];

const form = useForm({
    participant_type: '',
    attendance_method: '',
    article_scope: '',
    institution: '',
    special_needs: '',
    join_gala_dinner: false,
    terms_accepted: false,
});

const selectClass =
    'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';

function submit() {
    form.post(route('participant.registrations.store'));
}
</script>

<template>
    <Head title="Daftar Kegiatan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4" @submit.prevent="submit">
            <h1 class="text-xl font-semibold">Formulir Pendaftaran Kegiatan</h1>

            <div class="grid gap-2">
                <Label for="participant_type">Jenis Kepesertaan</Label>
                <select id="participant_type" v-model="form.participant_type" :class="selectClass">
                    <option value="" disabled>Pilih jenis kepesertaan</option>
                    <option value="presenter_luring">Presenter Luring</option>
                    <option value="presenter_daring">Presenter Daring</option>
                    <option value="peserta_umum">Peserta Umum (Nonpresenter)</option>
                    <option value="peserta_mahasiswa">Peserta Mahasiswa (Nonpresenter)</option>
                </select>
                <InputError :message="form.errors.participant_type" />
            </div>

            <div class="grid gap-2">
                <Label for="attendance_method">Metode Kehadiran</Label>
                <select id="attendance_method" v-model="form.attendance_method" :class="selectClass">
                    <option value="" disabled>Pilih metode kehadiran</option>
                    <option value="luring">Luring</option>
                    <option value="daring">Daring</option>
                </select>
                <InputError :message="form.errors.attendance_method" />
            </div>

            <div class="grid gap-2">
                <Label for="article_scope">Bidang / Scope Artikel</Label>
                <Input id="article_scope" v-model="form.article_scope" placeholder="Teknologi Informasi" />
                <InputError :message="form.errors.article_scope" />
            </div>

            <div class="grid gap-2">
                <Label for="institution">Institusi</Label>
                <Input id="institution" v-model="form.institution" required />
                <InputError :message="form.errors.institution" />
            </div>

            <div class="grid gap-2">
                <Label for="special_needs">Kebutuhan Khusus</Label>
                <textarea id="special_needs" v-model="form.special_needs" rows="2" :class="selectClass"></textarea>
                <InputError :message="form.errors.special_needs" />
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input v-model="form.join_gala_dinner" type="checkbox" class="h-4 w-4 rounded border-input" />
                Ikut malam keakraban
            </label>

            <label class="flex items-center gap-2 text-sm">
                <input v-model="form.terms_accepted" type="checkbox" class="h-4 w-4 rounded border-input" required />
                Saya menyetujui syarat dan ketentuan kegiatan
            </label>
            <InputError :message="form.errors.terms_accepted" />

            <Button type="submit" :disabled="form.processing">Daftar</Button>
        </form>
    </AppLayout>
</template>
