import { onBeforeUnmount, onMounted } from 'vue';

export interface AutoRefreshOptions {
  intervalMs: number;
  pauseWhenHidden?: boolean;
}

export function useAutoRefresh(
  refreshFn: () => void | Promise<void>,
  options: AutoRefreshOptions,
): void {
  const { intervalMs, pauseWhenHidden = true } = options;
  let timerId: number | null = null;

  const tick = (): void => {
    if (pauseWhenHidden && document.visibilityState === 'hidden') {
      return;
    }
    void refreshFn();
  };

  const start = (): void => {
    if (timerId !== null) return;
    timerId = window.setInterval(tick, intervalMs);
  };

  const stop = (): void => {
    if (timerId === null) return;
    window.clearInterval(timerId);
    timerId = null;
  };

  const onVisibilityChange = (): void => {
    if (!pauseWhenHidden) return;
    if (document.visibilityState === 'visible') {
      void refreshFn();
      start();
    } else {
      stop();
    }
  };

  onMounted(() => {
    start();
    document.addEventListener('visibilitychange', onVisibilityChange);
  });

  onBeforeUnmount(() => {
    stop();
    document.removeEventListener('visibilitychange', onVisibilityChange);
  });
}
