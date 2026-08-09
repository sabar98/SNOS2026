<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { participantTypeLabels, registrationStatusLabels, registrationStatusVariants } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

interface Registration {
    id: number;
    registration_number: string;
    participant_type: string;
    status: string;
    payments: { id: number; type: string; status: string; amount: string }[];
    articles: { id: number; title: string; status: string }[];
}

defineProps<{
    profileComplete: boolean;
    registrations: Registration[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/participant/dashboard' }];
</script>

<template>
    <Head title="Dashboard Peserta" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4">
            <Card v-if="!profileComplete" class="border-amber-400/50 bg-amber-50 dark:bg-amber-950">
                <CardContent class="flex items-center justify-between py-4">
                    <p class="text-sm">Lengkapi profil Anda terlebih dahulu sebelum mendaftar kegiatan.</p>
                    <Link :href="route('participant.profile.edit')">
                        <Button size="sm">Lengkapi Profil</Button>
                    </Link>
                </CardContent>
            </Card>

            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Pendaftaran Saya</h1>
                <Link :href="route('participant.registrations.create')">
                    <Button size="sm">Daftar Kegiatan Baru</Button>
                </Link>
            </div>

            <div v-if="registrations.length === 0" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                Belum ada pendaftaran. Klik "Daftar Kegiatan Baru" untuk memulai.
            </div>

            <div v-for="registration in registrations" :key="registration.id" class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between text-base">
                            <span>{{ registration.registration_number }}</span>
                            <Link :href="route('participant.registrations.show', registration.id)">
                                <Button variant="outline" size="sm">Detail</Button>
                            </Link>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2 text-sm">
                        <Badge :variant="registrationStatusVariants[registration.status] ?? 'secondary'">
                            {{ registrationStatusLabels[registration.status] ?? registration.status }}
                        </Badge>
                        <p class="text-muted-foreground">
                            Jenis: {{ participantTypeLabels[registration.participant_type] ?? registration.participant_type }}
                        </p>
                        <p v-if="registration.articles.length" class="text-muted-foreground">Artikel: {{ registration.articles.length }} diajukan</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
