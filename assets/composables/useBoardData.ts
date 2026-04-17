import { inject, ref, type Ref } from 'vue';
import { BoardApiKey } from '../services/boardApi';
import type { BoardResponse } from '../types/departure';
import { useAutoRefresh } from './useAutoRefresh';

export interface BoardDataState {
  data: Ref<BoardResponse>;
  loading: Ref<boolean>;
  error: Ref<string | null>;
  lastSuccessAt: Ref<number | null>;
  refresh: () => Promise<void>;
}

export function useBoardData(boardName: string, refreshMs = 30_000): BoardDataState {
  const api = inject(BoardApiKey);
  if (!api) {
    throw new Error('BoardApi not provided');
  }

  const data = ref<BoardResponse>([]);
  const loading = ref<boolean>(false);
  const error = ref<string | null>(null);
  const lastSuccessAt = ref<number | null>(null);

  const refresh = async (): Promise<void> => {
    loading.value = true;
    try {
      data.value = await api.getBoard(boardName);
      error.value = null;
      lastSuccessAt.value = Date.now();
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Unknown error';
    } finally {
      loading.value = false;
    }
  };

  void refresh();
  useAutoRefresh(refresh, { intervalMs: refreshMs });

  return { data, loading, error, lastSuccessAt, refresh };
}
