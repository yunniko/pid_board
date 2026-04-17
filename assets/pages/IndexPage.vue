<script setup lang="ts">
import { inject, onMounted, ref } from 'vue';
import AppNav from '../components/AppNav.vue';
import { BoardApiKey } from '../services/boardApi';
import type { BoardNavEntry } from '../types/departure';

const api = inject(BoardApiKey);
if (!api) throw new Error('BoardApi not provided');

const boards = ref<BoardNavEntry[]>([]);

onMounted(async () => {
  boards.value = await api.getBoards();
});

const hrefFor = (slug: string): string => (slug === 'home' ? '/board' : `/board/${slug}`);
</script>

<template>
  <AppNav />
  <h1>PID boards</h1>
  <ul>
    <li v-for="b in boards" :key="b.slug">
      <a :href="hrefFor(b.slug)">{{ b.label }}</a>
    </li>
  </ul>
</template>
