# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Symfony 5.4 PHP app (PHP >=7.2.5) that renders configurable public-transport departure boards for Prague (PID), backed by the Golemio v2 API (`https://api.golemio.cz/v2/pid/docs/openapi/#`). Frontend is Vue 3 + TypeScript mounted as an island into a thin Twig shell. Assets are built with Webpack Encore (Less + Vue loader + ts-loader).

Requires `PID_KEY` in `.env.local` (or the environment) — `PidApi::__construct` throws "Api is not configured" without it. `PID_URL` defaults to `https://api.golemio.cz`.

## Commands

Backend (Symfony):
- `composer install` — installs deps and runs `auto-scripts` (cache clear, asset install) plus `npm install` + `npm run build` via `post-install-cmd`.
- `php bin/console cache:clear` — clear Symfony cache.
- `php -S 127.0.0.1:8000 -t public` — simple local dev server.

Frontend (Webpack Encore):
- `npm run dev` — one-shot dev build.
- `npm run watch` — dev build + watch.
- `npm run dev-server` — Encore dev server.
- `npm run build` — production build (also run automatically by Composer post-install/update).

There is no test suite, linter, or formatter wired up in this repo.

## Deployment

`.github/workflows/deploy.yml` triggers only on push to the **`release`** branch (or manual dispatch). It SSHes to the production host and runs `git pull origin release && composer install --no-dev --optimize-autoloader`. Typical flow: merge feature PRs into `main`, then promote `main` → `release` to ship. Do not ship by pushing directly — use a PR.

## Architecture

### Request flow
`config/routes.yaml` defines page routes and a JSON API:
- `GET /` → `IndexController::index` → `index.html.twig` (Vue `IndexPage`).
- `GET /stops`, `GET /departures` → `IndexController::stops` / `departures` → Vue `StopsPage` / `DeparturesPage` (debug search forms).
- `GET /board/{name}` (default `home`) → `BoardsController::index` → `board.html.twig` with `data-board-name` (Vue `BoardPage`).
- `GET /api/boards`, `/api/board/{name}`, `/api/stops?names=`, `/api/departures?names=` → `ApiController` returning JSON.

### Twig is a thin shell
Every page renders `base.html.twig` → a single `<div id="app" data-page="..." [data-extra-props]></div>`. Vue reads `dataset.page` in `assets/main.ts`, picks the matching page component, passes other `data-*` attrs as props, provides `BoardApi`, and mounts on `#app`. **No rendering logic belongs in Twig.**

### Board configuration (PHP)
`App\Service\BoardRegistry` is the single source of truth for both the nav list and per-slug settings:
- `getBoardList()` → `[{slug, label}, ...]` used by the Vue `AppNav` and `IndexPage`.
- `getBoardSettings(string $name)` → array of stop specs consumed by `App\Model\Board::getData`.

Adding a board = add one `case` in `BoardRegistry::getBoardSettings` + one entry in `BoardRegistry::BOARDS`. No Vue changes required.

Each stop spec:
```php
[
  'name'     => 'Display label',
  'query'    => ['ids' => [...], 'names' => [...]],   // Golemio stop identifiers
  'filters'  => FilterInterface | callable | array,    // optional
  'past_count' => 1, 'future_count' => 5,              // optional
  'max_timerange_minutes' => 90,                        // optional
]
```

Use `/api/stops?names=...` (or the `/stops` debug page) to discover Golemio `U…` IDs.

### Filters (`src/Filters/`)
`FilterInterface` + abstract `Filter` implementations are applied in `PidApiResponse::getFilteredData()`. `$filters` may be a single `FilterInterface` (e.g. `FilterByRouteNumber(['S3','R21'])`, `FilterByExcludeRouteNumber([...])`), a plain `callable` receiving the response item (used for destination-substring matches, see `from_work_maddz`'s `Praha-Krč` stop), or an array of the above (applied in sequence).

### API client + serialization (`src/Model/PIDApi/`)
`PidApi::get(PidApiRequestInterface)` builds the URL from `$data::getRoute()`, calls `makeResponse()` on the request object to produce a strongly-typed `PidApiResponseInterface`, and hydrates each item via `getItemClass()`. `prepareQuery()` rewrites PHP's `param%5B0%5D` array encoding to `param%5B%5D` because Golemio expects bare `[]`.

Serialization for JSON API: `PidApiResponseItem::toArray()` emits every mapped column plus `_ts`/`_short`/`_diff` derived fields for each time column. Both `Stops` and `Departures` items get this for free.

### Frontend structure (`assets/`)
```
main.ts                    # entry: reads data-page, mounts page, provides BoardApi
pages/{Board,Index,Stops,Departures}Page.vue
components/
  AppNav.vue               # nav + toggle (sessionStorage state)
  CurrentTime.vue          # ticking clock
  StopPanel.vue            # one stop column
  DepartureRow.vue         # one row; computes past/current/future class
  RawSearchForm.vue        # shared form for /stops + /departures
composables/
  useCurrentTime.ts        # shared 1 Hz clock (onBeforeUnmount cleanup)
  useAutoRefresh.ts        # polls a fn; pauses on hidden tab
  useBoardData.ts          # BoardApi.getBoard + auto-refresh (30 s default)
services/boardApi.ts       # BoardApi interface + fetch impl; exposed via provide/inject
constants/transportType.ts # mirrors src/Constants/TransportType.php
types/departure.ts         # Departure, StopTimetable, BoardResponse, BoardNavEntry
styles/*.less              # unchanged; imported from main.ts
```

Design constraints when editing the frontend:
- **One Encore entry (`app`)**. Don't add per-page JS bundles — `main.ts` dispatches via `data-page`.
- **No vue-router, no Pinia**. Page = data root. If you reach for shared state, use `provide`/`inject` through the page component first.
- **Components depend on `BoardApiKey`**, not `fetch`. That's the seam for tests/mocks.
- **Time comparisons use `useCurrentTime().nowSeconds`**, not `Date.now()` inline, so rows re-render on every tick.
- **TypeScript is `strict: true`**. Pin `typescript` to 4.9.x — `@symfony/webpack-encore@4` has a peerOptional that rejects TS 5.
