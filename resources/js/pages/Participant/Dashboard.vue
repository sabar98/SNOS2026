<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { participantTypeLabels, registrationStatusLabels, registrationStatusVariants } from '@/lib/labels';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CalendarPlus, ClipboardList } from 'lucide-vue-next';

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
const page = usePage<SharedData>();
</script>

<template>
    <Head title="Dashboard Peserta" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="ClipboardList"
                :title="`Halo, ${page.props.auth.user.name.split(' ')[0]}`"
                description="Pantau pendaftaran dan progres kegiatan SNOS 2026 Anda di sini."
            >
                <template #actions>
                    <Link :href="route('participant.registrations.create')">
                        <Button size="sm"><CalendarPlus class="size-4" /> Daftar Kegiatan Baru</Button>
                    </Link>
                </template>
            </PageHeader>

            <Card v-if="!profileComplete" class="border-amber-400/50 bg-amber-50 dark:bg-amber-950">
                <CardContent class="flex flex-wrap items-center justify-between gap-3 py-4">
                    <p class="text-sm">Lengkapi profil Anda terlebih dahulu sebelum mendaftar kegiatan.</p>
                    <Link :href="route('participant.profile.edit')">
                        <Button size="sm">Lengkapi Profil</Button>
                    </Link>
                </CardContent>
            </Card>

            <h2 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">Pendaftaran Saya</h2>

            <EmptyState
                v-if="registrations.length === 0"
                :icon="ClipboardList"
                title="Belum ada pendaftaran"
                description='Klik "Daftar Kegiatan Baru" di atas untuk memulai pendaftaran Anda.'
            />

            <div v-for="registration in registrations" :key="registration.id" class="grid gap-4 md:grid-cols-2">
                <Card class="transition-shadow duration-200 hover:shadow-md">
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
