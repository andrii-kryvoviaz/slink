import type { Handle } from '@sveltejs/kit';
import { runWithLocale } from 'wuchale/load-utils/server';

import { defineHook } from '../define';

const applyClientLocale: Handle = async ({ event, resolve }) =>
  runWithLocale(event.locals.locale, () => resolve(event));

export default defineHook({ handle: applyClientLocale });
