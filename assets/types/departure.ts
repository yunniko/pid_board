import type { TransportTypeValue } from '../constants/transportType';

export interface Departure {
  departure_predicted_ts: number;
  departure_predicted_short: string;
  departure_predicted_diff: number;
  departure_scheduled_short: string;
  route_number: string;
  destination: string;
  transport_type: TransportTypeValue;
  platform: string | null;
  [key: string]: unknown;
}

export interface StopTimetable {
  stop: string;
  departures: Departure[];
  error?: string | null;
}

export type BoardResponse = StopTimetable[];

export interface BoardNavEntry {
  slug: string;
  label: string;
}

export type RawRecord = Record<string, unknown>;
