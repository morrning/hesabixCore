<template>
  <div class="editor-container">
    <div v-if="title" class="editor-title">{{ title }}</div>
    <div class="editor-toolbar" v-show="editor">
      <v-btn
        @click="editor?.chain().focus().toggleBold().run()"
        :class="{ 'is-active': isBold }"
        variant="text"
        density="compact"
      >
        <v-icon icon="mdi-format-bold"></v-icon>
        <v-tooltip activator="parent" location="bottom">پررنگ</v-tooltip>
      </v-btn>
      <v-btn
        @click="editor?.chain().focus().toggleItalic().run()"
        :class="{ 'is-active': isItalic }"
        variant="text"
        density="compact"
      >
        <v-icon icon="mdi-format-italic"></v-icon>
        <v-tooltip activator="parent" location="bottom">ایتالیک</v-tooltip>
      </v-btn>
      <v-btn
        @click="editor?.chain().focus().toggleBulletList().run()"
        :class="{ 'is-active': isBulletList }"
        variant="text"
        density="compact"
      >
        <v-icon icon="mdi-format-list-bulleted"></v-icon>
        <v-tooltip activator="parent" location="bottom">لیست نقطه‌ای</v-tooltip>
      </v-btn>
      <v-btn
        @click="editor?.chain().focus().toggleOrderedList().run()"
        :class="{ 'is-active': isOrderedList }"
        variant="text"
        density="compact"
      >
        <v-icon icon="mdi-format-list-numbered"></v-icon>
        <v-tooltip activator="parent" location="bottom">لیست شماره‌دار</v-tooltip>
      </v-btn>
      <v-btn
        @click="editor?.chain().focus().setTextAlign('right').run()"
        :class="{ 'is-active': isRightAlign }"
        variant="text"
        density="compact"
      >
        <v-icon icon="mdi-format-align-right"></v-icon>
        <v-tooltip activator="parent" location="bottom">راست‌چین</v-tooltip>
      </v-btn>
      <v-btn
        @click="editor?.chain().focus().setTextAlign('center').run()"
        :class="{ 'is-active': isCenterAlign }"
        variant="text"
        density="compact"
      >
        <v-icon icon="mdi-format-align-center"></v-icon>
        <v-tooltip activator="parent" location="bottom">وسط‌چین</v-tooltip>
      </v-btn>
      <v-btn
        @click="editor?.chain().focus().setTextAlign('left').run()"
        :class="{ 'is-active': isLeftAlign }"
        variant="text"
        density="compact"
      >
        <v-icon icon="mdi-format-align-left"></v-icon>
        <v-tooltip activator="parent" location="bottom">چپ‌چین</v-tooltip>
      </v-btn>
    </div>
    <editor-content v-show="editor" :editor="editor" class="editor-content" />
  </div>
</template>

<script>
import { EditorContent, Editor } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import TextAlign from '@tiptap/extension-text-align'

export default {
  name: 'CustomEditor',
  components: {
    EditorContent
  },
  props: {
    modelValue: {
      type: String,
      default: ''
    },
    title: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      editor: null,
      isBold: false,
      isItalic: false,
      isBulletList: false,
      isOrderedList: false,
      isRightAlign: false,
      isCenterAlign: false,
      isLeftAlign: false
    }
  },
  methods: {
    decodeHTML(html) {
      if (!html) return '';
      const txt = document.createElement("textarea");
      txt.innerHTML = html;
      return txt.value;
    },
    updateToolbarState() {
      if (!this.editor) return;
      this.isBold = this.editor.isActive('bold');
      this.isItalic = this.editor.isActive('italic');
      this.isBulletList = this.editor.isActive('bulletList');
      this.isOrderedList = this.editor.isActive('orderedList');
      this.isRightAlign = this.editor.isActive({ textAlign: 'right' });
      this.isCenterAlign = this.editor.isActive({ textAlign: 'center' });
      this.isLeftAlign = this.editor.isActive({ textAlign: 'left' });
    },
    initEditor() {
      const decodedContent = this.decodeHTML(this.modelValue);
      this.editor = new Editor({
        content: decodedContent,
        extensions: [
          StarterKit,
          TextAlign.configure({
            types: ['heading', 'paragraph'],
            alignments: ['left', 'center', 'right'],
            defaultAlignment: 'right'
          })
        ],
        onUpdate: ({ editor }) => {
          this.$emit('update:modelValue', editor.getHTML());
          this.updateToolbarState();
        },
        onSelectionUpdate: () => {
          this.updateToolbarState();
        }
      });
      this.updateToolbarState();
    }
  },
  watch: {
    modelValue: {
      immediate: true,
      handler(newValue) {
        if (this.editor && newValue !== this.editor.getHTML()) {
          const decodedContent = this.decodeHTML(newValue);
          this.editor.commands.setContent(decodedContent);
          this.updateToolbarState();
        }
      }
    }
  },
  mounted() {
    this.initEditor();
  },
  beforeUnmount() {
    this.editor?.destroy()
  }
}
</script>

<style scoped>
.editor-container {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 4px;
  padding: 8px;
  background: rgb(var(--v-theme-surface));
}

.editor-title {
  font-size: 1.1rem;
  font-weight: 500;
  margin-bottom: 8px;
  color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
}

.editor-toolbar {
  display: flex;
  gap: 4px;
  padding: 8px;
  border-bottom: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  margin-bottom: 8px;
  background: rgb(var(--v-theme-surface));
}

.editor-toolbar .v-btn {
  min-width: 36px;
  height: 36px;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
}

.editor-toolbar .v-btn.is-active {
  background-color: rgba(var(--v-theme-primary), 0.1);
  color: rgb(var(--v-theme-primary));
}

.editor-content {
  min-height: 200px;
  padding: 8px;
  direction: rtl;
  text-align: right;
  background: rgb(var(--v-theme-surface));
  color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
}

.editor-content:focus {
  outline: none;
}

.editor-content :deep(.ProseMirror) {
  outline: none !important;
  min-height: 200px;
}

.editor-content :deep(.ProseMirror:focus) {
  outline: none !important;
}

.editor-content :deep(.ProseMirror p) {
  margin-bottom: 8px;
}

.editor-content :deep(.ProseMirror ul),
.editor-content :deep(.ProseMirror ol) {
  padding-right: 24px;
  margin-bottom: 8px;
}
</style> 