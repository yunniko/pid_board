<script setup lang="ts">
import { useCurrentTime } from '../composables/useCurrentTime';

const { formatted } = useCurrentTime();
</script>

<template>
  <a class="current-time" href="/">{{ formatted }}</a>
</template>
