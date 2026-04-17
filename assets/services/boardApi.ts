import type { InjectionKey } from 'vue';
import type { BoardNavEntry, BoardResponse, RawRecord } from '../types/departure';

export interface BoardApi {
  getBoards(): Promise<BoardNavEntry[]>;
  getBoard(name: string): Promise<BoardResponse>;
  getStops(names: string): Promise<RawRecord[]>;
  getDepartures(names: string): Promise<RawRecord[]>;
}

async function getJson<T>(url: string): Promise<T> {
  const response = await fetch(url, { headers: { Accept: 'application/json' } });
  if (!response.ok) {
    throw new Error(`Request failed: ${response.status} ${response.statusText}`);
  }
  return response.json() as Promise<T>;
}

export const httpBoardApi: BoardApi = {
  getBoards: () => getJson<BoardNavEntry[]>('/api/boards'),
  getBoard: (name) => getJson<BoardResponse>(`/api/board/${encodeURIComponent(name)}`),
  getStops: (names) => getJson<RawRecord[]>(`/api/stops?names=${encodeURIComponent(names)}`),
  getDepartures: (names) => getJson<RawRecord[]>(`/api/departures?names=${encodeURIComponent(names)}`),
};

export const BoardApiKey: InjectionKey<BoardApi> = Symbol('BoardApi');
