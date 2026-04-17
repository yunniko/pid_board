<script setup lang="ts">
import { inject } from 'vue';
import AppNav from '../components/AppNav.vue';
import RawSearchForm from '../components/RawSearchForm.vue';
import { BoardApiKey } from '../services/boardApi';

const api = inject(BoardApiKey);
if (!api) throw new Error('BoardApi not provided');
</script>

<template>
  <AppNav />
  <h1>Raw departures</h1>
  <RawSearchForm :search="api.getDepartures" />
</template>
