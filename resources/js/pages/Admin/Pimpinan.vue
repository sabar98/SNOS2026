<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useInitials } from '@/composables/useInitials';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Crown, Pencil, Search, Trash2, UserPlus, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Leader {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    pimpinan: Leader[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Pimpinan', href: '/admin/pimpinan' }];

const { getInitials } = useInitials();

const search = ref('');

const filteredPimpinan = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return props.pimpinan;
    return props.pimpinan.filter((p) => p.name.toLowerCase().includes(query) || p.email.toLowerCase().includes(query));
});

function destroy(leader: Leader) {
    if (!confirm(`Hapus akun pimpinan "${leader.name}"?`)) {
        return;
    }
    router.delete(route('admin.pimpinan.destroy', leader.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Pimpinan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="Crown"
                icon-class="bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400"
                title="Pimpinan"
                description="Kelola akun pimpinan dengan akses dashboard statistik SNOS 2026."
            >
                <template #actions>
                    <Link :href="route('admin.pimpinan.create')">
                        <Button class="gap-2"><UserPlus class="size-4" /> Tambah Pimpinan</Button>
                    </Link>
                </template>
            </PageHeader>

            <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                <CardContent class="flex items-center gap-4 pt-6">
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400"
                    >
                        <Users class="size-5" />
                    </span>
                    <div>
                        <p class="text-2xl font-bold tracking-tight">{{ pimpinan.length }}</p>
                        <p class="text-sm text-muted-foreground">Akun Pimpinan</p>
                    </div>
                </CardContent>
            </Card>

            <Card class="border-amber-100 bg-amber-50 dark:border-border dark:bg-amber-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <CardTitle class="flex items-center gap-2"><Users class="size-4 text-muted-foreground" /> Daftar Pimpinan</CardTitle>
                    <div class="relative w-full max-w-xs">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" placeholder="Cari nama atau email..." class="pl-9" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[480px] text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="py-2">Nama</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="leader in filteredPimpinan"
                                    :key="leader.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-3">
                                        <div class="flex items-center gap-3">
                                            <Avatar size="sm" shape="circle" class="shrink-0 bg-amber-100 dark:bg-amber-950/60">
                                                <AvatarFallback class="text-amber-700 dark:text-amber-400">{{
                                                    getInitials(leader.name) || '?'
                                                }}</AvatarFallback>
                                            </Avatar>
                                            <div>
                                                <p class="font-medium">{{ leader.name }}</p>
                                                <p class="text-xs text-muted-foreground">{{ leader.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('admin.pimpinan.edit', leader.id)">
                                                <Button variant="outline" size="sm" class="gap-1.5"><Pencil class="size-3.5" /> Ubah</Button>
                                            </Link>
                                            <Button variant="destructive" size="sm" class="gap-1.5" @click="destroy(leader)">
                                                <Trash2 class="size-3.5" /> Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredPimpinan.length === 0">
                                    <td colspan="2" class="py-10 text-center text-muted-foreground">
                                        {{ search ? 'Tidak ada pimpinan yang cocok dengan pencarian.' : 'Belum ada akun pimpinan.' }}
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
