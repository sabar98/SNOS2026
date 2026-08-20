<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Building2, Eye, EyeOff, IdCard, LoaderCircle, Lock, Mail, Phone, User, UserPlus } from 'lucide-vue-next';
import { ref } from 'vue';

const form = useForm({
    name: '',
    nik: '',
    institution: '',
    whatsapp_number: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Buat Akun Baru" description="Lengkapi data di bawah ini untuk mendaftar sebagai peserta">
        <Head title="Daftar" />

        <div class="rounded-xl border bg-background p-6 shadow-sm sm:p-8">
            <form @submit.prevent="submit" class="flex flex-col gap-5">
                <div class="grid gap-2">
                    <Label for="name">Nama Lengkap</Label>
                    <div class="relative">
                        <User class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="name"
                            type="text"
                            required
                            autofocus
                            tabindex="1"
                            autocomplete="name"
                            v-model="form.name"
                            placeholder="Nama sesuai identitas"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="nik">NIDN/NUPK/NPM</Label>
                        <div class="relative">
                            <IdCard class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="nik"
                                type="text"
                                required
                                tabindex="2"
                                autocomplete="off"
                                v-model="form.nik"
                                placeholder="NIDN/NUPK/NPM"
                                class="pl-10"
                            />
                        </div>
                        <InputError :message="form.errors.nik" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="whatsapp_number">Nomor WhatsApp</Label>
                        <div class="relative">
                            <Phone class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="whatsapp_number"
                                type="text"
                                required
                                tabindex="3"
                                autocomplete="tel"
                                v-model="form.whatsapp_number"
                                placeholder="08xxxxxxxxxx"
                                class="pl-10"
                            />
                        </div>
                        <InputError :message="form.errors.whatsapp_number" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="institution">Perguruan Tinggi / Instansi</Label>
                    <div class="relative">
                        <Building2 class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="institution"
                            type="text"
                            required
                            tabindex="4"
                            v-model="form.institution"
                            placeholder="Universitas Contoh"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="form.errors.institution" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Alamat Email</Label>
                    <div class="relative">
                        <Mail class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="email"
                            type="email"
                            required
                            tabindex="5"
                            autocomplete="email"
                            v-model="form.email"
                            placeholder="nama@email.com"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="password">Kata Sandi</Label>
                        <div class="relative">
                            <Lock class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                tabindex="6"
                                autocomplete="new-password"
                                v-model="form.password"
                                placeholder="Buat sandi"
                                class="px-10"
                            />
                            <button
                                type="button"
                                tabindex="-1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
                                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff v-if="showPassword" class="size-4" />
                                <Eye v-else class="size-4" />
                            </button>
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Konfirmasi Sandi</Label>
                        <div class="relative">
                            <Lock class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="password_confirmation"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                required
                                tabindex="7"
                                autocomplete="new-password"
                                v-model="form.password_confirmation"
                                placeholder="Ulangi sandi"
                                class="px-10"
                            />
                            <button
                                type="button"
                                tabindex="-1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
                                :aria-label="showPasswordConfirmation ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                @click="showPasswordConfirmation = !showPasswordConfirmation"
                            >
                                <EyeOff v-if="showPasswordConfirmation" class="size-4" />
                                <Eye v-else class="size-4" />
                            </button>
                        </div>
                        <InputError :message="form.errors.password_confirmation" />
                    </div>
                </div>

                <Button type="submit" size="lg" class="mt-1 w-full gap-2" tabindex="8" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                    <UserPlus v-else class="size-4" />
                    Buat Akun
                </Button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-muted-foreground">
            Sudah punya akun?
            <TextLink :href="route('login')" :tabindex="9">Masuk di sini</TextLink>
        </p>
    </AuthBase>
</template>
