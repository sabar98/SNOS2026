<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        icon?: LucideIcon;
        title: string;
        description?: string;
        iconClass?: string;
    }>(),
    {
        iconClass: 'bg-primary/10 text-primary',
    },
);
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span v-if="icon" :class="['flex size-11 shrink-0 items-center justify-center rounded-xl shadow-sm', iconClass]">
                <component :is="icon" class="size-5" />
            </span>
            <div>
                <h1 class="text-xl font-semibold tracking-tight">{{ title }}</h1>
                <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
            </div>
        </div>
        <div v-if="$slots.actions" class="flex items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
