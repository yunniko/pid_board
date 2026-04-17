<script setup lang="ts">
import { computed, inject, onMounted, ref } from 'vue';
import { BoardApiKey } from '../services/boardApi';
import type { BoardNavEntry } from '../types/departure';

const STORAGE_KEY = 'nav-toggle-hidden';

const api = inject(BoardApiKey);
if (!api) throw new Error('BoardApi not provided');

const boards = ref<BoardNavEntry[]>([]);
const hidden = ref<boolean>(window.sessionStorage.getItem(STORAGE_KEY) === 'true');

const currentPath = typeof window !== 'undefined'
  ? window.location.pathname + window.location.search
  : '';

const links = computed(() => [
  { href: '/', label: 'Index' },
  ...boards.value.map((b) => ({
    href: b.slug === 'home' ? '/board' : `/board/${b.slug}`,
    label: b.label,
  })),
  { href: '/stops', label: 'Raw stops' },
  { href: '/departures', label: 'Raw departures' },
]);

const isActive = (href: string): boolean => currentPath === href;

function toggle(): void {
  hidden.value = !hidden.value;
  window.sessionStorage.setItem(STORAGE_KEY, String(hidden.value));
}

onMounted(async () => {
  try {
    boards.value = await api.getBoards();
  } catch {
    boards.value = [];
  }
});
</script>

<template>
  <div class="nav-container" :class="{ hidden }">
    <div class="nav-toggle" @click="toggle"></div>
    <div class="nav-header">
      <slot name="header">
        <a href="/">PID boards</a>
      </slot>
    </div>
    <a
      v-for="link in links"
      :key="link.href"
      class="nav"
      :class="{ active: isActive(link.href) }"
      :href="link.href"
    >{{ link.label }}</a>
  </div>
</template>
