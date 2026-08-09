<script setup lang="ts">
import InputError from '@/components/InputError.vue';
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

const materialForm = useForm({
    slide: null as File | null,
    video: null as File | null,
    short_bio: props.article.presentation_material?.short_bio ?? '',
    official_photo: null as File | null,
    consent: !!props.article.presentation_material?.consent_confirmed_at,
});

function submitMaterial() {
    materialForm.post(route('participant.articles.presentation-material.store', props.article.id), {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="article.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle>{{ article.title }}</CardTitle>
                    <Badge :variant="articleStatusVariants[article.status] ?? 'secondary'">
                        {{ articleStatusLabels[article.status] ?? article.status }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-1 text-sm">
                    <p v-if="article.admin_notes" class="text-amber-600">Catatan admin: {{ article.admin_notes }}</p>
                    <p v-if="article.letter_of_acceptance">
                        LoA: <span class="font-mono">{{ article.letter_of_acceptance.loa_number }}</span>
                        <a
                            v-if="article.letter_of_acceptance.file_path"
                            :href="`/storage/${article.letter_of_acceptance.file_path}`"
                            target="_blank"
                            class="ml-2 text-primary underline"
                        >
                            Unduh LoA
                        </a>
                    </p>
                </CardContent>
            </Card>

            <Card v-for="payment in article.payments" :key="payment.id">
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle class="text-base">Pembayaran Publikasi</CardTitle>
                    <Badge :variant="paymentStatusVariants[payment.status] ?? 'secondary'">
                        {{ paymentStatusLabels[payment.status] ?? payment.status }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <p>
                        Nominal: <span class="font-medium">{{ formatRupiah(payment.amount) }}</span>
                    </p>
                    <p v-if="payment.bank_account">Rekening tujuan: {{ payment.bank_account }}</p>
                    <p>
                        Kode pembayaran: <span class="font-mono">{{ payment.payment_code }}</span>
                    </p>
                    <p v-if="payment.due_at">Batas waktu: {{ new Date(payment.due_at).toLocaleString('id-ID') }}</p>
                    <p v-if="payment.notes" class="text-amber-600">Catatan panitia: {{ payment.notes }}</p>

                    <form
                        v-if="payment.status === 'belum_bayar' || payment.status === 'perlu_perbaikan'"
                        class="flex items-center gap-2 pt-2"
                        @submit.prevent="uploadProof(payment.id)"
                    >
                        <input
                            type="file"
                            accept="image/*,.pdf"
                            aria-label="Unggah bukti pembayaran"
                            class="text-sm"
                            @change="proofForm.proof = ($event.target as HTMLInputElement).files?.[0] ?? null"
                        />
                        <Button type="submit" size="sm" :disabled="proofForm.processing">Unggah Bukti</Button>
                    </form>
                </CardContent>
            </Card>

            <Card v-if="article.reviews.length">
                <CardHeader>
                    <CardTitle class="text-base">Hasil Review</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 text-sm">
                    <div v-for="review in article.reviews" :key="review.id" class="space-y-1 border-b pb-2">
                        <Badge :variant="reviewRecommendationVariants[review.recommendation] ?? 'secondary'">
                            {{ reviewRecommendationLabels[review.recommendation] ?? review.recommendation }}
                        </Badge>
                        <p v-if="review.comments" class="text-muted-foreground">{{ review.comments }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="needsRevision">
                <CardHeader>
                    <CardTitle class="text-base">Unggah Revisi</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-col gap-3" @submit.prevent="submitRevision">
                        <label for="revision_file" class="text-sm font-medium">File Revisi (.doc, .docx, .pdf)</label>
                        <input
                            id="revision_file"
                            type="file"
                            accept=".doc,.docx,.pdf"
                            @change="revisionForm.file = ($event.target as HTMLInputElement).files?.[0] ?? null"
                        />
                        <InputError :message="revisionForm.errors.file" />
                        <label for="revision_response" class="text-sm font-medium">Tanggapan terhadap Reviewer</label>
                        <textarea
                            id="revision_response"
                            v-model="revisionForm.response_to_reviewer"
                            rows="3"
                            placeholder="Tanggapan terhadap reviewer"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                        ></textarea>
                        <InputError :message="revisionForm.errors.response_to_reviewer" />
                        <Button type="submit" :disabled="revisionForm.processing">Kirim Revisi</Button>
                    </form>
                </CardContent>
            </Card>

            <Card v-if="article.revisions.length">
                <CardHeader>
                    <CardTitle class="text-base">Riwayat Revisi</CardTitle>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <p v-for="revision in article.revisions" :key="revision.id">
                        Versi {{ revision.version_number }} &mdash;
                        {{ revision.submitted_at ? new Date(revision.submitted_at).toLocaleString('id-ID') : '-' }}
                    </p>
                </CardContent>
            </Card>

            <Card v-if="article.publication" class="border-rose-200 bg-rose-50 dark:border-rose-900 dark:bg-rose-950/40">
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle class="text-base text-rose-800 dark:text-rose-300">Publikasi</CardTitle>
                    <Badge :variant="publicationStatusVariants[article.publication.status] ?? 'secondary'">
                        {{ publicationStatusLabels[article.publication.status] ?? article.publication.status }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-1 text-sm">
                    <p v-if="article.publication.journal">Jurnal/Prosiding: {{ article.publication.journal.name }}</p>
                    <p v-if="article.publication.volume">Volume: {{ article.publication.volume }}</p>
                    <p v-if="article.publication.issue_number">Nomor Terbitan: {{ article.publication.issue_number }}</p>
                    <p v-if="article.publication.doi">
                        DOI: <span class="font-mono">{{ article.publication.doi }}</span>
                    </p>
                    <p v-if="article.publication.article_url">
                        <a :href="article.publication.article_url" target="_blank" class="text-primary underline">Lihat artikel terbit</a>
                    </p>
                    <p v-if="article.publication.published_at" class="text-muted-foreground">
                        Terbit {{ new Date(article.publication.published_at).toLocaleDateString('id-ID') }}
                    </p>
                </CardContent>
            </Card>

            <Card v-if="canUploadMaterial" class="border-violet-200 bg-violet-50 dark:border-violet-900 dark:bg-violet-950/40">
                <CardHeader>
                    <CardTitle class="text-base text-violet-800 dark:text-violet-300">Materi Presentasi</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="article.presentation_material" class="mb-4 space-y-1 text-sm">
                        <p v-if="article.presentation_material.slide_path">
                            <a :href="`/storage/${article.presentation_material.slide_path}`" target="_blank" class="text-primary underline">
                                Lihat file PowerPoint tersimpan
                            </a>
                        </p>
                        <p v-if="article.presentation_material.video_path">
                            <a :href="`/storage/${article.presentation_material.video_path}`" target="_blank" class="text-primary underline">
                                Lihat video tersimpan
                            </a>
                        </p>
                        <p v-if="article.presentation_material.official_photo_path">
                            <a :href="`/storage/${article.presentation_material.official_photo_path}`" target="_blank" class="text-primary underline">
                                Lihat foto resmi tersimpan
                            </a>
                        </p>
                        <p v-if="article.presentation_material.consent_confirmed_at" class="text-muted-foreground">
                            Kesediaan presentasi dikonfirmasi
                            {{ new Date(article.presentation_material.consent_confirmed_at).toLocaleString('id-ID') }}
                        </p>
                    </div>

                    <div
                        :class="[
                            'mb-4 rounded-md border px-4 py-3 text-sm',
                            presentationMaterialDeadlinePassed
                                ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300'
                                : 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300',
                        ]"
                    >
                        Batas waktu unggah materi: <span class="font-medium">{{ formattedMaterialDeadline }}</span>
                        <span v-if="presentationMaterialDeadlinePassed"> &mdash; batas waktu telah berakhir.</span>
                    </div>

                    <form v-if="!presentationMaterialDeadlinePassed" class="flex flex-col gap-3" @submit.prevent="submitMaterial">
                        <div class="grid gap-1.5">
                            <label for="material_slide" class="text-sm font-medium">File PowerPoint (.ppt, .pptx, .pdf)</label>
                            <input
                                id="material_slide"
                                type="file"
                                accept=".ppt,.pptx,.pdf"
                                class="text-sm"
                                @change="materialForm.slide = ($event.target as HTMLInputElement).files?.[0] ?? null"
                            />
                            <InputError :message="materialForm.errors.slide" />
                        </div>

                        <div class="grid gap-1.5">
                            <label for="material_video" class="text-sm font-medium">Video Presentasi (.mp4, opsional)</label>
                            <input
                                id="material_video"
                                type="file"
                                accept="video/mp4"
                                class="text-sm"
                                @change="materialForm.video = ($event.target as HTMLInputElement).files?.[0] ?? null"
                            />
                            <InputError :message="materialForm.errors.video" />
                        </div>

                        <div class="grid gap-1.5">
                            <label for="material_short_bio" class="text-sm font-medium">Biodata Singkat</label>
                            <textarea
                                id="material_short_bio"
                                v-model="materialForm.short_bio"
                                rows="3"
                                class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                            ></textarea>
                            <InputError :message="materialForm.errors.short_bio" />
                        </div>

                        <div class="grid gap-1.5">
                            <label for="material_official_photo" class="text-sm font-medium">Foto Resmi</label>
                            <input
                                id="material_official_photo"
                                type="file"
                                accept="image/*"
                                class="text-sm"
                                @change="materialForm.official_photo = ($event.target as HTMLInputElement).files?.[0] ?? null"
                            />
                            <InputError :message="materialForm.errors.official_photo" />
                        </div>

                        <label for="material_consent" class="flex items-center gap-2 text-sm">
                            <input id="material_consent" v-model="materialForm.consent" type="checkbox" class="h-4 w-4 rounded border-input" />
                            Saya menyatakan kesediaan untuk mempresentasikan artikel ini
                        </label>
                        <InputError :message="materialForm.errors.consent" />

                        <Button type="submit" :disabled="materialForm.processing">Simpan Materi Presentasi</Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
