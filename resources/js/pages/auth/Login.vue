<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, LoaderCircle, Lock, LogIn, Mail } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Masuk ke Akun" description="Masukkan email dan kata sandi Anda untuk melanjutkan">
        <Head title="Masuk" />

        <div
            v-if="status"
            class="mb-4 rounded-md bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400"
        >
            {{ status }}
        </div>

        <div class="rounded-xl border bg-background p-6 shadow-sm sm:p-8">
            <form @submit.prevent="submit" class="flex flex-col gap-5">
                <div class="grid gap-2">
                    <Label for="email">Alamat Email</Label>
                    <div class="relative">
                        <Mail class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="email"
                            type="email"
                            required
                            autofocus
                            tabindex="1"
                            autocomplete="email"
                            v-model="form.email"
                            placeholder="nama@email.com"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Kata Sandi</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" tabindex="5"> Lupa kata sandi? </TextLink>
                    </div>
                    <div class="relative">
                        <Lock class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            tabindex="2"
                            autocomplete="current-password"
                            v-model="form.password"
                            placeholder="Masukkan kata sandi"
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

                <Label for="remember" class="flex w-fit items-center gap-2.5 text-sm font-normal text-muted-foreground" tabindex="3">
                    <Checkbox id="remember" v-model:checked="form.remember" tabindex="4" />
                    <span>Ingat saya di perangkat ini</span>
                </Label>

                <Button type="submit" size="lg" class="mt-1 w-full gap-2" tabindex="4" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                    <LogIn v-else class="size-4" />
                    Masuk
                </Button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-muted-foreground">
            Belum punya akun?
            <TextLink :href="route('register')" :tabindex="5">Daftar sekarang</TextLink>
        </p>
    </AuthBase>
</template>
