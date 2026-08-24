<!-- src/App.vue (or any parent view) -->
<template>
  <div class="app-container" :class="theme">
    <!-- Header -->
    <header class="app-header">
      <h2 class="app-title">Frag die Platane</h2>
      <div class="left-controls">
        <div class="status">
          <span>Status: {{ statusText }}</span>
          <span v-if="loading" class="spinner"></span>
        </div>
      </div>

      <div class="right-controls">
        <button class="button tooltip" @click="openInfo">
          <font-awesome-icon :icon="['fas', 'question']"/>
            <span class="tooltiptext">Info</span>
        </button>
        <button v-if="!loggedIn" class="button loginBtn tooltip" @click="openLogin">
          <font-awesome-icon :icon="['fas', 'right-to-bracket']"/>
          <span class="btn-label">Login</span>
          <span class="tooltiptext">Anmelden</span>
        </button>
        <button v-else class="button loginBtn tooltip" @click="openLogin" title="Abmelden">
          <font-awesome-icon :icon="['fas', 'check']" class="tick"/>
          <span class="tooltiptext">Abmelden</span>
        </button>
        <button @click="openVideo" class="button tooltip">
          <font-awesome-icon :icon="['fas', 'video']" />
          <span class="tooltiptext">Video</span>
        </button>
        <button class="button tooltip" @click="toggleTemperature">
          <font-awesome-icon :icon="['fas', tempIcon]" />
          <span class="tooltiptext">Temperatur</span> 
        </button>

        <div class="tooltip">
          <select v-model="languageModel" class="button" @change="updateLlm" aria-label="Sprachmodell wählen">
            <option value="1">Mistral</option>
            <option value="2">Gpt-Oss</option>
            <option value="3">DeepSeek</option>
            <option value="4">Nemotron</option>
          </select>
          <span class="tooltiptext">Modell wählen</span>
        </div>
        <!-- 
        llmodel_1 = "mistralai/Mistral-Small-3.2-24B-Instruct-2506"
        llmodel_2 = "openai/gpt-oss-120b"
        llmodel_3 = "deepseek-ai/DeepSeek-V3.1"
        llmodel_4 = "Qwen/Qwen3-30B-A3B"

        -->
        <!-- 
        <button @click="submit" class="button tooltip">
          <span class="tooltiptext">KI befragen</span>
          Absenden
        </button>
        -->
        <button @click="download" class="button tooltip">
          <font-awesome-icon :icon="['fas', 'download']" />
          <span class="btn-label">Download</span>
          <span class="tooltiptext">Download</span>
        </button>
        <button class="button tooltip" @click="toggleLayout">
          <font-awesome-icon :icon="['fas', useTabLayout ? 'display' : 'mobile-screen']" />
          <span class="tooltiptext">{{ useTabLayout ? 'Desktop-Ansicht' : 'Mobile-Ansicht' }}</span>
        </button>
        <button class="button tooltip" @click="toggleTheme">
          <font-awesome-icon :icon="['fas', theme === 'light' ? 'moon' : 'sun']" />
          <span class="tooltiptext">{{ theme === 'light' ? 'Dunkel' : 'Hell' }}</span>
        </button>
      </div>
    </header>

    <!-- Desktop Grid Layout -->
    <div v-if="!useTabLayout" class="wrapper">
      <!-- CardList -->
      <div class="cardlist-container">
        <CardList ref="cardListRef" title="Prompt" />
      </div>

      <!-- EditFields -->
      <div class="editfields-container">
        <EditField class="editfield" title="Frage" v-model:fieldContent="query" :disabled="false" ref="queryFieldRef"
          button="Suche" @button-click="ctxSearch" :comments="queryComments" tooltip="Was wissen wir?" />
        <EditField class="editfield" title="Kontext" v-model:fieldContent="context" :disabled="false" button="Löschen"
          @button-click="ctxClear" />
        <EditField class="editfield" title="Antwort" v-model:fieldContent="response" :disabled="true" 
        button="Absenden" @button-click="submit" tooltip="KI befragen"/>
      </div>
    </div>

    <!-- Mobile Tab Layout -->
    <TabLayout v-else :hint="mobileHint" :tabs="mobileTabs" @update:activeTab="tabTo($event)">
      <template #prompt>
        <CardList ref="cardListRef" title="Prompt" />
      </template>
      <template #qa>
        <QAPane
          :query="query"
          @update:query="query = $event"
          :queryComments="queryComments"
          :response="response"
          :loading="loading"
          @search="ctxSearchAndSubmit"
        />
      </template>
      <template #context>
        <EditField title="Kontext" v-model:fieldContent="context" :disabled="false" button="Löschen"
          @button-click="ctxClear" />
      </template>
    </TabLayout>
  </div>

  <!-- Login popup -->
  <LoginPopup v-if="showLogin" @success="handleLoginSuccess" @close="showLogin = false" />
  <VideoPopup v-if="showVideo" :src="videoSrc" @close="showVideo = false" />
  <InfoPopup
    v-if="showInfo"
    :use-tab-layout="useTabLayout"
    :active-tab="activeTab"
    @close="showInfo = false"
  />

</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
//import { watch } from "vue";
import CardList from "./components/CardList.vue";
import EditField from "./components/EditField.vue";
import TabLayout from "./components/TabLayout.vue";
import QAPane from "./components/QAPane.vue";
import LoginPopup from "./components/LoginPop.vue";
import VideoPopup from "./components/VideoPop.vue";
import InfoPopup from "./components/InfoPop.vue";

import sanitizeHtml from 'sanitize-html';


const showLogin = ref(false)
const loggedIn = ref(false)

const showVideo = ref(false);
const videoSrc = ref("media/baumvideo.mp4");

const showInfo = ref(false);

const languageModel = ref("1");

/* ---- Layout detection ---- */
const mobileQuery = window.matchMedia('(max-width: 768px)');
const useTabLayout = ref<boolean>(mobileQuery.matches);
const layoutManualOverride = ref<boolean>(false);

function onMobileQueryChange(e: MediaQueryListEvent) {
  // Only auto-update when the user has not made an explicit choice
  if (!layoutManualOverride.value) {
    useTabLayout.value = e.matches;
  }
}

function toggleLayout() {
  layoutManualOverride.value = true;
  useTabLayout.value = !useTabLayout.value;
}


const cardListRef = ref<InstanceType<typeof CardList> | null>(null);

const query = ref("")
const queryComments = ref<string | null>(null)
const context = ref("Nichts ...");

const classifier = ref<string | null>(null);
const rating = ref<string | null>(null);

const fullContext = ref<Array<Record<string, string>>>([]);

const response = ref("");

const mobileHint = computed(() => {
  if (loading.value) return '';
  if (!query.value.trim()) return 'Frage eingeben und Suche drücken';
  if (!context.value) return 'Suche drücken für Kontext';
  if (response.value) return 'Antwort erhalten · Umformulieren?';
  return '';
});

const mobileTabs = [
  { id: 'prompt',  label: 'Prompt',  icon: 'list'      },
  { id: 'qa',      label: 'Q&A',     icon: 'lightbulb'  },
  { id: 'context', label: 'Kontext', icon: 'book-open' },
];

const activeTab = ref("prompt");
const tabTo = (tabId: string) => {
  // 'question' and 'answer' are kept for backward compat with the desktop layout's tabTo calls
  if (!['prompt', 'qa', 'context', 'question', 'answer'].includes(tabId)) {
    console.warn("Invalid tab id:", tabId);
    return;
  }
  if (activeTab.value === tabId) {
    return;
  }
  console.log("Active tab changed to:", tabId)
  activeTab.value = tabId;

  switch (tabId) {
    case 'prompt': {
      const promptField = cardListRef.value?.getCombinedText().trim();
      console.log("Prompt field content:", promptField);
      break;
    }
    case 'qa':
    case 'question': {
      const queryField = query.value.trim();
      console.log("Query field content:", queryField);
      break;
    }
    case 'context': {
      const contextField = context.value.trim();
      console.log("Context field content:", contextField);
      break;
    }
    case 'answer': {
      const responseField = response.value.trim();
      console.log("Response field content:", responseField);
      break;
    }
  }
};

const loading = ref(false);
const statusText = ref("Gelangweilt");
const theme = ref("light");

const tempIcon = ref("gauge");
const toggleTemperature = () => {
  switch (tempIcon.value) {
    case "gauge":
      tempIcon.value = "fire";
      break;
    case "snowflake":
      tempIcon.value = "gauge";
      break;
    case "fire":
      tempIcon.value = "snowflake";
      break;
  }
}

/*
// query is bound to modelValue of EditField
watch(query, (newVal, oldVal) => {
  console.log("query changed:", { newVal, oldVal });
  console.log("QueryField content:", query.value);
});
*/

const download = () => {
  const p = cardListRef.value?.getCombinedText().trim() ?? "";
  const q = query.value.trim();
  const ctx = context.value.trim();
  const cd = cardListRef.value?.getConditions()?.trim() ?? "";
  const r = response.value.trim();
  const content = `Frage:\n${q}\n\nPrompt:\n${p}\n\nKontext:\n${ctx}\n${cd}\n\nAntwort:\n${r}\n`;
  const blob = new Blob([content], { type: "text/plain;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  const now = new Date();
  const pad = (n:number) => String(n).padStart(2, '0');
  const filename = `chat_${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}.txt`;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
};

const openInfo = () => {
  showInfo.value = true;
};


const openLogin = () => {
  loggedIn.value = false
  localStorage.removeItem("auth-token")
  showLogin.value = true
}

const openVideo = () => {
  console.log("Open video popup");
  showVideo.value = true
}

const updateLlm = () => {
  console.log("Selected language model:", languageModel.value);
}


function handleLoginSuccess(token: string) {
  localStorage.setItem("auth-token", token)
  loggedIn.value = true
  showLogin.value = false
}


/**
 * Remove non‑printable Unicode characters and normalize whitespace.
 */
const normalizeText = (input: string): string => {
  // NFC normalizes composed characters (e.g., é = e + ´)
  const normalized = input.normalize('NFC');

  // Collapse multiple spaces/tabs into a single space
  return normalized.replace(/\s+/g, ' ').trim();
}



const llmCall = async (p: string, ctx: string, cnd: string, q: string, temperature: number, seed: number, model: number = 1, signed:boolean = true) => {
  // Placeholder for LLM call logic
  const token = localStorage.getItem("auth-token");
  if (!token) {
    alert("Bitte zuerst anmelden")
    return null;
  }
  console.log("LLM call initiated");
  const sanitizedPrompt = sanitizeHtml(p, {
    allowedTags: [],
    allowedAttributes: {}
  })
  console.log("Sanitized prompt:", sanitizedPrompt);
  const sanitizedContext = sanitizeHtml(ctx, {
    allowedTags: [],
    allowedAttributes: {}
  })
  console.log("Sanitized context:", sanitizedContext);
  const sanitizedQuery = sanitizeHtml(q, {
    allowedTags: [],
    allowedAttributes: {}
  })
  console.log("Sanitized query:", sanitizedQuery);
  try {
    const payload: { query: string; prompt: string; context?: string; temperature?: number; seed?: number; model?: number } = {
      query: normalizeText(sanitizedQuery),
      prompt: normalizeText(sanitizedPrompt),
      temperature: temperature,
      seed: seed,
      model: model
    };
    if (sanitizedContext) {
      payload.context = (cnd && cnd.length > 0) ? normalizeText(sanitizedContext) + "\n" + cnd : normalizeText(sanitizedContext);
    }
    statusText.value = "Sende Anfrage …";
    const res = await fetch("php/llamaChat.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(payload),
    });

    if (!res.ok) {
      const errText = await res.text();
      console.log("LLM call failed:", res.status, errText);
      if (res.status === 401) {
        localStorage.removeItem("auth-token");
        loggedIn.value = false;
        alert("Anmeldung abgelaufen. Bitte erneut anmelden.");
      } else {
        alert(`Fehler bei der Anfrage: ${res.status} ${errText}`);
      }
      loading.value = false;
      statusText.value = "Error";
      return null
    }
    

    // try parse JSON, fall back to text
    let body: any;
    try {
      body = await res.json();
    } catch {
      body = await res.text();
    }

    loading.value = false;
    statusText.value = "Fertig";

    if (!body || !body.text) {
      return null
    } else {
      console.log("Submit success:", body);
      // try to get model and provider from body, if present. also try to get impact info if present
      let author = ""
      if (body.model) {
        console.log("Model:", body.model);
        author = body.model
      }
      if (body.provider) {
        console.log("Provider:", body.provider);
        author += (author ? " / " : "") + body.provider
      }
      if (body.impact) {
        console.log("Impact:", body.impact);
        author += (author ? " / " : "") + ` ${body.impact}`
      }
      const signedText = signed ? body.text + "\n\n" + (author != "" ? " (generated by: " + author + ")" : "") : body.text;
      console.log("Signed text:", signedText);
      return String(signedText).trim();
    }
  } catch (err: any) {
    console.error("Submit error:", err);
    loading.value = false;
    statusText.value = "Error";
    return null
  }
};


const submit = async () => {
  response.value = ".. Warte auf Antwort .."
  console.log("Submitting data:");
  const q = query.value.trim();
  console.log("Query:", q);
  if (q.length === 0) {
    statusText.value = "Query fehlt";
    response.value = "Bitte fügen Sie vor dem Absenden einen Query-Text hinzu.";
    return;
  }
  const p = cardListRef.value?.getCombinedText().trim() ?? "";
  console.log("Prompt:", p);
  if (p.length === 0) {
    statusText.value = "Prompt fehlt";
    response.value = "Bitte fügen Sie vor dem Absenden einen Prompt-Text hinzu.";
    return;
  }
  let cd = cardListRef.value?.getConditions()?.trim() ?? null;
  console.log("Conditions:", cd);
  if (cd) {
    console.log("Using conditions:", cd);
    const weather = await getWeather();
    if (weather) {
      console.log("Appending weather to context:", weather);
      cd = weather + "\n" + cd;
    }
  }
  console.log("Context:", context.value);
  loading.value = true;
  statusText.value = "Loading...";
  let results: string[] = [];
  // for llm, build the call params
  let temp = .5
  switch (tempIcon.value) {
    case "snowflake":
      // low
      temp = 0.0
      break;
    case "fire":
      // high
      temp = 1.0
      break;
  }
  let llm = 1
  switch (languageModel.value) {
    case "1":
      // mistral
      llm = 1
      break;
    case "2":
      // gpt-oss
      llm = 2 
      break;
    case "3":
      // deepdeek
      llm = 3  
      break;
    case "4":
      // qwen3
      llm = 4
      break;
    default:
      // mistral
      llm = 1
      break;
  }
  const r = await llmCall(p, context.value ? context.value : "", cd ? cd : "", q, temp, 1234 * (10 * temp), llm);
  loading.value = false;
  if (typeof r === "string") {
    statusText.value = "Fertig";
    response.value = r.trim()
    console.log("LLM chat results:", results);
    // try to get rating for output string. 
    // make sure to remove trailing "author" signature if present, as the rating prompt is not expecting it.
    let rawResult = r.trim();
    const signatureIndex = r.lastIndexOf(" (generated by: ");
    if (signatureIndex !== -1) {
      console.log("Removing signature from output for rating:", r.substring(signatureIndex));
      rawResult = r.substring(0, signatureIndex).trim();
    }
    const ratingResult = await rateOutput(rawResult ?? r.trim());
    if (ratingResult) {
      console.log("Rating result:", ratingResult);
    } else {
      console.log("No rating result");
    }
  } else {
    response.value = ""
    statusText.value = "Kein Ergebnis";
    console.log("LLM chat returned non-string:", r);
  }

};

const ctxSearch = async () => {
  console.log("Search button clicked. Current query:", query.value);
  // Example action: prepend "Searching for: " to the query field content
  if (query.value !== "") {
    queryComments.value = "";
    loading.value = true;
    statusText.value = "Suche ...";
    let classes: string[] = [];
    // for llm, build the call params
    if (classifier?.value) {
      // always use model 1 for context search
      const r = await llmCall(classifier?.value, "", "", query.value, 0.0, 42, 1, false);
      if (typeof r === "string") {
        classes = r.split(",").map(s => s.trim()).filter(s => s.length > 0);
        console.log("LLM classification results:", classes);
      } else {
        console.log("LLM classification returned non-string:", r);
      }
    }
    if (classes.length === 0 || (classes.length === 1 && classes[0] === "unrelated")) {
      context.value = "";
      loading.value = false;
      statusText.value = "Nichts gefunden";
      queryComments.value = "Kein Kontext gefunden"
      response.value = "";
      return;
    }
    console.log("Result classes:", classes);
    queryComments.value = `Gefundene Themen: ${classes.join(", ")}`;

    // Keep only context items whose key matches one of the found classes
    const matchedContext = fullContext.value.flatMap(item => {
      // item expected like { "category1": "value" } (possibly multiple keys)
      console.log("Checking item:", item);
      return Object.entries(item)
        .filter(([k]) => {
          const norm = (s: unknown) =>
            String(s).replace(/^["']+|["']+$/g, "").trim().toLowerCase();
          return classes.some(c => norm(c) === norm(k));
        })
        .map(([, v]) => `${v}\n`);
    });
    //console.log("Matched context:", matchedContext);
    if (matchedContext.length > 0) {
      context.value = matchedContext.join("\n");
      statusText.value = "Fertig";
    } else {
      context.value = "";
      statusText.value = "Nichts gefunden";
    }
    loading.value = false;
    response.value = "";
  }
};

const rateOutput = async (statement: string) => {
  console.log("Rating requested. Current query:", statement);
  if (statement.length === 0) {
    console.warn("Empty statement for rating. Skipping.");
    return "undefined";
  }
  loading.value = true;
  let ratingEval: string = "undefined";
  // for llm, build the call params
  if (rating?.value) {
    // always use model 1 for context search
    const r = await llmCall(rating?.value, "", "", statement, 0.0, 42, 1,false);
    if (typeof r === "string") {
      ratingEval = r;
      console.log("LLM rating results:", ratingEval);
    } else {
      console.log("LLM rating returned non-string:", r);
    }
  }
  loading.value = false;
  return ratingEval;
};

/**
 * Chained search+submit for the mobile Q&A tab:
 * 1. Runs context search (classifier → context matching).
 * 2. If context was found, immediately calls submit() without user interaction.
 */
const ctxSearchAndSubmit = async () => {
  // if context is already present and more than "nichts", skip search and just submit
  if (context.value && !context.value.trim().toLowerCase().startsWith("nichts")) {
    await submit();
    return;
  }
  await ctxSearch();
  if (context.value && context.value.trim().length > 0) {
    await submit();
  }
};

const ctxClear = () => {
  console.log("Clear button clicked.");
  if (context.value) {
    context.value = "";
    response.value = "";
  }
};

const getWeather = async () => {
  try {
    const res = await fetch('https://api.open-meteo.com/v1/forecast?latitude=49.0069&longitude=8.4037&current=temperature_2m,rain');
    if (!res.ok) throw new Error(`Failed to load weather.php (${res.status})`);
    const data = await res.json();
    if (!data) {
      console.warn('No weather data');
    } else {
      const curr = data.current ?? data.current_weather;
      if (!curr) {
        console.warn('Weather payload missing current/current_weather');
      } else {
        // numeric values
        const temperature = Number(curr.temperature_2m ?? curr.temperature ?? NaN);
        const rainVal = Number(curr.rain ?? 0);

        // normalize time string: if it has no timezone/offset and the payload is GMT/utc, treat as UTC
        let timeIso = String(curr.time ?? '');
        if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/.test(timeIso)) {
          const tzStr = String(data.timezone ?? '').toUpperCase();
          if (tzStr.includes('GMT') || data.utc_offset_seconds === 0 || data.timezone_abbreviation === 'GMT') {
            timeIso += 'Z';
          }
        }
        const dateUtc = new Date(timeIso);

        // format for Karlsruhe (Europe/Berlin) with DST automatically handled by Intl
        const targetZone = 'Europe/Berlin';
        const fmt = new Intl.DateTimeFormat('de-DE', {
          timeZone: targetZone,
          year: 'numeric',
          month: '2-digit',
          day: '2-digit',
          hour: '2-digit',
          minute: '2-digit',
          hour12: false
        });
        const localTime = fmt.format(dateUtc);

        const weekday = new Intl.DateTimeFormat('de-DE', { timeZone: targetZone, weekday: 'long' }).format(dateUtc);
        const localTimeWithWeekday = `${weekday}, ${localTime}`;

        const weather = `Heute ist ${localTimeWithWeekday}, Temperatur beträgt ${temperature.toFixed(1)}°C, ${rainVal ? `${rainVal} ${data.current_units?.rain ?? 'mm'}` : 'Kein Regen'}`;
        return weather;
      }
    }

  } catch (err) {
    console.warn('Could not load weather.php:', err);
  }
  return undefined;
}

// QR-code demo login: reads `username` and `pwd` from the URL query string.
// Note: passing credentials in URLs is intentional for QR-code based demo access
// and is accepted as a trade-off for ease of use in controlled demo environments.
async function checkUrlParamsLogin() {
  const params = new URLSearchParams(window.location.search)
  const username = params.get('username')
  const pwd = params.get('pwd')
  if (username && pwd) {
    try {
      const res = await fetch('php/llamaLogin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: username.trim(), password: pwd }),
      })
      if (!res.ok) throw new Error(`Login failed (${res.status})`)
      const data = await res.json()
      if (data.token) {
        localStorage.setItem('auth-token', data.token)
        loggedIn.value = true
      }
    } catch (err: any) {
      console.warn('URL param login failed:', err)
    }
  }
}

onMounted(() => {
  mobileQuery.addEventListener('change', onMobileQueryChange);
  // remove orphan token
  localStorage.removeItem("auth-token")
  // Check URL params for QR-code based login (username + pwd)
  checkUrlParamsLogin()
  // Check saved preference
  const saved = localStorage.getItem("app-theme");
  if (saved === "light" || saved === "dark") {
    theme.value = saved;
  } else {
    // Use system preference if no saved theme
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
    theme.value = prefersDark ? "dark" : "light";
  }

  applyTheme();
  // load context.json
  (async () => {
    try {
      const res = await fetch('data/context.json', { cache: 'no-cache' });
      if (!res.ok) throw new Error(`Failed to load context.json (${res.status})`);
      const data = await res.json();
      if (Array.isArray(data) && data.length > 0 && typeof data[0] === 'object') {
        fullContext.value = data
        console.log('Loaded context from /data/context.json') //, data);
        const uniqueKeys = Array.from(
          new Set(fullContext.value.flatMap(obj => Object.keys(obj)))
        );
        console.log("Unique keys in /data/context.json:", uniqueKeys);
      } else {
        console.warn('Invalid format in /data/context.json');
      }
    } catch (err) {
      console.warn('Could not load /data/context.json:', err);
    }
  })();
  // load classifier promt
  (async () => {
    try {
      const res = await fetch('data/classifier_prompt.json', { cache: 'no-cache' });
      if (!res.ok) throw new Error(`Failed to load classifier_prompt.json (${res.status})`);
      const data = await res.json();
      if (typeof (data) === 'object') {
        classifier.value = data.prompt;
        console.log('Loaded classifier from /data/classifier_prompt.json') //, classifier.value);
      } else {
        console.warn('Invalid format in /data/classifier_prompt.json');
      }
    } catch (err) {
      console.warn('Could not load /data/classifier_prompt.json:', err);
    }
  })();
  // load rating promt
  (async () => {
    try {
      const res = await fetch('data/rating_prompt.json', { cache: 'no-cache' });
      if (!res.ok) throw new Error(`Failed to load rating_prompt.json (${res.status})`);
      const data = await res.json();
      if (typeof (data) === 'object') {
        rating.value = data.prompt;
        console.log('Loaded rating from /data/rating_prompt.json') //, rating.value);
      } else {
        console.warn('Invalid format in /data/rating_prompt.json');
      }
    } catch (err) {
      console.warn('Could not load /data/rating_prompt.json:', err);
    }
  })();

  // Test weather fetch
  getWeather().then(weather => {
    console.log(weather);
  });

});

onUnmounted(() => {
  mobileQuery.removeEventListener('change', onMobileQueryChange);
});

function toggleTheme() {
  theme.value = theme.value === "light" ? "dark" : "light";
  localStorage.setItem("app-theme", theme.value);
  applyTheme();
}

function applyTheme() {
  const appContainer = document.querySelector(".app-container");
  if (appContainer) {
    appContainer.classList.remove("light", "dark");
    appContainer.classList.add(theme.value);
  }
}
</script>

<style scoped></style>
