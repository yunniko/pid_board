<script setup lang="ts">
import { computed } from 'vue';
import DepartureRow from './DepartureRow.vue';
import type { Departure, StopTimetable } from '../types/departure';

const props = defineProps<{
  timetable: StopTimetable;
  nowSeconds: number;
}>();

type Row = { departure: Departure; state: 'past' | 'current' | 'future' };

const rows = computed<Row[]>(() => {
  let firstFutureSeen = false;
  return props.timetable.departures.map((departure: Departure): Row => {
    if (departure.departure_predicted_ts < props.nowSeconds) {
      return { departure, state: 'past' };
    }
    if (!firstFutureSeen) {
      firstFutureSeen = true;
      return { departure, state: 'current' };
    }
    return { departure, state: 'future' };
  });
});
</script>

<template>
  <div class="timetable-panel">
    <div class="timetable-name">{{ timetable.stop }}</div>
    <div class="timetable-content">
      <DepartureRow
        v-for="(row, idx) in rows"
        :key="idx"
        :departure="row.departure"
        :state="row.state"
      />
    </div>
  </div>
</template>
