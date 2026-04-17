<script setup lang="ts">
import AppNav from '../components/AppNav.vue';
import CurrentTime from '../components/CurrentTime.vue';
import StopPanel from '../components/StopPanel.vue';
import { useBoardData } from '../composables/useBoardData';
import { useCurrentTime } from '../composables/useCurrentTime';

const props = defineProps<{ boardName: string }>();

const { data, error } = useBoardData(props.boardName);
const { nowSeconds } = useCurrentTime();
</script>

<template>
  <AppNav>
    <template #header>
      <CurrentTime />
    </template>
  </AppNav>
  <p v-if="error" class="error">{{ error }}</p>
  <div class="timetable">
    <StopPanel
      v-for="(timetable, idx) in data"
      :key="idx"
      :timetable="timetable"
      :now-seconds="nowSeconds"
    />
  </div>
</template>
