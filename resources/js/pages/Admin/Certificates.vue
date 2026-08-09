<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { certificateRoleLabels } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Certificate {
    id: number;
    certificate_number: string;
    role: string;
    file_path: string | null;
    user: { name: string };
}

interface EligibleRegistration {
    id: number;
    registration_number: string;
    institution: string | null;
    user: { name: string };
}

interface CertificateTemplateEntry {
    id: number;
    role: string;
    file_path: string;
}

const props = defineProps<{
    certificates: { data: Certificate[] };
    eligibleRegistrations: EligibleRegistration[];
    certificateTemplates: Record<string, CertificateTemplateEntry>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Sertifikat', href: '/admin/certificates' }];
const selectClass = 'flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm';

const form = useForm({
    event_registration_id: '',
    role: 'peserta',
    jp_hours: 8,
    certificate_file: null as File | null,
});

const selectedRegistration = computed(() => props.eligibleRegistrations.find((r) => r.id === Number(form.event_registration_id)) ?? null);

const fileInput = ref<HTMLInputElement>();

function onFileChange(event: Event) {
    form.certificate_file = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function issue() {
    form.post(route('admin.certificates.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
}

const currentTemplate = computed(() => props.certificateTemplates[form.role] ?? null);

const previewUrl = computed(() =>
    route('admin.certificates.preview', {
        role: form.role,
        event_registration_id: form.event_registration_id || undefined,
        jp_hours: form.jp_hours || undefined,
    }),
);

const templateForm = useForm({
    role: '',
    template_file: null as File | null,
});

const templateFileInput = ref<HTMLInputElement>();

function onTemplateFileChange(event: Event) {
    templateForm.template_file = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function uploadTemplate() {
    templateForm.role = form.role;
    templateForm.post(route('admin.certificate-templates.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            templateForm.reset('template_file');
            if (templateFileInput.value) {
                templateFileInput.value.value = '';
            }
        },
    });
}

function removeTemplate(template: CertificateTemplateEntry) {
    if (!confirm(`Hapus template sertifikat untuk peran "${certificateRoleLabels[template.role] ?? template.role}"?`)) {
        return;
    }
    router.delete(route('admin.certificate-templates.destroy', template.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Sertifikat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
            <div class="grid gap-4 md:grid-cols-2">
                <Card class="border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40">
                    <CardHeader
                        ><CardTitle class="text-sm font-medium text-emerald-800/80 dark:text-emerald-300/80"
                            >Sertifikat Diterbitkan</CardTitle
                        ></CardHeader
                    >
                    <CardContent class="text-2xl font-bold text-emerald-800 dark:text-emerald-300">{{ certificates.data.length }}</CardContent>
                </Card>
                <Card class="border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/40">
                    <CardHeader
                        ><CardTitle class="text-sm font-medium text-amber-800/80 dark:text-amber-300/80"
                            >Peserta Memenuhi Syarat</CardTitle
                        ></CardHeader
                    >
                    <CardContent class="text-2xl font-bold text-amber-800 dark:text-amber-300">{{ eligibleRegistrations.length }}</CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader><CardTitle>Terbitkan Sertifikat</CardTitle></CardHeader>
                <CardContent>
                    <form class="grid gap-3" @submit.prevent="issue">
                        <div
                            v-if="Object.keys(form.errors).length > 0"
                            class="rounded-md border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive"
                        >
                            Sertifikat gagal disimpan. Periksa kembali isian di bawah ini.
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <select v-model="form.event_registration_id" aria-label="Pilih peserta" :class="selectClass">
                                <option value="" disabled>Pilih peserta</option>
                                <option v-for="registration in eligibleRegistrations" :key="registration.id" :value="registration.id">
                                    {{ registration.user.name }} ({{ registration.registration_number }})
                                </option>
                            </select>
                            <select v-model="form.role" aria-label="Peran sertifikat" :class="selectClass">
                                <option value="peserta">Peserta</option>
                                <option value="presenter">Presenter</option>
                                <option value="moderator">Moderator</option>
                                <option value="reviewer">Reviewer</option>
                                <option value="narasumber">Narasumber</option>
                                <option value="panitia">Panitia</option>
                            </select>
                            <input
                                v-model.number="form.jp_hours"
                                type="number"
                                min="0"
                                aria-label="Jumlah JP"
                                class="w-20 rounded-md border border-input px-3 py-2 text-sm"
                            />
                        </div>
                        <p v-if="form.errors.event_registration_id" class="text-sm text-destructive">{{ form.errors.event_registration_id }}</p>

                        <div v-if="selectedRegistration" class="grid grid-cols-2 gap-x-6 gap-y-1 rounded-md border bg-muted/40 p-3 text-sm">
                            <div>
                                <span class="text-muted-foreground">Nama Peserta</span>
                                <p class="font-medium">{{ selectedRegistration.user.name }}</p>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Institusi</span>
                                <p class="font-medium">{{ selectedRegistration.institution ?? '-' }}</p>
                            </div>
                            <p class="col-span-2 text-xs text-muted-foreground">
                                Nama dan institusi diambil otomatis dari data pendaftaran, tidak perlu diketik ulang.
                            </p>
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="certificate_file">Upload File Sertifikat (PDF, opsional)</Label>
                            <input
                                id="certificate_file"
                                ref="fileInput"
                                type="file"
                                accept="application/pdf"
                                class="text-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-secondary-foreground"
                                @change="onFileChange"
                            />
                            <p class="text-xs text-muted-foreground">
                                Kosongkan untuk membiarkan sistem membuat sertifikat otomatis dengan nama peserta yang sudah terisi.
                            </p>
                            <p v-if="form.errors.certificate_file" class="text-sm text-destructive">{{ form.errors.certificate_file }}</p>
                        </div>

                        <Button type="submit" class="w-fit" :disabled="form.processing || !form.event_registration_id">Terbitkan</Button>
                    </form>
                </CardContent>
            </Card>

            <Card v-if="form.role">
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle>Template Sertifikat &mdash; {{ certificateRoleLabels[form.role] ?? form.role }}</CardTitle>
                    <Button as="a" :href="previewUrl" target="_blank" variant="outline" size="sm">Pratinjau Sertifikat</Button>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <div v-if="currentTemplate" class="flex items-center gap-4">
                        <img
                            :src="`/storage/${currentTemplate.file_path}`"
                            alt="Template sertifikat saat ini"
                            class="h-24 w-auto rounded-md border object-cover"
                        />
                        <div class="flex flex-col items-start gap-1">
                            <p class="text-sm text-muted-foreground">
                                Template aktif untuk peran ini. Sertifikat otomatis akan memakai desain ini sebagai latar. Gunakan "Pratinjau
                                Sertifikat" untuk mengecek posisi nama sebelum menerbitkan sertifikat sungguhan.
                            </p>
                            <Button variant="destructive" size="sm" @click="removeTemplate(currentTemplate)">Hapus Template</Button>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">
                        Belum ada template untuk peran ini. Sertifikat otomatis akan memakai desain bawaan sampai template diunggah.
                    </p>

                    <form class="grid gap-1.5" @submit.prevent="uploadTemplate">
                        <Label :for="`template_file_${form.role}`">{{ currentTemplate ? 'Ganti' : 'Unggah' }} Template (JPG/PNG)</Label>
                        <input
                            :id="`template_file_${form.role}`"
                            ref="templateFileInput"
                            type="file"
                            accept="image/png,image/jpeg"
                            class="text-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-secondary-foreground"
                            @change="onTemplateFileChange"
                        />
                        <p v-if="templateForm.errors.template_file" class="text-sm text-destructive">{{ templateForm.errors.template_file }}</p>
                        <Button type="submit" size="sm" class="w-fit" :disabled="templateForm.processing || !templateForm.template_file">
                            Simpan Template
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle>Sertifikat Diterbitkan</CardTitle></CardHeader>
                <CardContent>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="py-2">Nomor</th>
                                <th>Nama</th>
                                <th>Peran</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="certificate in certificates.data" :key="certificate.id" class="border-b last:border-0">
                                <td class="py-2 font-mono">{{ certificate.certificate_number }}</td>
                                <td>{{ certificate.user.name }}</td>
                                <td>
                                    <Badge variant="success">{{ certificateRoleLabels[certificate.role] ?? certificate.role }}</Badge>
                                </td>
                                <td>
                                    <a
                                        v-if="certificate.file_path"
                                        :href="`/storage/${certificate.file_path}`"
                                        target="_blank"
                                        class="text-primary underline"
                                    >
                                        PDF
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
