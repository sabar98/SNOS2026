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
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { CalendarClock, ExternalLink, Globe, Handshake, ListChecks, MessageSquareQuote, Mic, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Speaker {
    name: string;
    title: string;
    topic?: string | null;
    photo_path?: string | null;
}

interface TimelineItem {
    label: string;
    date: string;
}

interface LeaderMessage {
    name: string;
    title: string;
    message: string;
    photo_path?: string | null;
}

interface Partner {
    name: string;
    logo_path?: string | null;
}

interface Setting {
    name: string;
    theme: string;
    date_range: string;
    location: string;
    scope: string[];
    speakers: Speaker[];
    timeline: TimelineItem[];
    leader_message: LeaderMessage;
    partners: Partner[];
}

const props = defineProps<{
    setting: Setting;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Landing Page', href: '/admin/landing-settings' }];

const { getInitials } = useInitials();

const form = useForm({
    name: props.setting.name,
    theme: props.setting.theme,
    date_range: props.setting.date_range,
    location: props.setting.location,
    scope: [...props.setting.scope],
    speakers: props.setting.speakers.map((speaker) => ({
        name: speaker.name,
        title: speaker.title,
        topic: speaker.topic ?? '',
        photo_path: speaker.photo_path ?? null,
        photo: null as File | null,
    })),
    timeline: props.setting.timeline.map((item) => ({ ...item })),
    leader_message: {
        name: props.setting.leader_message.name,
        title: props.setting.leader_message.title,
        message: props.setting.leader_message.message,
        photo_path: props.setting.leader_message.photo_path ?? null,
        photo: null as File | null,
    },
    partners: props.setting.partners.map((partner) => ({
        name: partner.name,
        logo_path: partner.logo_path ?? null,
        logo: null as File | null,
    })),
});

function addScope() {
    form.scope.push('');
}

function removeScope(index: number) {
    form.scope.splice(index, 1);
}

const speakerPhotoPreviews = ref<(string | null)[]>(props.setting.speakers.map(() => null));

function addSpeaker() {
    form.speakers.push({ name: '', title: '', topic: '', photo_path: null, photo: null });
    speakerPhotoPreviews.value.push(null);
}

function removeSpeaker(index: number) {
    if (speakerPhotoPreviews.value[index]) {
        URL.revokeObjectURL(speakerPhotoPreviews.value[index]!);
    }
    form.speakers.splice(index, 1);
    speakerPhotoPreviews.value.splice(index, 1);
}

function onSpeakerPhotoChange(event: Event, index: number) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.speakers[index].photo = file;

    if (speakerPhotoPreviews.value[index]) {
        URL.revokeObjectURL(speakerPhotoPreviews.value[index]!);
    }
    speakerPhotoPreviews.value[index] = file ? URL.createObjectURL(file) : null;
}

const leaderPhotoPreview = ref<string | null>(null);

function onLeaderPhotoChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.leader_message.photo = file;

    if (leaderPhotoPreview.value) {
        URL.revokeObjectURL(leaderPhotoPreview.value);
    }
    leaderPhotoPreview.value = file ? URL.createObjectURL(file) : null;
}

const partnerLogoPreviews = ref<(string | null)[]>(props.setting.partners.map(() => null));

function addPartner() {
    form.partners.push({ name: '', logo_path: null, logo: null });
    partnerLogoPreviews.value.push(null);
}

function removePartner(index: number) {
    if (partnerLogoPreviews.value[index]) {
        URL.revokeObjectURL(partnerLogoPreviews.value[index]!);
    }
    form.partners.splice(index, 1);
    partnerLogoPreviews.value.splice(index, 1);
}

function onPartnerLogoChange(event: Event, index: number) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.partners[index].logo = file;

    if (partnerLogoPreviews.value[index]) {
        URL.revokeObjectURL(partnerLogoPreviews.value[index]!);
    }
    partnerLogoPreviews.value[index] = file ? URL.createObjectURL(file) : null;
}

function addTimelineItem() {
    form.timeline.push({ label: '', date: '' });
}

function removeTimelineItem(index: number) {
    form.timeline.splice(index, 1);
}

function submit() {
    form.transform((data) => ({ ...data, _method: 'put' })).post(route('admin.landing-settings.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.speakers.forEach((speaker) => {
                speaker.photo = null;
            });
            form.leader_message.photo = null;
            form.partners.forEach((partner) => {
                partner.logo = null;
            });
        },
    });
}
</script>

<template>
    <Head title="Pengaturan Landing Page" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4" @submit.prevent="submit">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <PageHeader :icon="Globe" title="Pengaturan Landing Page" description="Sesuaikan konten yang tampil di halaman depan SNOS 2026." />
                <a
                    :href="route('home')"
                    target="_blank"
                    class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm font-medium hover:bg-muted/40"
                >
                    <ExternalLink class="size-4" /> Lihat Landing Page
                </a>
            </div>

            <div
                v-if="Object.keys(form.errors).length > 0"
                class="rounded-md border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive"
            >
                Sebagian isian belum valid. Periksa kembali detail di bawah ini.
            </div>

            <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                <CardHeader>
                    <CardTitle class="text-base">Informasi Umum</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="name">Nama Seminar</Label>
                        <Input id="name" v-model="form.name" required />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="theme">Tema</Label>
                        <Input id="theme" v-model="form.theme" required />
                        <InputError :message="form.errors.theme" />
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="date_range">Rentang Tanggal</Label>
                            <Input id="date_range" v-model="form.date_range" placeholder="12-13 November 2026" required />
                            <InputError :message="form.errors.date_range" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="location">Lokasi</Label>
                            <Input id="location" v-model="form.location" required />
                            <InputError :message="form.errors.location" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <MessageSquareQuote class="size-4 text-muted-foreground" /> Kata Sambutan Pemimpin
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <div class="flex items-center gap-4">
                        <Avatar size="base" shape="circle" class="shrink-0 border-2 border-background shadow-sm">
                            <AvatarImage
                                v-if="leaderPhotoPreview || form.leader_message.photo_path"
                                :src="leaderPhotoPreview ?? `/storage/${form.leader_message.photo_path}`"
                                :alt="form.leader_message.name"
                            />
                            <AvatarFallback class="text-lg">{{ getInitials(form.leader_message.name) || '?' }}</AvatarFallback>
                        </Avatar>
                        <div class="flex flex-1 flex-col gap-1.5">
                            <Label for="leader_photo">{{ form.leader_message.photo_path ? 'Ganti Foto' : 'Unggah Foto' }}</Label>
                            <input
                                id="leader_photo"
                                type="file"
                                accept="image/*"
                                class="text-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-secondary-foreground"
                                @change="onLeaderPhotoChange"
                            />
                            <InputError :message="form.errors['leader_message.photo']" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="leader_name">Nama</Label>
                            <Input id="leader_name" v-model="form.leader_message.name" placeholder="Prof. Dr. Ketua Panitia, M.T." />
                            <InputError :message="form.errors['leader_message.name']" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="leader_title">Jabatan</Label>
                            <Input id="leader_title" v-model="form.leader_message.title" placeholder="Ketua Panitia SNOS 2026" />
                            <InputError :message="form.errors['leader_message.title']" />
                        </div>
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="leader_message_text">Isi Sambutan</Label>
                        <textarea
                            id="leader_message_text"
                            v-model="form.leader_message.message"
                            rows="5"
                            placeholder="Tulis kata sambutan yang akan tampil di halaman depan..."
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                        ></textarea>
                        <InputError :message="form.errors['leader_message.message']" />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <ListChecks class="size-4 text-muted-foreground" /> Ruang Lingkup Artikel
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <div v-for="(_, index) in form.scope" :key="index" class="flex items-center gap-2">
                        <Input v-model="form.scope[index]" placeholder="Mis. Teknologi Informasi dan Komunikasi" />
                        <Button type="button" variant="outline" size="icon" @click="removeScope(index)">
                            <Trash2 class="size-4" />
                        </Button>
                    </div>
                    <InputError :message="form.errors.scope" />
                    <Button type="button" variant="outline" size="sm" class="w-fit" @click="addScope"> <Plus class="size-4" /> Tambah Bidang </Button>
                </CardContent>
            </Card>

            <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base"><Mic class="size-4 text-muted-foreground" /> Narasumber</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <div v-for="(speaker, index) in form.speakers" :key="index" class="grid grid-cols-1 gap-3 rounded-md border p-3">
                        <div class="flex items-center gap-4">
                            <Avatar size="base" shape="circle" class="shrink-0 border-2 border-background shadow-sm">
                                <AvatarImage
                                    v-if="speakerPhotoPreviews[index] || speaker.photo_path"
                                    :src="speakerPhotoPreviews[index] ?? `/storage/${speaker.photo_path}`"
                                    :alt="speaker.name"
                                />
                                <AvatarFallback class="text-lg">{{ getInitials(speaker.name) || '?' }}</AvatarFallback>
                            </Avatar>
                            <div class="flex flex-1 flex-col gap-1.5">
                                <Label :for="`speaker_photo_${index}`">{{ speaker.photo_path ? 'Ganti Foto' : 'Unggah Foto' }}</Label>
                                <input
                                    :id="`speaker_photo_${index}`"
                                    type="file"
                                    accept="image/*"
                                    class="text-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-secondary-foreground"
                                    @change="onSpeakerPhotoChange($event, index)"
                                />
                                <InputError :message="form.errors[`speakers.${index}.photo`]" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div class="grid gap-1.5 sm:col-span-2">
                                <Label :for="`speaker_name_${index}`">Nama</Label>
                                <Input :id="`speaker_name_${index}`" v-model="speaker.name" placeholder="Prof. Dr. Contoh Satu" />
                            </div>
                            <div class="grid gap-1.5">
                                <Label :for="`speaker_title_${index}`">Jabatan / Keterangan</Label>
                                <Input :id="`speaker_title_${index}`" v-model="speaker.title" placeholder="Guru Besar Ilmu Komputer" />
                            </div>
                            <div class="grid gap-1.5">
                                <Label :for="`speaker_topic_${index}`">Materi / Topik</Label>
                                <Input :id="`speaker_topic_${index}`" v-model="speaker.topic" placeholder="Transformasi Digital di Sektor Publik" />
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <Button type="button" variant="outline" size="sm" @click="removeSpeaker(index)"> <Trash2 class="size-4" /> Hapus </Button>
                        </div>
                    </div>
                    <p v-if="form.speakers.length === 0" class="text-sm text-muted-foreground">Belum ada narasumber ditambahkan.</p>
                    <Button type="button" variant="outline" size="sm" class="w-fit" @click="addSpeaker">
                        <Plus class="size-4" /> Tambah Narasumber
                    </Button>
                </CardContent>
            </Card>

            <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <CalendarClock class="size-4 text-muted-foreground" /> Timeline Kegiatan
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <div v-for="(item, index) in form.timeline" :key="index" class="grid grid-cols-1 gap-2 rounded-md border p-3 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label :for="`timeline_label_${index}`">Kegiatan</Label>
                            <Input :id="`timeline_label_${index}`" v-model="item.label" placeholder="Pembukaan Pendaftaran" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label :for="`timeline_date_${index}`">Tanggal</Label>
                            <Input :id="`timeline_date_${index}`" v-model="item.date" placeholder="1 Agustus 2026" />
                        </div>
                        <div class="flex justify-end sm:col-span-2">
                            <Button type="button" variant="outline" size="sm" @click="removeTimelineItem(index)">
                                <Trash2 class="size-4" /> Hapus
                            </Button>
                        </div>
                    </div>
                    <p v-if="form.timeline.length === 0" class="text-sm text-muted-foreground">Belum ada item timeline ditambahkan.</p>
                    <Button type="button" variant="outline" size="sm" class="w-fit" @click="addTimelineItem">
                        <Plus class="size-4" /> Tambah Item
                    </Button>
                </CardContent>
            </Card>

            <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Handshake class="size-4 text-muted-foreground" /> Mitra &amp; Kerjasama
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <div v-for="(partner, index) in form.partners" :key="index" class="flex items-center gap-4 rounded-md border p-3">
                        <Avatar size="base" shape="square" class="shrink-0 border bg-background shadow-sm">
                            <AvatarImage
                                v-if="partnerLogoPreviews[index] || partner.logo_path"
                                :src="partnerLogoPreviews[index] ?? `/storage/${partner.logo_path}`"
                                :alt="partner.name"
                            />
                            <AvatarFallback class="text-sm">{{ getInitials(partner.name) || '?' }}</AvatarFallback>
                        </Avatar>
                        <div class="grid flex-1 gap-2 sm:grid-cols-2">
                            <div class="grid gap-1.5">
                                <Label :for="`partner_name_${index}`">Nama Mitra</Label>
                                <Input :id="`partner_name_${index}`" v-model="partner.name" placeholder="Universitas Contoh" />
                                <InputError :message="form.errors[`partners.${index}.name`]" />
                            </div>
                            <div class="grid gap-1.5">
                                <Label :for="`partner_logo_${index}`">{{ partner.logo_path ? 'Ganti Logo' : 'Unggah Logo' }}</Label>
                                <input
                                    :id="`partner_logo_${index}`"
                                    type="file"
                                    accept="image/*"
                                    class="text-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-secondary-foreground"
                                    @change="onPartnerLogoChange($event, index)"
                                />
                                <InputError :message="form.errors[`partners.${index}.logo`]" />
                            </div>
                        </div>
                        <Button type="button" variant="outline" size="icon" class="shrink-0" @click="removePartner(index)">
                            <Trash2 class="size-4" />
                        </Button>
                    </div>
                    <p v-if="form.partners.length === 0" class="text-sm text-muted-foreground">Belum ada mitra ditambahkan.</p>
                    <Button type="button" variant="outline" size="sm" class="w-fit" @click="addPartner">
                        <Plus class="size-4" /> Tambah Mitra
                    </Button>
                </CardContent>
            </Card>

            <Button type="submit" size="lg" class="w-full sm:w-fit" :disabled="form.processing">Simpan Perubahan</Button>
        </form>
    </AppLayout>
</template>
