<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { FileSignature, PenTool } from 'lucide-vue-next';
import { ref } from 'vue';

interface LoaSetting {
    signature_path: string | null;
    signer_name: string | null;
    signer_title: string | null;
}

const props = defineProps<{
    setting: LoaSetting;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Tanda Tangan LoA', href: '/admin/loa-settings' }];

const form = useForm({
    signer_name: props.setting.signer_name ?? '',
    signer_title: props.setting.signer_title ?? '',
    signature: null as File | null,
});

const fileInput = ref<HTMLInputElement>();

function onFileChange(event: Event) {
    form.signature = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function save() {
    form.post(route('admin.loa-settings.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.signature = null;
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
}

function remove() {
    if (!confirm('Hapus tanda tangan yang sedang aktif? Dokumen LoA berikutnya tidak akan menampilkan tanda tangan sampai diunggah ulang.')) {
        return;
    }
    router.delete(route('admin.loa-settings.destroy'), { preserveScroll: true });
}
</script>

<template>
    <Head title="Tanda Tangan LoA" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <PageHeader
                :icon="PenTool"
                icon-class="bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400"
                title="Tanda Tangan LoA"
                description="Atur nama pimpinan dan tanda tangan yang akan ditampilkan pada setiap dokumen Letter of Acceptance (LoA) yang diterbitkan."
            />

            <Card class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2"><FileSignature class="size-4 text-muted-foreground" /> Penandatangan LoA</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-6">
                    <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                        <img
                            v-if="setting.signature_path"
                            :src="`/storage/${setting.signature_path}`"
                            alt="Tanda tangan LoA saat ini"
                            class="h-20 w-auto rounded-md border bg-white object-contain p-2"
                        />
                        <div class="flex flex-col items-start gap-1">
                            <p class="text-sm text-muted-foreground">
                                <template v-if="setting.signature_path">
                                    Tanda tangan ini otomatis ditempelkan pada setiap LoA yang diterbitkan dari sekarang. LoA yang sudah terbit
                                    sebelumnya tidak berubah.
                                </template>
                                <template v-else>
                                    Belum ada tanda tangan yang diunggah. LoA tidak akan menampilkan gambar tanda tangan sampai diunggah.
                                </template>
                            </p>
                            <Button v-if="setting.signature_path" variant="destructive" size="sm" @click="remove">Hapus Tanda Tangan</Button>
                        </div>
                    </div>

                    <form class="grid gap-4" @submit.prevent="save">
                        <div class="grid gap-1.5">
                            <Label for="signer_name">Nama Pimpinan <span class="text-destructive">*</span></Label>
                            <Input id="signer_name" v-model="form.signer_name" placeholder="Contoh: Prof. Dr. Nama Lengkap, M.T." />
                            <p v-if="form.errors.signer_name" class="text-sm text-destructive">{{ form.errors.signer_name }}</p>
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="signer_title">Jabatan</Label>
                            <Input id="signer_title" v-model="form.signer_title" placeholder="Contoh: Ketua Panitia SNOS 2026" />
                            <p v-if="form.errors.signer_title" class="text-sm text-destructive">{{ form.errors.signer_title }}</p>
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="signature">{{ setting.signature_path ? 'Ganti' : 'Unggah' }} Tanda Tangan (PNG/JPG)</Label>
                            <input
                                id="signature"
                                ref="fileInput"
                                type="file"
                                accept="image/png,image/jpeg"
                                class="text-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-secondary-foreground"
                                @change="onFileChange"
                            />
                            <p class="text-xs text-muted-foreground">
                                Gunakan gambar tanda tangan dengan latar transparan (PNG) agar hasilnya rapi di atas dokumen. Kosongkan jika hanya
                                ingin mengubah nama atau jabatan.
                            </p>
                            <p v-if="form.errors.signature" class="text-sm text-destructive">{{ form.errors.signature }}</p>
                        </div>

                        <Button type="submit" size="sm" class="w-fit" :disabled="form.processing || !form.signer_name.trim()">Simpan</Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
