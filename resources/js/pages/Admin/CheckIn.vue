<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CameraOff, QrCode } from 'lucide-vue-next';
import QrScanner from 'qr-scanner';
import { onMounted, onUnmounted, ref } from 'vue';

interface RecentCheckIn {
    id: number;
    registration_number: string;
    user: { name: string };
    attendances: { checked_in_at: string | null }[];
}

defineProps<{
    status?: string;
    checkedInName?: string;
    recentCheckIns: RecentCheckIn[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Check-in QR', href: '/admin/check-in' }];

const videoEl = ref<HTMLVideoElement>();
const cameraAvailable = ref(true);
const scanning = ref(false);
let scanner: QrScanner | null = null;

const manualForm = useForm({ registration_number: '' });

function submitScan(registrationNumber: string) {
    if (!registrationNumber.trim()) {
        return;
    }

    scanning.value = false;

    router.post(
        route('admin.check-in.store'),
        { registration_number: registrationNumber.trim() },
        {
            preserveScroll: true,
            onFinish: () => {
                manualForm.reset();
                scanning.value = true;
            },
        },
    );
}

function submitManual() {
    submitScan(manualForm.registration_number);
}

onMounted(async () => {
    cameraAvailable.value = await QrScanner.hasCamera();

    if (!cameraAvailable.value || !videoEl.value) {
        return;
    }

    scanner = new QrScanner(
        videoEl.value,
        (result) => {
            if (scanning.value) {
                submitScan(result.data);
            }
        },
        { highlightScanRegion: true, highlightCodeOutline: true, maxScansPerSecond: 2 },
    );

    scanning.value = true;
    await scanner.start();
});

onUnmounted(() => {
    scanner?.destroy();
    scanner = null;
});
</script>

<template>
    <Head title="Check-in QR" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <div
                v-if="status === 'checked-in'"
                class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300"
            >
                Check-in berhasil{{ checkedInName ? ` — ${checkedInName}` : '' }}.
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2"><QrCode class="size-5" /> Pindai Tiket QR</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="overflow-hidden rounded-lg border bg-black">
                        <video ref="videoEl" class="aspect-square w-full object-cover" muted playsinline></video>
                    </div>
                    <p v-if="!cameraAvailable" class="flex items-center gap-2 text-sm text-muted-foreground">
                        <CameraOff class="size-4" /> Kamera tidak tersedia. Masukkan nomor registrasi secara manual di bawah.
                    </p>

                    <form class="flex items-center gap-2" @submit.prevent="submitManual">
                        <Input v-model="manualForm.registration_number" placeholder="Atau masukkan nomor registrasi manual" />
                        <Button type="submit" :disabled="manualForm.processing">Check-in</Button>
                    </form>
                    <p v-if="manualForm.errors.registration_number" class="text-sm text-destructive">
                        {{ manualForm.errors.registration_number }}
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle class="text-base">Check-in Terbaru</CardTitle></CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <div
                        v-for="registration in recentCheckIns"
                        :key="registration.id"
                        class="flex items-center justify-between border-b pb-2 last:border-0"
                    >
                        <div>
                            <p class="font-medium">{{ registration.user.name }}</p>
                            <p class="font-mono text-xs text-muted-foreground">{{ registration.registration_number }}</p>
                        </div>
                        <div class="text-right">
                            <Badge variant="success">Hadir</Badge>
                            <p v-if="registration.attendances[0]?.checked_in_at" class="mt-1 text-xs text-muted-foreground">
                                {{ new Date(registration.attendances[0].checked_in_at).toLocaleTimeString('id-ID') }}
                            </p>
                        </div>
                    </div>
                    <p v-if="recentCheckIns.length === 0" class="text-muted-foreground">Belum ada peserta yang check-in.</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
