<!-- src/components/QAPane.vue -->
<template>
  <div class="qa-pane">
    <!-- Top 40%: Question input -->
    <div class="qa-top">
      <div class="cardHeader">
        <h3>Frage</h3>
        <button class="button tooltip" :disabled="loading" @click="$emit('search')">
          <span class="tooltiptext">Was wissen wir?</span>
          Suche
        </button>
      </div>
      <textarea
        class="editTextarea"
        v-model="queryModel"
        placeholder="Frag etwas…"
        :disabled="loading"
      ></textarea>
    </div>

    <!-- Divider bar showing retrieved categories -->
    <div class="qa-divider">
      <span v-if="queryComments">{{ queryComments }}</span>
      <span v-else class="qa-divider-placeholder">— Kontext-Kategorien erscheinen hier —</span>
    </div>

    <!-- Bottom 60%: Model response -->
    <div class="qa-bottom">
      <div class="cardHeader">
        <h3>Antwort</h3>
      </div>
      <textarea
        class="editTextarea"
        :value="response"
        disabled
        placeholder="Antwort erscheint hier…"
      ></textarea>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  query: string;
  queryComments?: string | null;
  response: string;
  loading?: boolean;
}>();

const emit = defineEmits<{
  (e: 'update:query', value: string): void;
  (e: 'search'): void;
}>();

const queryModel = computed({
  get: () => props.query,
  set: (val: string) => emit('update:query', val),
});
</script>

<style scoped>
.qa-pane {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
}

.qa-top {
  flex: 0 0 40%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.qa-divider {
  flex-shrink: 0;
  padding: 4px 8px;
  font-size: 0.8rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.qa-divider-placeholder {
  opacity: 0.85;
}

.qa-bottom {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.cardHeader {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 8px;
  flex-shrink: 0;
}

.cardHeader h3 {
  margin: 0;
  font-size: 0.95rem;
}

.editTextarea {
  flex: 1;
  resize: none;
  overflow-y: auto;
  width: 100%;
  box-sizing: border-box;
  padding: 8px;
  font-family: inherit;
  font-size: 0.9rem;
  border: none;
  outline: none;
  background: transparent;
}
</style>
