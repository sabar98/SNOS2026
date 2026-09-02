<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { attendanceMethodLabels } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Lock, MapPin, Save, Sparkles, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

interface Registration {
    id: number;
    registration_number: string;
    participant_type: string;
    attendance_method: string;
    article_scope: string | null;
    institution: string;
    special_needs: string | null;
    join_wisata_sabang: boolean;
    join_wisata_lokal: boolean;
}

interface RegistrationFeeRow {
    participant_type: string;
    attendance_method: string;
    amount: number;
}

interface ParticipantCategoryRow {
    key: string;
    label: string;
    is_presenter: boolean;
}

const props = defineProps<{
    registration: Registration;
    registrationFees: RegistrationFeeRow[];
    participantCategories: ParticipantCategoryRow[];
    feeLocked: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: props.registration.registration_number, href: route('participant.registrations.show', props.registration.id) },
    { title: 'Ubah Data', href: '#' },
];

const form = useForm({
    participant_type: props.registration.participant_type,
    attendance_method: props.registration.attendance_method,
    article_scope: props.registration.article_scope ?? '',
    institution: props.registration.institution,
    special_needs: props.registration.special_needs ?? '',
    join_wisata_sabang: props.registration.join_wisata_sabang,
    join_wisata_lokal: props.registration.join_wisata_lokal,
});

const selectClass =
    'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60';

const selectedFee = computed(() => {
    if (!form.participant_type || !form.attendance_method) return null;
    const match = props.registrationFees.find(
        (fee: RegistrationFeeRow) => fee.participant_type === form.participant_type && fee.attendance_method === form.attendance_method,
    );
    return match ? match.amount : null;
});
const selectedCategory = computed(() =>
    props.participantCategories.find((category: ParticipantCategoryRow) => category.key === form.participant_type),
);
const isPresenter = computed(() => selectedCategory.value?.is_presenter ?? false);

function formatRupiah(amount: number): string {
    return `Rp${amount.toLocaleString('id-ID')}`;
}

function submit() {
    form.put(route('participant.registrations.update', props.registration.id));
}
</script>

<template>
    <Head title="Ubah Data Pendaftaran" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4" @submit.prevent="submit">
            <Link
                :href="route('participant.registrations.show', registration.id)"
                class="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="size-4" /> Kembali ke Detail Pendaftaran
            </Link>

            <PageHeader
                :icon="Sparkles"
                icon-class="bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400"
                title="Ubah Data Pendaftaran"
                :description="`Perbarui data pendaftaran ${registration.registration_number}.`"
            />

            <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Sparkles class="size-4 text-sky-700 dark:text-sky-400" /> Jenis Kepesertaan
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="participant_type">Jenis Kepesertaan</Label>
                        <select id="participant_type" v-model="form.participant_type" :disabled="feeLocked" :class="selectClass">
                            <option v-for="category in participantCategories" :key="category.key" :value="category.key">
                                {{ category.label }}
                            </option>
                        </select>
                        <p v-if="feeLocked" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <Lock class="size-3.5" /> Tidak dapat diubah karena pembayaran registrasi sudah diverifikasi.
                        </p>
                        <InputError :message="form.errors.participant_type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="attendance_method">Metode Kehadiran</Label>
                        <select id="attendance_method" v-model="form.attendance_method" :class="selectClass">
                            <option value="luring">Luring</option>
                            <option value="daring">Daring</option>
                        </select>
                        <InputError :message="form.errors.attendance_method" />
                    </div>

                    <div
                        v-if="selectedFee !== null && !feeLocked"
                        class="flex items-center justify-between rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-900 dark:bg-emerald-950/40"
                    >
                        <div class="flex items-center gap-2 text-sm text-emerald-800/80 dark:text-emerald-300/80">
                            <Wallet class="size-4" /> Biaya {{ selectedCategory?.label }} &middot;
                            {{ attendanceMethodLabels[form.attendance_method] }}
                        </div>
                        <span class="text-lg font-bold text-emerald-800 dark:text-emerald-300">{{ formatRupiah(selectedFee) }}</span>
                    </div>
                </CardContent>
            </Card>

            <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                <CardHeader>
                    <CardTitle class="text-base">Detail Tambahan</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="article_scope">Bidang / Scope Artikel {{ isPresenter ? '' : '(opsional)' }}</Label>
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
                        <textarea
                            id="special_needs"
                            v-model="form.special_needs"
                            rows="2"
                            placeholder="Mis. kursi roda, alergi makanan, dsb. (opsional)"
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        ></textarea>
                        <InputError :message="form.errors.special_needs" />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <MapPin class="size-4 text-sky-700 dark:text-sky-400" /> Kegiatan Tambahan (Opsional)
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <label class="flex cursor-pointer items-start gap-3 rounded-md border p-3 text-sm transition-colors hover:bg-muted/40">
                        <input v-model="form.join_wisata_sabang" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-input" />
                        <span>Ikut Wisata Sabang</span>
                    </label>
                    <InputError :message="form.errors.join_wisata_sabang" />

                    <label class="flex cursor-pointer items-start gap-3 rounded-md border p-3 text-sm transition-colors hover:bg-muted/40">
                        <input v-model="form.join_wisata_lokal" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-input" />
                        <span>Ikut Wisata Lokal (Banda Aceh)</span>
                    </label>
                    <InputError :message="form.errors.join_wisata_lokal" />
                </CardContent>
            </Card>

            <div class="flex items-center gap-3">
                <Button type="submit" size="lg" class="gap-2" :disabled="form.processing"> <Save class="size-4" /> Simpan Perubahan </Button>
                <Link :href="route('participant.registrations.show', registration.id)">
                    <Button type="button" variant="outline" size="lg">Batal</Button>
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
