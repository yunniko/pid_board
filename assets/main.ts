import { createApp, h, type Component } from 'vue';
import { BoardApiKey, httpBoardApi } from './services/boardApi';
import BoardPage from './pages/BoardPage.vue';
import IndexPage from './pages/IndexPage.vue';
import StopsPage from './pages/StopsPage.vue';
import DeparturesPage from './pages/DeparturesPage.vue';

import './styles/main.less';
import './styles/board.less';
import './styles/index.less';

type PageKey = 'board' | 'index' | 'stops' | 'departures';

const pages: Record<PageKey, Component> = {
  board: BoardPage,
  index: IndexPage,
  stops: StopsPage,
  departures: DeparturesPage,
};

function isPageKey(value: string | undefined): value is PageKey {
  return value === 'board' || value === 'index' || value === 'stops' || value === 'departures';
}

function readProps(root: HTMLElement): Record<string, string> {
  const result: Record<string, string> = {};
  for (const [key, value] of Object.entries(root.dataset)) {
    if (key === 'page' || value === undefined) continue;
    result[key] = value;
  }
  return result;
}

const root = document.getElementById('app');
if (root) {
  const page = root.dataset.page;
  if (!isPageKey(page)) {
    throw new Error(`Unknown page: ${page ?? '(missing data-page)'}`);
  }
  const Page = pages[page];
  const props = readProps(root);
  const app = createApp({ render: () => h(Page, props) });
  app.provide(BoardApiKey, httpBoardApi);
  app.mount(root);
}
