<script setup lang="ts">
import { Ckeditor } from '@ckeditor/ckeditor5-vue';
import { Bold, ClassicEditor, Essentials, Italic, Link, List, Paragraph, Undo } from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';

defineProps<{
    placeholder?: string;
}>();

const modelValue = defineModel<string>({ default: '' });

const editorConfig = {
    licenseKey: 'GPL',
    plugins: [Essentials, Paragraph, Bold, Italic, Link, List, Undo],
    toolbar: ['undo', 'redo', '|', 'bold', 'italic', 'link', '|', 'bulletedList', 'numberedList'],
};
</script>

<template>
    <div class="rich-text-editor rounded-md border border-input bg-background text-sm">
        <Ckeditor :editor="ClassicEditor" v-model="modelValue" :config="{ ...editorConfig, placeholder }" />
    </div>
</template>

<style>
.rich-text-editor .ck-editor__editable {
    min-height: 10rem;
}

.rich-text-editor .ck.ck-editor__top .ck-sticky-panel__content,
.rich-text-editor .ck.ck-toolbar {
    border: none;
    border-bottom: 1px solid hsl(var(--border));
    background: hsl(var(--muted));
}

.rich-text-editor .ck.ck-editor__editable_inline {
    border: none;
}

.rich-text-editor .ck.ck-editor__editable_inline:focus {
    box-shadow: none;
}
</style>
