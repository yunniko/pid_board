<script setup lang="ts">
import { computed } from 'vue';
import AppNav from '../components/AppNav.vue';
import CurrentTime from '../components/CurrentTime.vue';
import StopPanel from '../components/StopPanel.vue';
import { useBoardData } from '../composables/useBoardData';
import { useCurrentTime } from '../composables/useCurrentTime';

const props = defineProps<{ boardName: string }>();

const { data, error, lastSuccessAt } = useBoardData(props.boardName);
const { nowSeconds } = useCurrentTime();

const pad = (n: number): string => (n < 10 ? `0${n}` : String(n));

const lastSuccessLabel = computed<string | null>(() => {
  if (lastSuccessAt.value === null) return null;
  const d = new Date(lastSuccessAt.value);
  return `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
});

const hasData = computed<boolean>(() => data.value.length > 0);
</script>

<template>
  <AppNav>
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
