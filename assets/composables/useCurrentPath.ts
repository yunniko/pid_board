import { ref, type Ref } from 'vue';

const path = ref<string>(
  typeof window !== 'undefined' ? window.location.pathname + window.location.search : '',
);

if (typeof window !== 'undefined') {
  window.addEventListener('popstate', () => {
    path.value = window.location.pathname + window.location.search;
  });
}

export function useCurrentPath(): Ref<string> {
  return path;
}

export function navigateTo(href: string): void {
  window.history.pushState({}, '', href);
  path.value = href;
}
