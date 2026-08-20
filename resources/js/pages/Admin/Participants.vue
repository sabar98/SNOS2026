<script setup lang="ts">
import DonutChart from '@/components/charts/DonutChart.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { participantTypeLabels, registrationStatusLabels, registrationStatusVariants, toChartData } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ListChecks, Users } from 'lucide-vue-next';
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
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader :icon="Users" title="Peserta" description="Kelola seluruh pendaftaran peserta SNOS 2026." />

            <div class="grid gap-4 md:grid-cols-2">
                <Card class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                    <CardContent class="pt-6">
                        <DonutChart title="Peserta per Jenis" :data="typeChartData" />
                    </CardContent>
                </Card>
                <Card class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                    <CardContent class="pt-6">
                        <DonutChart title="Peserta per Status" :data="statusChartData" />
                    </CardContent>
                </Card>
            </div>

            <Card class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle class="flex items-center gap-2"><ListChecks class="size-4 text-muted-foreground" /> Daftar Peserta</CardTitle>
                    <Badge variant="secondary">{{ registrations.data.length }} pendaftaran</Badge>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[600px] text-sm">
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
                                <tr
                                    v-for="registration in registrations.data"
                                    :key="registration.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
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
                    </div>

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
