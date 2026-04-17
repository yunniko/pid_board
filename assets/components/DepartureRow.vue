<script setup lang="ts">
import { computed } from 'vue';
import { TransportType } from '../constants/transportType';
import type { Departure } from '../types/departure';

const props = defineProps<{
  departure: Departure;
  state: 'past' | 'current' | 'future';
}>();

const showPlatform = computed(() =>
  props.departure.transport_type === TransportType.TRAIN && !!props.departure.platform,
);

const showScheduled = computed(
  () => props.departure.departure_scheduled_short !== props.departure.departure_predicted_short,
);
</script>

<template>
  <div class="timetable-item" :class="state">
    <div>{{ departure.departure_predicted_diff }}</div>
    <div><b>{{ departure.departure_predicted_short }}</b></div>
    <div>
      <span v-if="showScheduled" class="scheduled">
        ({{ departure.departure_scheduled_short }})
      </span>
    </div>
    <div><b>{{ departure.route_number }}</b></div>
    <div>{{ departure.destination }}</div>
    <div v-if="showPlatform">[{{ departure.platform }}]</div>
    <div v-else></div>
  </div>
</template>
