<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Leader {
    id: number;
    name: string;
    email: string;
}

defineProps<{
    pimpinan: Leader[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Pimpinan', href: '/admin/pimpinan' }];

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('admin.pimpinan.store'), {
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

function startEdit(leader: Leader) {
    editingId.value = leader.id;
    editForm.reset();
    editForm.clearErrors();
    editForm.name = leader.name;
    editForm.email = leader.email;
}

function cancelEdit() {
    editingId.value = null;
    editForm.reset();
    editForm.clearErrors();
}

function submitEdit(leaderId: number) {
    editForm.put(route('admin.pimpinan.update', leaderId), {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
            editForm.reset();
        },
    });
}

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
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <Card>
                <CardHeader><CardTitle>Tambah Akun Pimpinan</CardTitle></CardHeader>
                <CardContent>
                    <form class="grid gap-3" @submit.prevent="submit">
                        <div class="grid gap-1.5">
                            <Label for="pimpinan_name">Nama</Label>
                            <Input id="pimpinan_name" v-model="form.name" required />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="pimpinan_email">Email</Label>
                            <Input id="pimpinan_email" v-model="form.email" type="email" required />
                            <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="pimpinan_password">Kata Sandi</Label>
                            <Input id="pimpinan_password" v-model="form.password" type="password" required />
                            <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="pimpinan_password_confirmation">Konfirmasi Kata Sandi</Label>
                            <Input id="pimpinan_password_confirmation" v-model="form.password_confirmation" type="password" required />
                        </div>
                        <Button type="submit" :disabled="form.processing">Simpan</Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle>Daftar Pimpinan</CardTitle></CardHeader>
                <CardContent>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="py-2">Nama</th>
                                <th>Email</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="leader in pimpinan" :key="leader.id">
                                <tr v-if="editingId !== leader.id" class="border-b last:border-0">
                                    <td class="py-2">{{ leader.name }}</td>
                                    <td>{{ leader.email }}</td>
                                    <td class="space-x-2 text-right">
                                        <Button variant="outline" size="sm" @click="startEdit(leader)">Ubah</Button>
                                        <Button variant="destructive" size="sm" @click="destroy(leader)">Hapus</Button>
                                    </td>
                                </tr>
                                <tr v-else class="border-b bg-muted/40 last:border-0">
                                    <td colspan="3" class="py-3">
                                        <form class="grid gap-3" @submit.prevent="submitEdit(leader.id)">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="grid gap-1.5">
                                                    <Label :for="`edit_name_${leader.id}`">Nama</Label>
                                                    <Input :id="`edit_name_${leader.id}`" v-model="editForm.name" required />
                                                    <p v-if="editForm.errors.name" class="text-sm text-destructive">{{ editForm.errors.name }}</p>
                                                </div>
                                                <div class="grid gap-1.5">
                                                    <Label :for="`edit_email_${leader.id}`">Email</Label>
                                                    <Input :id="`edit_email_${leader.id}`" v-model="editForm.email" type="email" required />
                                                    <p v-if="editForm.errors.email" class="text-sm text-destructive">{{ editForm.errors.email }}</p>
                                                </div>
                                                <div class="grid gap-1.5">
                                                    <Label :for="`edit_password_${leader.id}`">Kata Sandi Baru (opsional)</Label>
                                                    <Input :id="`edit_password_${leader.id}`" v-model="editForm.password" type="password" />
                                                    <p v-if="editForm.errors.password" class="text-sm text-destructive">
                                                        {{ editForm.errors.password }}
                                                    </p>
                                                </div>
                                                <div class="grid gap-1.5">
                                                    <Label :for="`edit_password_confirmation_${leader.id}`">Konfirmasi Kata Sandi</Label>
                                                    <Input
                                                        :id="`edit_password_confirmation_${leader.id}`"
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
                            <tr v-if="pimpinan.length === 0">
                                <td colspan="3" class="py-6 text-center text-muted-foreground">Belum ada akun pimpinan.</td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
