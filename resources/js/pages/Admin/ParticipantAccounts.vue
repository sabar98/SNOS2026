<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useInitials } from '@/composables/useInitials';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { IdCard, Pencil, Search, Trash2, UserPlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface ParticipantAccount {
    id: number;
    name: string;
    email: string;
    nik: string | null;
    institution: string | null;
    whatsapp_number: string | null;
    event_registrations_count: number;
}

const props = defineProps<{
    participants: ParticipantAccount[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Akun Peserta', href: '/admin/participant-accounts' }];

const { getInitials } = useInitials();

const search = ref('');

const filteredParticipants = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return props.participants;
    return props.participants.filter(
        (p) =>
            p.name.toLowerCase().includes(query) ||
            p.email.toLowerCase().includes(query) ||
            (p.nik ?? '').toLowerCase().includes(query) ||
            (p.institution ?? '').toLowerCase().includes(query),
    );
});

function destroy(participant: ParticipantAccount) {
    const warning =
        participant.event_registrations_count > 0
            ? ` Akun ini memiliki ${participant.event_registrations_count} pendaftaran kegiatan yang akan ikut terhapus.`
            : '';
    if (!confirm(`Hapus akun peserta "${participant.name}"?${warning}`)) {
        return;
    }
    router.delete(route('admin.participant-accounts.destroy', participant.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Akun Peserta" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="IdCard"
                icon-class="bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"
                title="Akun Peserta"
                description="Kelola akun login peserta yang mendaftar sendiri melalui halaman registrasi."
            >
                <template #actions>
                    <Link :href="route('admin.participant-accounts.create')">
                        <Button class="gap-2"><UserPlus class="size-4" /> Tambah Akun</Button>
                    </Link>
                </template>
            </PageHeader>

            <Card class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                <CardContent class="flex items-center gap-4 pt-6">
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400"
                    >
                        <IdCard class="size-5" />
                    </span>
                    <div>
                        <p class="text-2xl font-bold tracking-tight">{{ participants.length }}</p>
                        <p class="text-sm text-muted-foreground">Akun Peserta</p>
                    </div>
                </CardContent>
            </Card>

            <Card class="border-emerald-100 bg-emerald-50 dark:border-border dark:bg-emerald-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <CardTitle class="flex items-center gap-2"><IdCard class="size-4 text-muted-foreground" /> Daftar Akun Peserta</CardTitle>
                    <div class="relative w-full max-w-xs">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" placeholder="Cari nama, email, NIK, atau institusi..." class="pl-9" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[720px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="py-2">Nama</th>
                                    <th>NIK</th>
                                    <th>Institusi</th>
                                    <th>WhatsApp</th>
                                    <th>Pendaftaran</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="participant in filteredParticipants"
                                    :key="participant.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-3">
                                        <div class="flex items-center gap-3">
                                            <Avatar size="sm" shape="circle" class="shrink-0 bg-emerald-100 dark:bg-emerald-950/60">
                                                <AvatarFallback class="text-emerald-700 dark:text-emerald-400">{{
                                                    getInitials(participant.name) || '?'
                                                }}</AvatarFallback>
                                            </Avatar>
                                            <div>
                                                <p class="font-medium">{{ participant.name }}</p>
                                                <p class="text-xs text-muted-foreground">{{ participant.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-mono text-muted-foreground">{{ participant.nik ?? '-' }}</td>
                                    <td>{{ participant.institution ?? '-' }}</td>
                                    <td>{{ participant.whatsapp_number ?? '-' }}</td>
                                    <td>
                                        <Badge variant="secondary">{{ participant.event_registrations_count }} kegiatan</Badge>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('admin.participant-accounts.edit', participant.id)">
                                                <Button variant="outline" size="sm" class="gap-1.5"><Pencil class="size-3.5" /> Ubah</Button>
                                            </Link>
                                            <Button variant="destructive" size="sm" class="gap-1.5" @click="destroy(participant)">
                                                <Trash2 class="size-3.5" /> Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredParticipants.length === 0">
                                    <td colspan="6" class="py-10 text-center text-muted-foreground">
                                        {{ search ? 'Tidak ada akun yang cocok dengan pencarian.' : 'Belum ada akun peserta.' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
