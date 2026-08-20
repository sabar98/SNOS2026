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
import { CalendarClock, Mic, Pencil, Search, Trash2, UserPlus, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Moderator {
    id: number;
    name: string;
    email: string;
    moderated_sessions_count: number;
}

const props = defineProps<{
    moderators: Moderator[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Moderator', href: '/admin/moderators' }];

const { getInitials } = useInitials();

const search = ref('');

const filteredModerators = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return props.moderators;
    return props.moderators.filter((m) => m.name.toLowerCase().includes(query) || m.email.toLowerCase().includes(query));
});

const totalSessions = computed(() => props.moderators.reduce((sum, m) => sum + m.moderated_sessions_count, 0));

function destroy(moderator: Moderator) {
    if (!confirm(`Hapus akun moderator "${moderator.name}"?`)) {
        return;
    }
    router.delete(route('admin.moderators.destroy', moderator.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Moderator" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <PageHeader
                :icon="Mic"
                icon-class="bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400"
                title="Moderator"
                description="Kelola akun moderator yang memandu sesi presentasi SNOS 2026."
            >
                <template #actions>
                    <Link :href="route('admin.moderators.create')">
                        <Button class="gap-2"><UserPlus class="size-4" /> Tambah Moderator</Button>
                    </Link>
                </template>
            </PageHeader>

            <div class="grid gap-4 sm:grid-cols-2">
                <Card class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400"
                        >
                            <Users class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ moderators.length }}</p>
                            <p class="text-sm text-muted-foreground">Akun Moderator</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-400"
                        >
                            <CalendarClock class="size-5" />
                        </span>
                        <div>
                            <p class="text-2xl font-bold tracking-tight">{{ totalSessions }}</p>
                            <p class="text-sm text-muted-foreground">Total Sesi Dimoderasi</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card class="border-violet-100 bg-violet-50 dark:border-border dark:bg-violet-950/40">
                <CardHeader class="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <CardTitle class="flex items-center gap-2"><Users class="size-4 text-muted-foreground" /> Daftar Moderator</CardTitle>
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
                                    <th>Sesi</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="moderator in filteredModerators"
                                    :key="moderator.id"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-3">
                                        <div class="flex items-center gap-3">
                                            <Avatar size="sm" shape="circle" class="shrink-0 bg-violet-100 dark:bg-violet-950/60">
                                                <AvatarFallback class="text-violet-700 dark:text-violet-400">{{
                                                    getInitials(moderator.name) || '?'
                                                }}</AvatarFallback>
                                            </Avatar>
                                            <div>
                                                <p class="font-medium">{{ moderator.name }}</p>
                                                <p class="text-xs text-muted-foreground">{{ moderator.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <Badge variant="secondary">{{ moderator.moderated_sessions_count }}</Badge>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('admin.moderators.edit', moderator.id)">
                                                <Button variant="outline" size="sm" class="gap-1.5"><Pencil class="size-3.5" /> Ubah</Button>
                                            </Link>
                                            <Button variant="destructive" size="sm" class="gap-1.5" @click="destroy(moderator)">
                                                <Trash2 class="size-3.5" /> Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredModerators.length === 0">
                                    <td colspan="3" class="py-10 text-center text-muted-foreground">
                                        {{ search ? 'Tidak ada moderator yang cocok dengan pencarian.' : 'Belum ada akun moderator.' }}
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
