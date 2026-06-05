# PID Board

Configurable public-transport departure boards for Prague (PID), backed by the
[Golemio v2 API](https://api.golemio.cz/v2/pid/docs/openapi/). Define a board
once in PHP and it appears in the nav and renders live departures — no frontend
changes required.

**Stack:** Symfony 5.4 (PHP ≥ 7.2.5) · Vue 3 + TypeScript · Webpack Encore (Less, Vue loader, ts-loader)

## What it does

Renders one or more "boards" — each a set of stops with live departures pulled
from Golemio. Time columns are enriched server-side with derived fields
(timestamp, short label, minutes-from-now), and the frontend re-renders every
second so past/current/future departures stay accurate without reloading.

## Architecture at a glance

The design deliberately keeps a single source of truth on the backend and
treats the frontend as a thin, data-driven island.

- **Twig is a thin shell.** Every page renders `base.html.twig` → a single
  `<div id="app" data-page="…">`. Vue reads `data-page` in `assets/main.ts`,
  selects the matching page component, and passes remaining `data-*` attributes
  as props. No rendering logic lives in Twig.
- **Boards are configuration, not code paths.** `App\Service\BoardRegistry` is
  the single source of truth for both the nav list and per-slug settings.
  **Adding a board = one entry in `BoardRegistry::BOARDS` + one case in
  `getBoardSettings()`. No Vue changes needed.**
- **Composable filters.** Stop specs accept a `FilterInterface`, a plain
  callable, or an array of them, applied in sequence (e.g.
  `FilterByRouteNumber(['S3','R21'])`, destination-substring matches, etc.).
- **Strongly-typed API client.** `PidApi::get()` builds the request from the
  request object's route, produces a typed response, and hydrates each item.
  Items serialize to JSON with derived `_ts` / `_short` / `_diff` time fields
  for free.

### Frontend design constraints

- One Encore entry (`app`); `main.ts` dispatches by `data-page` — no per-page bundles.
- No vue-router, no Pinia. The page component is the data root; shared state
  flows via `provide`/`inject`.
- Components depend on an injected `BoardApi` (the seam for tests/mocks), not on `fetch` directly.
- Time comparisons use a shared 1 Hz clock (`useCurrentTime().nowSeconds`) so rows tick in sync.
- TypeScript runs in `strict` mode.

## Getting started

### Requirements
- PHP ≥ 7.2.5, Composer
- Node.js + npm
- A Golemio API key

### Setup
```bash
composer install        # installs PHP + npm deps and builds assets
```
Create `.env.local` with your key:
