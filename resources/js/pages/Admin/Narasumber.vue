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
import { Award, Pencil, Presentation, Search, Trash2, UserPlus, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Speaker {
    id: number;
    name: string;
    email: string;
    certificates_count: number;
}

const props = defineProps<{
    narasumber: Speaker[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Narasumber', href: '/admin/narasumber' }];

const { getInitials } = useInitials();

const search = ref('');

const filteredNarasumber = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return props.narasumber;
    return props.narasumber.filter((s) => s.name.toLowerCase().includes(query) || s.email.toLowerCase().includes(query));
});

const totalCertificates = computed(() => props.narasumber.reduce((sum, s) => sum + s.certificates_count, 0));

function destroy(speaker: Speaker) {
    if (!confirm(`Hapus akun narasumber "${speaker.name}"?`)) {
        return;
    }
    router.delete(route('admin.narasumber.destroy', speaker.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Narasumber" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="Presentation"
                icon-class="bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400"
                title="Narasumber"
                description="Kelola akun narasumber / pembicara tamu SNOS 2026."
            >
                <template #actions>
                    <Link :href="route('admin.narasumber.create')">
                        <Button class="gap-2"><UserPlus class="size-4" /> Tambah Narasumber</Button>
                    </Link>
                </template>
            </PageHeader>

            <div class="grid gap-4 sm:grid-cols-2">
                <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400"
                        >
                            <Users class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ narasumber.length }}</p>
                            <p class="text-sm text-muted-foreground">Akun Narasumber</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400"
                        >
                            <Award class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ totalCertificates }}</p>
                            <p class="text-sm text-muted-foreground">Sertifikat Diterbitkan</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card class="border-sky-100 bg-sky-50 dark:border-border dark:bg-sky-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <CardTitle class="flex items-center gap-2"><Users class="size-4 text-muted-foreground" /> Daftar Narasumber</CardTitle>
                    <div class="relative w-full max-w-xs">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" placeholder="Cari nama atau email..." class="pl-9" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[600px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="py-2">Nama</th>
                                    <th>Sertifikat</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="speaker in filteredNarasumber"
                                    :key="speaker.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-3">
                                        <div class="flex items-center gap-3">
                                            <Avatar size="sm" shape="circle" class="shrink-0 bg-sky-100 dark:bg-sky-950/60">
                                                <AvatarFallback class="text-sky-700 dark:text-sky-400">{{
                                                    getInitials(speaker.name) || '?'
                                                }}</AvatarFallback>
                                            </Avatar>
                                            <div>
                                                <p class="font-medium">{{ speaker.name }}</p>
                                                <p class="text-xs text-muted-foreground">{{ speaker.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <Badge variant="secondary">{{ speaker.certificates_count }}</Badge>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('admin.narasumber.edit', speaker.id)">
                                                <Button variant="outline" size="sm" class="gap-1.5"><Pencil class="size-3.5" /> Ubah</Button>
                                            </Link>
                                            <Button variant="destructive" size="sm" class="gap-1.5" @click="destroy(speaker)">
                                                <Trash2 class="size-3.5" /> Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredNarasumber.length === 0">
                                    <td colspan="3" class="py-10 text-center text-muted-foreground">
                                        {{ search ? 'Tidak ada narasumber yang cocok dengan pencarian.' : 'Belum ada akun narasumber.' }}
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
