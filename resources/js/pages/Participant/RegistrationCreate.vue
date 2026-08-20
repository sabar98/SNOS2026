<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { participantTypeLabels } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ClipboardCheck, ClipboardPlus, MessageSquareHeart, Sparkles, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

interface Seminar {
    fees: Record<string, number>;
}

const props = defineProps<{
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

const selectedFee = computed(() => (form.participant_type ? props.seminar.fees[form.participant_type] : null));
const isPresenter = computed(() => form.participant_type.startsWith('presenter_'));

function formatRupiah(amount: number): string {
    return `Rp${amount.toLocaleString('id-ID')}`;
}

function submit() {
    form.post(route('participant.registrations.store'));
}
</script>

<template>
    <Head title="Daftar Kegiatan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4" @submit.prevent="submit">
            <PageHeader :icon="ClipboardPlus" title="Formulir Pendaftaran Kegiatan" description="Lengkapi data pendaftaran Anda untuk SNOS 2026." />

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Sparkles class="size-4 text-muted-foreground" /> Jenis Kepesertaan
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4">
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

                    <div
                        v-if="selectedFee !== null"
                        class="flex items-center justify-between rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-900 dark:bg-emerald-950/40"
                    >
                        <div class="flex items-center gap-2 text-sm text-emerald-800/80 dark:text-emerald-300/80">
                            <Wallet class="size-4" /> Biaya {{ participantTypeLabels[form.participant_type] }}
                        </div>
                        <span class="text-lg font-bold text-emerald-800 dark:text-emerald-300">{{ formatRupiah(selectedFee) }}</span>
                    </div>
                </CardContent>
            </Card>

            <Card>
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
                            :class="selectClass"
                        ></textarea>
                        <InputError :message="form.errors.special_needs" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <ClipboardCheck class="size-4 text-muted-foreground" /> Persetujuan
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <label class="flex cursor-pointer items-start gap-3 rounded-md border p-3 text-sm transition-colors hover:bg-muted/40">
                        <input v-model="form.join_gala_dinner" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-input" />
                        <span class="flex items-center gap-2">
                            <MessageSquareHeart class="size-4 shrink-0 text-muted-foreground" />
                            Ikut malam keakraban
                        </span>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-md border p-3 text-sm transition-colors hover:bg-muted/40">
                        <input v-model="form.terms_accepted" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-input" required />
                        <span>Saya menyetujui syarat dan ketentuan kegiatan</span>
                    </label>
                    <InputError :message="form.errors.terms_accepted" />
                </CardContent>
            </Card>

            <Button type="submit" size="lg" class="w-full sm:w-fit" :disabled="form.processing">Daftar</Button>
        </form>
    </AppLayout>
</template>
