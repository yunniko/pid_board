<script setup lang="ts">
import { computed, watch } from 'vue';
import AppNav from '../components/AppNav.vue';
import CurrentTime from '../components/CurrentTime.vue';
import StopPanel from '../components/StopPanel.vue';
import { useBoardData } from '../composables/useBoardData';
import { useCurrentPath, navigateTo } from '../composables/useCurrentPath';
import { useCurrentTime } from '../composables/useCurrentTime';

const props = defineProps<{ boardName: string }>();

const path = useCurrentPath();

const boardPathPattern = /^\/board(?:\/([^?#]+))?/;

const currentBoard = computed<string>(() => {
  const match = path.value.match(boardPathPattern);
  if (!match) return props.boardName;
  return match[1] ? decodeURIComponent(match[1]) : 'home';
});

const { data, error, lastSuccessAt } = useBoardData(currentBoard);
const { nowSeconds } = useCurrentTime();

const pad = (n: number): string => (n < 10 ? `0${n}` : String(n));

const lastSuccessLabel = computed<string | null>(() => {
  if (lastSuccessAt.value === null) return null;
  const d = new Date(lastSuccessAt.value);
  return `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
});

const hasData = computed<boolean>(() => data.value.length > 0);

watch(path, (newPath) => {
  if (!boardPathPattern.test(newPath)) {
    window.location.href = newPath;
  }
});

function onNavClick(payload: { event: MouseEvent; href: string }): void {
  const { event, href } = payload;
  if (!href.startsWith('/board')) return;
  if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
  if (event.button !== 0) return;
  event.preventDefault();
  navigateTo(href);
}
</script>

<template>
  <AppNav @nav-click="onNavClick">
    <template #header>
      <CurrentTime />
    </template>
  </AppNav>
  <p v-if="error && hasData && lastSuccessLabel" class="error">
    Connection problem — showing data from {{ lastSuccessLabel }}
  </p>
  <p v-else-if="error" class="error">{{ error }}</p>
  <div class="timetable">
    <StopPanel
      v-for="(timetable, idx) in data"
      :key="idx"
      :timetable="timetable"
      :now-seconds="nowSeconds"
    />
  </div>
</template>
