<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';

defineProps<{
    icon: LucideIcon;
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed p-10 text-center">
        <span class="flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground">
            <component :is="icon" class="size-6" />
        </span>
        <div>
            <p class="font-medium">{{ title }}</p>
            <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
        </div>
        <div v-if="$slots.action" class="mt-1">
            <slot name="action" />
        </div>
    </div>
</template>
