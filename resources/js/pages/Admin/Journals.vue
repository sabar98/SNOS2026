<script setup lang="ts">
import BarChart from '@/components/charts/BarChart.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Journal {
    id: number;
    name: string;
    type: string;
    publisher: string | null;
    publication_fee: string;
    is_active: boolean;
}

const props = defineProps<{
    journals: Journal[];
    articlesByJournal: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Jurnal & Prosiding', href: '/admin/journals' }];

const journalChartData = computed(() => Object.entries(props.articlesByJournal).map(([label, value]) => ({ label, value })));

const selectClass = 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm';

const form = useForm({
    name: '',
    type: 'jurnal',
    publisher: '',
    issn: '',
    website_url: '',
    publication_fee: 0,
    description: '',
});

function submit() {
    form.post(route('admin.journals.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Jurnal & Prosiding" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <Card>
                <CardContent class="pt-6">
                    <BarChart title="Artikel per Jurnal Tujuan" :data="journalChartData" />
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle>Tambah Jurnal / Prosiding</CardTitle></CardHeader>
                <CardContent>
                    <form class="grid gap-3" @submit.prevent="submit">
                        <Label for="journal_name">Nama</Label>
                        <Input id="journal_name" v-model="form.name" required />
                        <Label for="journal_type">Tipe</Label>
                        <select id="journal_type" v-model="form.type" :class="selectClass">
                            <option value="jurnal">Jurnal</option>
                            <option value="prosiding">Prosiding</option>
                        </select>
                        <Label for="journal_publisher">Penerbit</Label>
                        <Input id="journal_publisher" v-model="form.publisher" />
                        <Label for="journal_fee">Biaya Publikasi (Rp)</Label>
                        <Input id="journal_fee" v-model.number="form.publication_fee" type="number" min="0" />
                        <Button type="submit" :disabled="form.processing">Simpan</Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle>Daftar Jurnal</CardTitle></CardHeader>
                <CardContent>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="py-2">Nama</th>
                                <th>Tipe</th>
                                <th>Biaya</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="journal in journals" :key="journal.id" class="border-b last:border-0">
                                <td class="py-2">{{ journal.name }}</td>
                                <td>{{ journal.type }}</td>
                                <td>Rp{{ Number(journal.publication_fee).toLocaleString('id-ID') }}</td>
                                <td>{{ journal.is_active ? 'Aktif' : 'Nonaktif' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
