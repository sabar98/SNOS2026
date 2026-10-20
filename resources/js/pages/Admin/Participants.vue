<script setup lang="ts">
import DonutChart from '@/components/charts/DonutChart.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { participantTypeLabels, registrationStatusLabels, registrationStatusVariants, toChartData } from '@/lib/labels';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { FileSpreadsheet, ListChecks, Printer, Search, Users } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Registration {
    id: number;
    registration_number: string;
    participant_type: string;
    status: string;
    user: { name: string; email: string };
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    registrations: { data: Registration[]; links: PaginationLink[]; total: number };
    participantsByType: Record<string, number>;
    participantsByStatus: Record<string, number>;
    filters: { search: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Peserta', href: '/admin/participants' }];

const typeChartData = computed(() => toChartData(props.participantsByType, participantTypeLabels));
const statusChartData = computed(() => toChartData(props.participantsByStatus, registrationStatusLabels));

// Server-side search (the list is paginated, so filtering only the current page
// would hide matches on other pages) — debounced so it doesn't fire on every keystroke.
const search = ref(props.filters.search);
let searchTimeout: ReturnType<typeof setTimeout> | undefined;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.participants.index'),
            { search: value || undefined },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 400);
});

// Export/print always reflect whatever is currently searched.
const exportParams = computed(() => ({ search: search.value || undefined }));
const exportExcelUrl = computed(() => route('admin.participants.export', exportParams.value));
const exportPdfUrl = computed(() => route('admin.participants.export-pdf', exportParams.value));
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
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <div class="flex items-center gap-2">
                        <CardTitle class="flex items-center gap-2"><ListChecks class="size-4 text-muted-foreground" /> Daftar Peserta</CardTitle>
                        <Badge variant="secondary">{{ registrations.total }} pendaftaran</Badge>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="relative w-full max-w-xs">
                            <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="search" placeholder="Cari nama, email, atau nomor HP..." class="pl-9" />
                        </div>
                        <div class="flex items-center gap-2">
                            <a :href="exportExcelUrl">
                                <Button size="sm" class="gap-2 bg-emerald-600 text-white hover:bg-emerald-700">
                                    <FileSpreadsheet class="size-4" /> Export Excel
                                </Button>
                            </a>
                            <a :href="exportPdfUrl" target="_blank">
                                <Button variant="destructive" size="sm" class="gap-2"><Printer class="size-4" /> Cetak PDF</Button>
                            </a>
                        </div>
                    </div>
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
                                <tr v-if="registrations.data.length === 0">
                                    <td colspan="5" class="py-6 text-center text-muted-foreground">
                                        Tidak ada peserta yang cocok dengan pencarian.
                                    </td>
                                </tr>
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
