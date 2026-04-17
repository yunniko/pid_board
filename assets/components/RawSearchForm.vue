<script setup lang="ts">
import { ref } from 'vue';
import type { RawRecord } from '../types/departure';

const props = defineProps<{
  search: (names: string) => Promise<RawRecord[]>;
  initial?: string;
}>();

const names = ref<string>(props.initial ?? '');
const result = ref<RawRecord[] | null>(null);
const error = ref<string | null>(null);
const loading = ref<boolean>(false);

async function submit(): Promise<void> {
  if (!names.value.trim()) return;
  loading.value = true;
  error.value = null;
  try {
    result.value = await props.search(names.value);
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unknown error';
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <form @submit.prevent="submit">
    <input v-model="names" name="names" placeholder="Stop names, comma-separated" />
    <button type="submit" :disabled="loading">Search</button>
  </form>
  <p v-if="error" class="error">{{ error }}</p>
  <pre v-if="result">{{ JSON.stringify(result, null, 2) }}</pre>
</template>
