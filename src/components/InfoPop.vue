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
const mobileInfoSources = {
  fallback: '/data/info_mobile.md',
  tabs: {
    prompt: '/data/info_mobile_prompt.md',
    qa: '/data/info_mobile_qa.md',
    context: '/data/info_mobile_context.md',
  } as Record<string, string>,
}

function resolveInfoSrc(): string {
  if (!props.useTabLayout) {
    return desktopInfoSrc
  }
  const src = mobileInfoSources.tabs[props.activeTab]
  if (!src) {
    console.warn(`Unknown mobile tab for info popup: ${props.activeTab}. Expected one of: ${Object.keys(mobileInfoSources.tabs).join(', ')}.`)
    return mobileInfoSources.fallback
  }
  return src
}

onMounted(async () => {
  try {
    const src = resolveInfoSrc()
    const res = await fetch(src)
    if (!res.ok) {
      throw new Error(`Failed to load info file: ${src} (${res.status})`)
    }
    const markdown = await res.text()
    html.value = await marked(markdown)
  } catch (error) {
    console.error(error)
    html.value = '<p>Die Hilfe konnte gerade nicht geladen werden.</p>'
  }
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
