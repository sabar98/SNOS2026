<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { certificateRoleLabels } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

interface Certificate {
    id: number;
    certificate_number: string;
    role: string;
    jp_hours: number | null;
    file_path: string | null;
}

interface Registration {
    id: number;
    registration_number: string;
    evaluation: unknown | null;
}

interface Eligibility {
    is_paid: boolean;
    is_present: boolean;
    has_evaluated: boolean;
    presentation_done: boolean;
    is_eligible: boolean;
}

defineProps<{
    certificates: Certificate[];
    registrations: Registration[];
    eligibility: Record<number, Eligibility>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Sertifikat', href: '/participant/certificates' }];

const evalForm = useForm({
    speaker_rating: 5,
    committee_rating: 5,
    material_quality_rating: 5,
    facility_rating: 5,
    zoom_rating: 5,
    feedback: '',
});

function submitEvaluation(registrationId: number) {
    evalForm.post(route('participant.evaluation.store', registrationId), { preserveScroll: true });
}
</script>

<template>
    <Head title="Sertifikat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <Card
                v-for="certificate in certificates"
                :key="certificate.id"
                class="border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40"
            >
                <CardHeader>
                    <CardTitle class="text-base text-emerald-800 dark:text-emerald-300">
                        Sertifikat {{ certificateRoleLabels[certificate.role] ?? certificate.role }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <p>
                        Nomor: <span class="font-mono">{{ certificate.certificate_number }}</span>
                    </p>
                    <p v-if="certificate.jp_hours">Jumlah JP: {{ certificate.jp_hours }}</p>
                    <a v-if="certificate.file_path" :href="`/storage/${certificate.file_path}`" target="_blank">
                        <Button size="sm">Unduh Sertifikat</Button>
                    </a>
                </CardContent>
            </Card>

            <div v-if="certificates.length === 0" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                Belum ada sertifikat yang diterbitkan.
            </div>

            <Card v-for="registration in registrations" :key="registration.id">
                <CardHeader>
                    <CardTitle class="text-base">Kelayakan &mdash; {{ registration.registration_number }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <ul class="space-y-2">
                        <li class="flex items-center justify-between">
                            <span>Pembayaran lunas</span>
                            <Badge :variant="eligibility[registration.id]?.is_paid ? 'success' : 'secondary'">
                                {{ eligibility[registration.id]?.is_paid ? 'Ya' : 'Belum' }}
                            </Badge>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Hadir</span>
                            <Badge :variant="eligibility[registration.id]?.is_present ? 'success' : 'secondary'">
                                {{ eligibility[registration.id]?.is_present ? 'Ya' : 'Belum' }}
                            </Badge>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Evaluasi terisi</span>
                            <Badge :variant="eligibility[registration.id]?.has_evaluated ? 'success' : 'secondary'">
                                {{ eligibility[registration.id]?.has_evaluated ? 'Ya' : 'Belum' }}
                            </Badge>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Kewajiban presentasi</span>
                            <Badge :variant="eligibility[registration.id]?.presentation_done ? 'success' : 'secondary'">
                                {{ eligibility[registration.id]?.presentation_done ? 'Selesai' : 'Belum' }}
                            </Badge>
                        </li>
                    </ul>

                    <form v-if="!registration.evaluation" class="grid gap-2 border-t pt-3" @submit.prevent="submitEvaluation(registration.id)">
                        <p class="text-xs text-muted-foreground">Isi evaluasi kegiatan (skala 1-5) untuk membuka sertifikat.</p>
                        <label class="flex items-center justify-between">
                            Narasumber
                            <input v-model.number="evalForm.speaker_rating" type="number" min="1" max="5" class="w-16 rounded border px-2" />
                        </label>
                        <label class="flex items-center justify-between">
                            Panitia
                            <input v-model.number="evalForm.committee_rating" type="number" min="1" max="5" class="w-16 rounded border px-2" />
                        </label>
                        <label class="flex items-center justify-between">
                            Kualitas Materi
                            <input v-model.number="evalForm.material_quality_rating" type="number" min="1" max="5" class="w-16 rounded border px-2" />
                        </label>
                        <label class="flex items-center justify-between">
                            Fasilitas
                            <input v-model.number="evalForm.facility_rating" type="number" min="1" max="5" class="w-16 rounded border px-2" />
                        </label>
                        <label class="flex items-center justify-between">
                            Pelaksanaan Zoom
                            <input v-model.number="evalForm.zoom_rating" type="number" min="1" max="5" class="w-16 rounded border px-2" />
                        </label>
                        <textarea
                            v-model="evalForm.feedback"
                            rows="2"
                            placeholder="Kritik dan saran"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                        ></textarea>
                        <Button type="submit" size="sm" :disabled="evalForm.processing">Kirim Evaluasi</Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
