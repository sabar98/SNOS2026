<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    articleStatusLabels,
    articleStatusVariants,
    paymentStatusLabels,
    paymentStatusVariants,
    publicationStatusLabels,
    publicationStatusVariants,
    reviewRecommendationLabels,
    reviewRecommendationVariants,
} from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    Award,
    BookOpen,
    CalendarClock,
    CheckCircle2,
    Clock,
    CreditCard,
    Download,
    ExternalLink,
    FileText,
    History,
    Image as ImageIcon,
    Presentation,
    Star,
    Upload,
    User,
    Video,
} from 'lucide-vue-next';

interface Review {
    id: number;
    recommendation: string;
    comments: string | null;
}

interface Revision {
    id: number;
    version_number: number;
    submitted_at: string | null;
}

interface Loa {
    loa_number: string;
    issued_at: string;
    file_path: string | null;
}

interface ArticlePayment {
    id: number;
    amount: string;
    bank_account: string | null;
    payment_code: string;
    due_at: string | null;
    status: string;
    notes: string | null;
}

interface PresentationMaterial {
    slide_path: string | null;
    video_path: string | null;
    short_bio: string | null;
    official_photo_path: string | null;
    consent_confirmed_at: string | null;
}

interface Publication {
    status: string;
    volume: string | null;
    issue_number: string | null;
    doi: string | null;
    article_url: string | null;
    published_at: string | null;
    journal: { name: string } | null;
}

interface Article {
    id: number;
    title: string;
    status: string;
    admin_notes: string | null;
    file_path: string | null;
    reviews: Review[];
    revisions: Revision[];
    letter_of_acceptance: Loa | null;
    presentation_material: PresentationMaterial | null;
    publication: Publication | null;
    payments: ArticlePayment[];
}

const props = defineProps<{
    article: Article;
    presentationMaterialDeadline: string;
    presentationMaterialDeadlinePassed: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: props.article.title, href: '#' }];

const needsRevision = ['revisi_minor', 'revisi_mayor'].includes(props.article.status);
const canUploadMaterial = props.article.status === 'diterima';

const formattedMaterialDeadline = new Date(props.presentationMaterialDeadline).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

const revisionForm = useForm({
    file: null as File | null,
    response_to_reviewer: '',
});

function submitRevision() {
    revisionForm.post(route('participant.articles.revisions.store', props.article.id), {
        forceFormData: true,
        preserveScroll: true,
    });
}

const proofForm = useForm({
    proof: null as File | null,
});

function uploadProof(paymentId: number) {
    proofForm.post(route('participant.payments.upload', paymentId), {
        forceFormData: true,
        preserveScroll: true,
    });
}

function formatRupiah(value: string): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value));
}

function formatDate(value: string | null): string {
    if (!value) return '-';
    return new Date(value).toLocaleString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

// Materi presentasi diunggah per-berkas (bukan satu permintaan gabungan) supaya setiap
// unggahan berukuran kecil, punya progress sendiri, dan tidak saling menggagalkan.
const materialEndpoint = route('participant.articles.presentation-material.store', props.article.id);

const slideForm = useForm({ slide: null as File | null });
function uploadSlide(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    if (!file) return;
    slideForm.slide = file;
    slideForm.post(materialEndpoint, { forceFormData: true, preserveScroll: true, onSuccess: () => slideForm.reset() });
}

const videoForm = useForm({ video: null as File | null });
function uploadVideo(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    if (!file) return;
    videoForm.video = file;
    videoForm.post(materialEndpoint, { forceFormData: true, preserveScroll: true, onSuccess: () => videoForm.reset() });
}

const photoForm = useForm({ official_photo: null as File | null });
function uploadPhoto(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    if (!file) return;
    photoForm.official_photo = file;
    photoForm.post(materialEndpoint, { forceFormData: true, preserveScroll: true, onSuccess: () => photoForm.reset() });
}

const detailForm = useForm({
    short_bio: props.article.presentation_material?.short_bio ?? '',
    consent: !!props.article.presentation_material?.consent_confirmed_at,
});
function submitDetail() {
    detailForm.post(materialEndpoint, { preserveScroll: true });
}

const fileInputClass =
    'flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-muted file:px-3 file:py-1.5 file:text-xs file:font-medium';
</script>

<template>
    <Head :title="article.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
            <PageHeader
                :icon="FileText"
                icon-class="bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400"
                title="Detail Artikel"
                description="Status review, pembayaran publikasi, dan hasil publikasi artikel Anda."
            />

            <Card class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-2 space-y-0">
                    <CardTitle>{{ article.title }}</CardTitle>
                    <Badge :variant="articleStatusVariants[article.status] ?? 'secondary'">
                        {{ articleStatusLabels[article.status] ?? article.status }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-3 text-sm">
                    <div
                        v-if="article.admin_notes"
                        class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300"
                    >
                        <AlertCircle class="mt-0.5 size-4 shrink-0" />
                        <p>Catatan admin: {{ article.admin_notes }}</p>
                    </div>
                    <div v-if="article.letter_of_acceptance" class="flex items-center gap-2 rounded-lg border bg-background/70 px-3 py-2.5">
                        <Award class="size-4 shrink-0 text-violet-700 dark:text-violet-400" />
                        <p>
                            LoA: <span class="font-mono">{{ article.letter_of_acceptance.loa_number }}</span>
                        </p>
                        <a
                            v-if="article.letter_of_acceptance.file_path"
                            :href="`/storage/${article.letter_of_acceptance.file_path}`"
                            download
                            class="ml-auto"
                        >
                            <Button variant="outline" size="sm" class="gap-1.5"><Download class="size-3.5" /> Unduh LoA</Button>
                        </a>
                    </div>
                </CardContent>
            </Card>

            <Card class="overflow-hidden border-indigo-100 bg-indigo-50 dark:border-border dark:bg-indigo-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <FileText class="size-4 text-indigo-700 dark:text-indigo-400" /> Berkas Artikel (PDF)
                    </CardTitle>
                    <a v-if="article.file_path" :href="`/storage/${article.file_path}`" download>
                        <Button variant="outline" size="sm" class="gap-1.5"><Download class="size-3.5" /> Unduh</Button>
                    </a>
                </CardHeader>
                <CardContent class="p-0">
                    <div v-if="article.file_path" class="overflow-hidden rounded-b-xl border-t bg-background">
                        <iframe :src="`/storage/${article.file_path}`" class="h-[560px] w-full" title="Berkas artikel PDF Anda"></iframe>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center gap-2 border-t px-6 py-16 text-center text-muted-foreground">
                        <FileText class="size-8" />
                        <p class="text-sm">Belum ada berkas PDF yang tersimpan untuk artikel ini.</p>
                    </div>
                </CardContent>
            </Card>

            <Card v-for="payment in article.payments" :key="payment.id" class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <CreditCard class="size-4 text-amber-700 dark:text-amber-400" /> Pembayaran Publikasi
                    </CardTitle>
                    <Badge :variant="paymentStatusVariants[payment.status] ?? 'secondary'">
                        {{ paymentStatusLabels[payment.status] ?? payment.status }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <div class="grid gap-2 rounded-lg border bg-background/70 p-3 sm:grid-cols-2">
                        <p>
                            Nominal: <span class="font-semibold">{{ formatRupiah(payment.amount) }}</span>
                        </p>
                        <p v-if="payment.bank_account">Rekening tujuan: {{ payment.bank_account }}</p>
                        <p>
                            Kode pembayaran: <span class="font-mono">{{ payment.payment_code }}</span>
                        </p>
                        <p v-if="payment.due_at" class="flex items-center gap-1.5">
                            <Clock class="size-3.5 text-muted-foreground" /> Batas waktu: {{ formatDate(payment.due_at) }}
                        </p>
                    </div>
                    <div
                        v-if="payment.notes"
                        class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-100/60 px-3 py-2 text-amber-800 dark:border-amber-900 dark:bg-amber-950/60 dark:text-amber-300"
                    >
                        <AlertCircle class="mt-0.5 size-4 shrink-0" />
                        <p>Catatan panitia: {{ payment.notes }}</p>
                    </div>

                    <form
                        v-if="payment.status === 'belum_bayar' || payment.status === 'perlu_perbaikan'"
                        class="flex flex-wrap items-center gap-2 pt-2"
                        @submit.prevent="uploadProof(payment.id)"
                    >
                        <input
                            type="file"
                            accept="image/*,.pdf"
                            aria-label="Unggah bukti pembayaran"
                            :class="fileInputClass"
                            @change="proofForm.proof = ($event.target as HTMLInputElement).files?.[0] ?? null"
                        />
                        <Button type="submit" size="sm" class="gap-1.5" :disabled="proofForm.processing">
                            <Upload class="size-3.5" /> Unggah Bukti
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card v-if="article.reviews.length" class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Star class="size-4 text-emerald-700 dark:text-emerald-400" /> Hasil Review
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 text-sm">
                    <div v-for="review in article.reviews" :key="review.id" class="space-y-2 rounded-lg border bg-background/70 p-3 last:mb-0">
                        <Badge :variant="reviewRecommendationVariants[review.recommendation] ?? 'secondary'">
                            {{ reviewRecommendationLabels[review.recommendation] ?? review.recommendation }}
                        </Badge>
                        <p v-if="review.comments" class="text-muted-foreground">{{ review.comments }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="needsRevision" class="border-orange-100 bg-orange-50 dark:border-border dark:bg-orange-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Upload class="size-4 text-orange-700 dark:text-orange-400" /> Unggah Revisi
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-col gap-4" @submit.prevent="submitRevision">
                        <div class="grid gap-1.5">
                            <label for="revision_file" class="text-sm font-medium">File Revisi (.doc, .docx, .pdf)</label>
                            <input
                                id="revision_file"
                                type="file"
                                accept=".doc,.docx,.pdf"
                                :class="fileInputClass"
                                @change="revisionForm.file = ($event.target as HTMLInputElement).files?.[0] ?? null"
                            />
                            <InputError :message="revisionForm.errors.file" />
                        </div>
                        <div class="grid gap-1.5">
                            <label for="revision_response" class="text-sm font-medium">Tanggapan terhadap Reviewer</label>
                            <textarea
                                id="revision_response"
                                v-model="revisionForm.response_to_reviewer"
                                rows="3"
                                placeholder="Tanggapan terhadap reviewer"
                                class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                            ></textarea>
                            <InputError :message="revisionForm.errors.response_to_reviewer" />
                        </div>
                        <Button type="submit" class="w-fit gap-2" :disabled="revisionForm.processing">
                            <Upload class="size-4" /> Kirim Revisi
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card v-if="article.revisions.length" class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <History class="size-4 text-sky-700 dark:text-sky-400" /> Riwayat Revisi
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <div
                        v-for="revision in article.revisions"
                        :key="revision.id"
                        class="flex items-center justify-between rounded-lg border bg-background/70 px-3 py-2"
                    >
                        <span class="font-medium">Versi {{ revision.version_number }}</span>
                        <span class="flex items-center gap-1.5 text-muted-foreground">
                            <CalendarClock class="size-3.5" /> {{ revision.submitted_at ? formatDate(revision.submitted_at) : '-' }}
                        </span>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="article.publication" class="border-rose-100 bg-rose-50 dark:border-border dark:bg-rose-950/40">
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <BookOpen class="size-4 text-rose-700 dark:text-rose-400" /> Publikasi
                    </CardTitle>
                    <Badge :variant="publicationStatusVariants[article.publication.status] ?? 'secondary'">
                        {{ publicationStatusLabels[article.publication.status] ?? article.publication.status }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <div class="grid gap-1.5 rounded-lg border bg-background/70 p-3">
                        <p v-if="article.publication.journal">Jurnal/Prosiding: {{ article.publication.journal.name }}</p>
                        <p v-if="article.publication.volume">Volume: {{ article.publication.volume }}</p>
                        <p v-if="article.publication.issue_number">Nomor Terbitan: {{ article.publication.issue_number }}</p>
                        <p v-if="article.publication.doi">
                            DOI: <span class="font-mono">{{ article.publication.doi }}</span>
                        </p>
                        <p v-if="article.publication.published_at" class="text-muted-foreground">
                            Terbit {{ new Date(article.publication.published_at).toLocaleDateString('id-ID') }}
                        </p>
                    </div>
                    <a v-if="article.publication.article_url" :href="article.publication.article_url" target="_blank" rel="noopener">
                        <Button variant="outline" size="sm" class="gap-1.5"> <ExternalLink class="size-3.5" /> Lihat artikel terbit </Button>
                    </a>
                </CardContent>
            </Card>

            <Card v-if="canUploadMaterial" class="border-teal-100 bg-teal-50 dark:border-border dark:bg-teal-950/40">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Presentation class="size-4 text-teal-700 dark:text-teal-400" /> Materi Presentasi
                    </CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    <div
                        :class="[
                            'flex items-start gap-2 rounded-lg border px-4 py-3 text-sm',
                            presentationMaterialDeadlinePassed
                                ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300'
                                : 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300',
                        ]"
                    >
                        <Clock class="mt-0.5 size-4 shrink-0" />
                        <p>
                            Batas waktu unggah materi: <span class="font-medium">{{ formattedMaterialDeadline }}</span>
                            <span v-if="presentationMaterialDeadlinePassed"> &mdash; batas waktu telah berakhir.</span>
                        </p>
                    </div>

                    <p class="text-xs text-muted-foreground">
                        Setiap berkas diunggah dan tersimpan secara terpisah begitu dipilih, sehingga lebih cepat dan tidak perlu mengunggah ulang
                        berkas lain jika salah satu gagal.
                    </p>

                    <fieldset :disabled="presentationMaterialDeadlinePassed" class="flex flex-col gap-4 disabled:opacity-60">
                        <div class="grid gap-1.5">
                            <label for="material_slide" class="text-sm font-medium">File PowerPoint (.ppt, .pptx, .pdf)</label>
                            <div
                                v-if="article.presentation_material?.slide_path"
                                class="flex items-center justify-between gap-2 rounded-lg border bg-background/70 px-3 py-2 text-sm"
                            >
                                <a
                                    :href="`/storage/${article.presentation_material.slide_path}`"
                                    download
                                    class="flex items-center gap-1.5 text-primary hover:underline"
                                >
                                    <FileText class="size-3.5" /> File tersimpan
                                </a>
                                <Badge variant="success" class="gap-1"><CheckCircle2 class="size-3" /> Tersimpan</Badge>
                            </div>
                            <input
                                id="material_slide"
                                type="file"
                                accept=".ppt,.pptx,.pdf"
                                :disabled="!!slideForm.progress"
                                :class="fileInputClass"
                                @change="uploadSlide"
                            />
                            <div v-if="slideForm.progress" class="flex items-center gap-2 text-xs text-muted-foreground">
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-muted">
                                    <div
                                        class="h-full rounded-full bg-teal-600 transition-all"
                                        :style="{ width: (slideForm.progress.percentage ?? 0) + '%' }"
                                    ></div>
                                </div>
                                <span class="tabular-nums">{{ slideForm.progress.percentage ?? 0 }}%</span>
                            </div>
                            <InputError :message="slideForm.errors.slide" />
                        </div>

                        <div class="grid gap-1.5">
                            <label for="material_video" class="text-sm font-medium">Video Presentasi (.mp4, opsional)</label>
                            <div
                                v-if="article.presentation_material?.video_path"
                                class="flex items-center justify-between gap-2 rounded-lg border bg-background/70 px-3 py-2 text-sm"
                            >
                                <a
                                    :href="`/storage/${article.presentation_material.video_path}`"
                                    download
                                    class="flex items-center gap-1.5 text-primary hover:underline"
                                >
                                    <Video class="size-3.5" /> Video tersimpan
                                </a>
                                <Badge variant="success" class="gap-1"><CheckCircle2 class="size-3" /> Tersimpan</Badge>
                            </div>
                            <input
                                id="material_video"
                                type="file"
                                accept="video/mp4"
                                :disabled="!!videoForm.progress"
                                :class="fileInputClass"
                                @change="uploadVideo"
                            />
                            <div v-if="videoForm.progress" class="flex items-center gap-2 text-xs text-muted-foreground">
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-muted">
                                    <div
                                        class="h-full rounded-full bg-teal-600 transition-all"
                                        :style="{ width: (videoForm.progress.percentage ?? 0) + '%' }"
                                    ></div>
                                </div>
                                <span class="tabular-nums">{{ videoForm.progress.percentage ?? 0 }}%</span>
                            </div>
                            <InputError :message="videoForm.errors.video" />
                        </div>

                        <div class="grid gap-1.5">
                            <label for="material_official_photo" class="text-sm font-medium">Foto Resmi</label>
                            <div
                                v-if="article.presentation_material?.official_photo_path"
                                class="flex items-center justify-between gap-2 rounded-lg border bg-background/70 px-3 py-2 text-sm"
                            >
                                <a
                                    :href="`/storage/${article.presentation_material.official_photo_path}`"
                                    download
                                    class="flex items-center gap-1.5 text-primary hover:underline"
                                >
                                    <ImageIcon class="size-3.5" /> Foto tersimpan
                                </a>
                                <Badge variant="success" class="gap-1"><CheckCircle2 class="size-3" /> Tersimpan</Badge>
                            </div>
                            <input
                                id="material_official_photo"
                                type="file"
                                accept="image/*"
                                :disabled="!!photoForm.progress"
                                :class="fileInputClass"
                                @change="uploadPhoto"
                            />
                            <div v-if="photoForm.progress" class="flex items-center gap-2 text-xs text-muted-foreground">
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-muted">
                                    <div
                                        class="h-full rounded-full bg-teal-600 transition-all"
                                        :style="{ width: (photoForm.progress.percentage ?? 0) + '%' }"
                                    ></div>
                                </div>
                                <span class="tabular-nums">{{ photoForm.progress.percentage ?? 0 }}%</span>
                            </div>
                            <InputError :message="photoForm.errors.official_photo" />
                        </div>
                    </fieldset>

                    <div
                        v-if="article.presentation_material?.consent_confirmed_at"
                        class="flex items-center gap-1.5 rounded-lg border border-teal-200 bg-teal-100/60 px-3 py-2 text-sm text-teal-800 dark:border-teal-900 dark:bg-teal-950/60 dark:text-teal-300"
                    >
                        <CheckCircle2 class="size-3.5 shrink-0" /> Kesediaan presentasi dikonfirmasi
                        {{ formatDate(article.presentation_material.consent_confirmed_at) }}
                    </div>

                    <form v-if="!presentationMaterialDeadlinePassed" class="flex flex-col gap-3 border-t pt-4" @submit.prevent="submitDetail">
                        <div class="grid gap-1.5">
                            <label for="material_short_bio" class="flex items-center gap-1.5 text-sm font-medium">
                                <User class="size-3.5 text-muted-foreground" /> Biodata Singkat
                            </label>
                            <textarea
                                id="material_short_bio"
                                v-model="detailForm.short_bio"
                                rows="3"
                                class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                            ></textarea>
                            <InputError :message="detailForm.errors.short_bio" />
                        </div>

                        <label for="material_consent" class="flex items-center gap-2 text-sm">
                            <input id="material_consent" v-model="detailForm.consent" type="checkbox" class="h-4 w-4 rounded border-input" />
                            Saya menyatakan kesediaan untuk mempresentasikan artikel ini
                        </label>
                        <InputError :message="detailForm.errors.consent" />

                        <Button type="submit" class="w-fit gap-2" :disabled="detailForm.processing">
                            <CheckCircle2 class="size-4" /> Simpan Biodata &amp; Kesediaan
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
