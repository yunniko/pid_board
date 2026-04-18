<script setup lang="ts">
import { computed, inject, onMounted, ref } from 'vue';
import { BoardApiKey } from '../services/boardApi';
import { useCurrentPath } from '../composables/useCurrentPath';
import type { BoardNavEntry } from '../types/departure';

const STORAGE_KEY = 'nav-toggle-hidden';

const api = inject(BoardApiKey);
if (!api) throw new Error('BoardApi not provided');

const emit = defineEmits<{
  (e: 'nav-click', payload: { event: MouseEvent; href: string }): void;
}>();

const boards = ref<BoardNavEntry[]>([]);
const hidden = ref<boolean>(window.sessionStorage.getItem(STORAGE_KEY) === 'true');
const menuOpen = ref<boolean>(false);
const currentPath = useCurrentPath();

const links = computed(() =>
  boards.value.map((b, idx) => ({
    href: b.slug === 'home' ? '/board' : `/board/${b.slug}`,
    label: b.label,
    order: idx,
  })),
);

const isActive = (href: string): boolean => currentPath.value === href;

const activeLink = computed(() => links.value.find((l) => isActive(l.href)) ?? null);
const inactiveLinks = computed(() => links.value.filter((l) => !isActive(l.href)));

function toggle(): void {
  hidden.value = !hidden.value;
  window.sessionStorage.setItem(STORAGE_KEY, String(hidden.value));
}

function toggleMenu(): void {
  menuOpen.value = !menuOpen.value;
}

function onNavClick(event: MouseEvent, href: string): void {
  emit('nav-click', { event, href });
  menuOpen.value = false;
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
  <div class="nav-container" :class="{ hidden, 'menu-open': menuOpen }">
    <div class="nav-toggle" @click="toggle"></div>
    <div class="nav-header">
      <slot name="header">
        <a href="/">PID boards</a>
      </slot>
    </div>
    <a
      v-if="activeLink"
      class="nav active"
      :style="{ order: activeLink!.order }"
      :href="activeLink!.href"
      @click="onNavClick($event, activeLink!.href)"
    >{{ activeLink!.label }}</a>
    <div
      class="nav-menu-button"
      :class="{ open: menuOpen }"
      :aria-expanded="menuOpen"
      aria-label="Toggle navigation menu"
      role="button"
      @click="toggleMenu"
    ></div>
    <div class="nav-links">
      <a
        v-for="link in inactiveLinks"
        :key="link.href"
        class="nav"
        :style="{ order: link.order }"
        :href="link.href"
        @click="onNavClick($event, link.href)"
      >{{ link.label }}</a>
    </div>
  </div>
</template>
