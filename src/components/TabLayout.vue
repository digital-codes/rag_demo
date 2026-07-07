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
      <div v-show="activeTab === 'context'" class="tab-panel">
        <slot name="context" />
      </div>
      <div v-show="activeTab === 'answer'" class="tab-panel">
        <slot name="answer" />
      </div>
    </div>

    <nav class="tab-bar">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        :class="['tab-btn', { active: activeTab === tab.id }]"
        @click="activeTab = tab.id"
        :aria-label="tab.label"
      >
        <font-awesome-icon :icon="['fas', tab.icon]" />
        <span class="tab-label">{{ tab.label }}</span>
      </button>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const activeTab = ref<string>('prompt');

const tabs = [
  { id: 'prompt',   label: 'Prompt',  icon: 'list'             },
  { id: 'question', label: 'Frage',   icon: 'magnifying-glass' },
  { id: 'context',  label: 'Kontext', icon: 'book-open'        },
  { id: 'answer',   label: 'Antwort', icon: 'comment-dots'     },
];
</script>
