<template>
  <div class="infoPop" @click="close">
    <div class="modal" @click.stop>
      <button class="close" @click="close" aria-label="Close">✕</button>
      <div class="infoContent" v-html="html"></div>
    </div>
  </div>  
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { marked } from 'marked'

const html = ref<string>('')
const props = defineProps<{
  useTabLayout: boolean
  activeTab: string
}>()

const desktopInfoSrc = '/data/info_desktop.md'
const mobileFallbackSrc = '/data/info_mobile.md'

const mobileTabInfoMap: Record<string, string> = {
  prompt: '/data/info_mobile_prompt.md',
  qa: '/data/info_mobile_qa.md',
  context: '/data/info_mobile_context.md',
}

function resolveInfoSrc(): string {
  if (!props.useTabLayout) {
    return desktopInfoSrc
  }
  return mobileTabInfoMap[props.activeTab] ?? mobileFallbackSrc
}

onMounted(async () => {
  const src = resolveInfoSrc()
  const res = await fetch(src)
  const markdown = await res.text()
  html.value = await marked(markdown)
})

const emit = defineEmits<{
  (e: 'close'): void
}>()  


function close() {
  emit('close')
}


</script>

<style scoped>
.infoPop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  display: block;
}

.modal {
  position: relative;
  width: 100%;
  height: 100%;
  max-height: 90vh;
  display: block;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  box-sizing: border-box;
  overflow: hidden;
}

.infoContent {
  width: 100%;
  height: 95%;
  object-fit: contain;
  background: black;
  color:white;
  overflow: scroll;
  border-radius: 6px;
  display:block;
  padding: 1rem;
  box-sizing: border-box;
}

/* Close button */
.close {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  background: rgba(0,0,0,0.5);
  color: white;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  font-size: 18px;
  line-height: 1;
  display: grid;
  place-items: center;
  cursor: pointer;
  z-index: 1;
}
.close:hover {
  background: rgba(0,0,0,0.75);
}
</style>
