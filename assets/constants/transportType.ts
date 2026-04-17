export const TransportType = {
  TRAM: 0,
  METRO: 1,
  TRAIN: 2,
  BUS: 3,
  TROLLEYBUS: 11,
} as const;

export type TransportTypeValue = (typeof TransportType)[keyof typeof TransportType];
