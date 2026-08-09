<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-white text-sky-700 shadow-sm">
        <AppLogoIcon class="size-5 fill-current text-sky-700" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate font-semibold leading-none text-sidebar-foreground">SNOS 2026</span>
    </div>
</template>
