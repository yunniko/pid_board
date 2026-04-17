<script setup lang="ts">
import { inject, onMounted, ref } from 'vue';
import AppNav from '../components/AppNav.vue';
import { BoardApiKey } from '../services/boardApi';
import type { BoardNavEntry } from '../types/departure';

const api = inject(BoardApiKey);
if (!api) throw new Error('BoardApi not provided');

const boards = ref<BoardNavEntry[]>([]);
const loadError = ref<string | null>(null);

onMounted(async () => {
  try {
    boards.value = await api.getBoards();
  } catch (err) {
    loadError.value = err instanceof Error ? err.message : 'Unknown error';
  }
});

const hrefFor = (slug: string): string => (slug === 'home' ? '/board' : `/board/${slug}`);
</script>

<template>
  <AppNav />
  <h1>PID boards</h1>
  <p v-if="loadError" class="error">Failed to load boards: {{ loadError }}</p>
  <ul>
    <li v-for="b in boards" :key="b.slug">
      <a :href="hrefFor(b.slug)">{{ b.label }}</a>
    </li>
  </ul>
  <hr />
  <ul>
    <li><a href="/stops">Raw stops</a></li>
    <li><a href="/departures">Raw departures</a></li>
  </ul>
</template>
