<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Reviewer {
    id: number;
    name: string;
    email: string;
    review_assignments_count: number;
}

defineProps<{
    reviewers: Reviewer[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Reviewer', href: '/admin/reviewers' }];

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('admin.reviewers.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

const editingId = ref<number | null>(null);
const editForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function startEdit(reviewer: Reviewer) {
    editingId.value = reviewer.id;
    editForm.reset();
    editForm.clearErrors();
    editForm.name = reviewer.name;
    editForm.email = reviewer.email;
}

function cancelEdit() {
    editingId.value = null;
    editForm.reset();
    editForm.clearErrors();
}

function submitEdit(reviewerId: number) {
    editForm.put(route('admin.reviewers.update', reviewerId), {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
            editForm.reset();
        },
    });
}

function destroy(reviewer: Reviewer) {
    if (!confirm(`Hapus akun reviewer "${reviewer.name}"?`)) {
        return;
    }
    router.delete(route('admin.reviewers.destroy', reviewer.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Reviewer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <Card>
                <CardHeader><CardTitle>Tambah Akun Reviewer</CardTitle></CardHeader>
                <CardContent>
                    <form class="grid gap-3" @submit.prevent="submit">
                        <div class="grid gap-1.5">
                            <Label for="reviewer_name">Nama</Label>
                            <Input id="reviewer_name" v-model="form.name" required />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="reviewer_email">Email</Label>
                            <Input id="reviewer_email" v-model="form.email" type="email" required />
                            <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="reviewer_password">Kata Sandi</Label>
                            <Input id="reviewer_password" v-model="form.password" type="password" required />
                            <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="reviewer_password_confirmation">Konfirmasi Kata Sandi</Label>
                            <Input id="reviewer_password_confirmation" v-model="form.password_confirmation" type="password" required />
                        </div>
                        <Button type="submit" :disabled="form.processing">Simpan</Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle>Daftar Reviewer</CardTitle></CardHeader>
                <CardContent>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="py-2">Nama</th>
                                <th>Email</th>
                                <th>Tugas Review</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="reviewer in reviewers" :key="reviewer.id">
                                <tr v-if="editingId !== reviewer.id" class="border-b last:border-0">
                                    <td class="py-2">{{ reviewer.name }}</td>
                                    <td>{{ reviewer.email }}</td>
                                    <td>
                                        <Badge variant="secondary">{{ reviewer.review_assignments_count }}</Badge>
                                    </td>
                                    <td class="space-x-2 text-right">
                                        <Button variant="outline" size="sm" @click="startEdit(reviewer)">Ubah</Button>
                                        <Button variant="destructive" size="sm" @click="destroy(reviewer)">Hapus</Button>
                                    </td>
                                </tr>
                                <tr v-else class="border-b bg-muted/40 last:border-0">
                                    <td colspan="4" class="py-3">
                                        <form class="grid gap-3" @submit.prevent="submitEdit(reviewer.id)">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="grid gap-1.5">
                                                    <Label :for="`edit_name_${reviewer.id}`">Nama</Label>
                                                    <Input :id="`edit_name_${reviewer.id}`" v-model="editForm.name" required />
                                                    <p v-if="editForm.errors.name" class="text-sm text-destructive">{{ editForm.errors.name }}</p>
                                                </div>
                                                <div class="grid gap-1.5">
                                                    <Label :for="`edit_email_${reviewer.id}`">Email</Label>
                                                    <Input :id="`edit_email_${reviewer.id}`" v-model="editForm.email" type="email" required />
                                                    <p v-if="editForm.errors.email" class="text-sm text-destructive">{{ editForm.errors.email }}</p>
                                                </div>
                                                <div class="grid gap-1.5">
                                                    <Label :for="`edit_password_${reviewer.id}`">Kata Sandi Baru (opsional)</Label>
                                                    <Input :id="`edit_password_${reviewer.id}`" v-model="editForm.password" type="password" />
                                                    <p v-if="editForm.errors.password" class="text-sm text-destructive">
                                                        {{ editForm.errors.password }}
                                                    </p>
                                                </div>
                                                <div class="grid gap-1.5">
                                                    <Label :for="`edit_password_confirmation_${reviewer.id}`">Konfirmasi Kata Sandi</Label>
                                                    <Input
                                                        :id="`edit_password_confirmation_${reviewer.id}`"
                                                        v-model="editForm.password_confirmation"
                                                        type="password"
                                                    />
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <Button type="submit" size="sm" :disabled="editForm.processing">Simpan Perubahan</Button>
                                                <Button type="button" variant="outline" size="sm" @click="cancelEdit">Batal</Button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="reviewers.length === 0">
                                <td colspan="4" class="py-6 text-center text-muted-foreground">Belum ada akun reviewer.</td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
