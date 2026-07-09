<!-- src/components/TabLayout.vue -->
<template>
  <div class="tab-layout">
    <div class="tab-content">
      <div v-show="activeTab === 'prompt'" class="tab-panel">
        <slot name="prompt" />
      </div>
      <div v-show="activeTab === 'question'" class="tab-panel">
        <slot name="question" />
      </div>
      <div v-show="activeTab === 'qa'" class="tab-panel">
        <slot name="qa" />
      </div>
      <div v-show="activeTab === 'context'" class="tab-panel">
        <slot name="context" />
      </div>
      <div v-show="activeTab === 'answer'" class="tab-panel">
        <slot name="answer" />
      </div>
    </div>

    <div v-if="hint" class="tab-hint" aria-live="polite">
      <span class="tab-hint-text">{{ hint }}</span>
    </div>

    <nav class="tab-bar">
      <button
        v-for="tab in resolvedTabs"
        :key="tab.id"
        :class="['tab-btn', { active: activeTab === tab.id }]"
        @click="activeTab = tab.id; emit('update:activeTab', tab.id)"
        :aria-label="tab.label"
      >
        <font-awesome-icon :icon="['fas', tab.icon]" />
        <span class="tab-label">{{ tab.label }}</span>
      </button>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

interface TabDef {
  id: string;
  label: string;
  icon: string;
}

const props = defineProps<{
  hint?: string;
  tabs?: TabDef[];
}>();

const emit = defineEmits<{
  (e: 'update:activeTab', value: string): void
}>();

const activeTab = ref<string>('prompt');

const defaultTabs: TabDef[] = [
  { id: 'prompt',   label: 'Prompt',  icon: 'list'             },
  { id: 'question', label: 'Frage',   icon: 'magnifying-glass' },
  { id: 'context',  label: 'Kontext', icon: 'book-open'        },
  { id: 'answer',   label: 'Antwort', icon: 'comment-dots'     },
];

const resolvedTabs = computed<TabDef[]>(() => props.tabs ?? defaultTabs);
</script>
