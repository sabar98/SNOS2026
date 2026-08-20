<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/composables/useInitials';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { FileText, IdCard, Image as ImageIcon, UserCog } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Profile {
    title_prefix?: string;
    title_suffix?: string;
    gender?: string;
    address?: string;
    institution?: string;
    study_program?: string;
    avatar_path?: string | null;
    student_card_path?: string | null;
}

const props = defineProps<{
    profile: Profile | null;
    whatsappNumber: string | null;
}>();

const page = usePage<SharedData>();
const { getInitials } = useInitials();

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

const avatarPreview = ref<string | null>(null);
const avatarInput = ref<HTMLInputElement>();

const avatarSrc = computed(() => avatarPreview.value ?? (props.profile?.avatar_path ? `/storage/${props.profile.avatar_path}` : undefined));

function onAvatarChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.avatar = file;

    if (avatarPreview.value) {
        URL.revokeObjectURL(avatarPreview.value);
    }
    avatarPreview.value = file ? URL.createObjectURL(file) : null;
}

const studentCardPreview = ref<string | null>(null);
const studentCardFileName = ref<string | null>(null);
const studentCardInput = ref<HTMLInputElement>();

function onStudentCardChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.student_card = file;

    if (studentCardPreview.value) {
        URL.revokeObjectURL(studentCardPreview.value);
    }
    studentCardPreview.value = file && file.type.startsWith('image/') ? URL.createObjectURL(file) : null;
    studentCardFileName.value = file?.name ?? null;
}

function submit() {
    form.transform((data) => ({ ...data, _method: 'put' })).post(route('participant.profile.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.avatar = null;
            form.student_card = null;
            if (avatarInput.value) avatarInput.value.value = '';
            if (studentCardInput.value) studentCardInput.value.value = '';
            if (avatarPreview.value) {
                URL.revokeObjectURL(avatarPreview.value);
                avatarPreview.value = null;
            }
            if (studentCardPreview.value) {
                URL.revokeObjectURL(studentCardPreview.value);
                studentCardPreview.value = null;
            }
            studentCardFileName.value = null;
        },
    });
}
</script>

<template>
    <Head title="Lengkapi Profil" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4" @submit.prevent="submit">
            <PageHeader :icon="UserCog" title="Lengkapi Profil" description="Data ini akan digunakan pada sertifikat dan dokumen resmi Anda." />

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base"><ImageIcon class="size-4 text-muted-foreground" /> Foto Profil</CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                    <Avatar size="lg" shape="circle" class="border-4 border-background shadow-md">
                        <AvatarImage v-if="avatarSrc" :src="avatarSrc" :alt="page.props.auth.user.name" />
                        <AvatarFallback class="text-3xl">{{ getInitials(page.props.auth.user.name) }}</AvatarFallback>
                    </Avatar>
                    <div class="flex flex-1 flex-col gap-2">
                        <Label for="avatar">{{ avatarSrc ? 'Ganti Foto' : 'Unggah Foto' }}</Label>
                        <input
                            id="avatar"
                            ref="avatarInput"
                            type="file"
                            accept="image/*"
                            class="text-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-secondary-foreground"
                            @change="onAvatarChange"
                        />
                        <p class="text-xs text-muted-foreground">Format JPG/PNG, maksimum 2MB. Pratinjau akan tampil di sebelah kiri.</p>
                        <InputError :message="form.errors.avatar" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Informasi Pribadi</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Institusi & Kontak</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4">
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
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base"><IdCard class="size-4 text-muted-foreground" /> Kartu Mahasiswa</CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col gap-3">
                    <div v-if="studentCardPreview" class="w-32 overflow-hidden rounded-md border shadow-sm">
                        <img :src="studentCardPreview" alt="Pratinjau kartu mahasiswa" class="h-auto w-full object-cover" />
                    </div>
                    <div v-else-if="studentCardFileName" class="flex items-center gap-2 rounded-md border bg-muted/40 px-3 py-2 text-sm">
                        <FileText class="size-4 shrink-0 text-muted-foreground" />
                        <span class="truncate">{{ studentCardFileName }}</span>
                    </div>
                    <a
                        v-else-if="profile?.student_card_path"
                        :href="`/storage/${profile.student_card_path}`"
                        target="_blank"
                        class="inline-flex w-fit items-center gap-2 rounded-md border bg-muted/40 px-3 py-2 text-sm text-primary underline"
                    >
                        <FileText class="size-4 shrink-0" /> Lihat file yang sudah diunggah
                    </a>

                    <div class="grid gap-2">
                        <Label for="student_card">{{ profile?.student_card_path ? 'Ganti File' : 'Unggah File' }} (jika berlaku)</Label>
                        <input
                            id="student_card"
                            ref="studentCardInput"
                            type="file"
                            accept="image/*,.pdf"
                            class="text-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-secondary-foreground"
                            @change="onStudentCardChange"
                        />
                        <p class="text-xs text-muted-foreground">Format JPG, PNG, atau PDF, maksimum 2MB.</p>
                        <InputError :message="form.errors.student_card" />
                    </div>
                </CardContent>
            </Card>

            <Button type="submit" size="lg" class="w-full sm:w-fit" :disabled="form.processing">Simpan Profil</Button>
        </form>
    </AppLayout>
</template>
