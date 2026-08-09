<script setup lang="ts">
import BarChart from '@/components/charts/BarChart.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { participantTypeLabels, registrationStatusLabels, registrationStatusVariants, toChartData } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Registration {
    id: number;
    registration_number: string;
    participant_type: string;
    status: string;
    user: { name: string; email: string };
}

const props = defineProps<{
    registrations: { data: Registration[]; links: { url: string | null; label: string; active: boolean }[] };
    participantsByType: Record<string, number>;
    participantsByStatus: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Peserta', href: '/admin/participants' }];

const typeChartData = computed(() => toChartData(props.participantsByType, participantTypeLabels));
const statusChartData = computed(() => toChartData(props.participantsByStatus, registrationStatusLabels));
</script>

<template>
    <Head title="Kelola Peserta" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardContent class="pt-6">
                        <BarChart title="Peserta per Jenis" :data="typeChartData" />
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <BarChart title="Peserta per Status" :data="statusChartData" />
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader><CardTitle>Daftar Peserta</CardTitle></CardHeader>
                <CardContent>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="py-2">Nomor</th>
                                <th>Nama</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="registration in registrations.data" :key="registration.id" class="border-b last:border-0">
                                <td class="py-2 font-mono">{{ registration.registration_number }}</td>
                                <td>{{ registration.user.name }}</td>
                                <td>{{ participantTypeLabels[registration.participant_type] ?? registration.participant_type }}</td>
                                <td>
                                    <Badge :variant="registrationStatusVariants[registration.status] ?? 'secondary'">
                                        {{ registrationStatusLabels[registration.status] ?? registration.status }}
                                    </Badge>
                                </td>
                                <td>
                                    <Link :href="route('admin.participants.show', registration.id)">
                                        <Button variant="outline" size="sm">Detail</Button>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <Link
                            v-for="link in registrations.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            :class="[
                                'rounded px-3 py-1 text-sm',
                                link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground',
                                !link.url && 'pointer-events-none opacity-50',
                            ]"
                        >
                            <span v-html="link.label" />
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
