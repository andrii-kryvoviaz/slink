import { loadLocales } from 'wuchale/load-utils/server';

import { locales } from './locales/data.js';
import * as js from './locales/js.loader.server.js';
import * as main from './locales/main.loader.server.svelte.js';

await loadLocales(main.key, main.loadCount, main.loadCatalog, locales);
await loadLocales(js.key, js.loadCount, js.loadCatalog, locales);

export { handle } from '@slink/lib/server/hooks';
