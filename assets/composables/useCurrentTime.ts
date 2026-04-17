import { onBeforeUnmount, ref, type Ref } from 'vue';

const pad = (n: number): string => (n < 10 ? `0${n}` : String(n));

export interface CurrentTime {
  now: Ref<Date>;
  formatted: Ref<string>;
  nowSeconds: Ref<number>;
}

export function useCurrentTime(intervalMs = 1000): CurrentTime {
  const now = ref<Date>(new Date());
  const formatted = ref<string>(format(now.value));
  const nowSeconds = ref<number>(Math.floor(now.value.getTime() / 1000));

  const id = window.setInterval(() => {
    const current = new Date();
    now.value = current;
    formatted.value = format(current);
    nowSeconds.value = Math.floor(current.getTime() / 1000);
  }, intervalMs);

  onBeforeUnmount(() => window.clearInterval(id));

  return { now, formatted, nowSeconds };
}

function format(date: Date): string {
  return `${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
}
