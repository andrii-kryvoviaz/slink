import type { Handle } from '@sveltejs/kit';
import { sequence } from '@sveltejs/kit/hooks';

import { type HookSettings, hookSettings } from './config';
import type { HookDefinition } from './define';
import type { HookId } from './manifest.gen';

const STAGE_ORDER = ['request', 'locals', 'render', 'response'] as const;

type Stage = (typeof STAGE_ORDER)[number];

interface RegisteredHook {
  path: string;
  id: HookId;
  stage: Stage;
  settings: HookSettings;
  definition: HookDefinition;
}

const modules = import.meta.glob<{ default: HookDefinition }>('./*/*.hook.ts', {
  eager: true,
});

const isStage = (value: string): value is Stage =>
  (STAGE_ORDER as readonly string[]).includes(value);

const isHookId = (value: string): value is HookId => value in hookSettings;

const resolveStage = (path: string): Stage => {
  const segment = path.split('/')[1] ?? '';

  if (!isStage(segment)) {
    throw new Error(
      `Hook "${path}" is in unknown stage folder "${segment}". Expected one of: ${STAGE_ORDER.join(', ')}.`,
    );
  }

  return segment;
};

const resolveId = (path: string): HookId => {
  const id = path.replace(/^\.\//, '').replace(/\.hook\.ts$/, '');

  if (!isHookId(id)) {
    throw new Error(
      `Hook "${path}" has no settings entry "${id}" in hooks/config.ts.`,
    );
  }

  return id;
};

const assertNoOrphanSettings = (hooks: RegisteredHook[]): void => {
  const discovered = new Set<string>(hooks.map(({ id }) => id));

  for (const id of Object.keys(hookSettings)) {
    if (!discovered.has(id)) {
      throw new Error(
        `Hook settings entry "${id}" has no matching file "./${id}.hook.ts".`,
      );
    }
  }
};

const assertUniqueOrders = (hooks: RegisteredHook[]): void => {
  const seen = new Map<string, string>();

  for (const { path, stage, settings } of hooks) {
    const key = `${stage}:${settings.order}`;
    const existing = seen.get(key);

    if (existing) {
      throw new Error(
        `Duplicate hook order ${settings.order} in stage "${stage}": "${existing}" and "${path}".`,
      );
    }

    seen.set(key, path);
  }
};

const compareHooks = (a: RegisteredHook, b: RegisteredHook): number => {
  const stageDiff = STAGE_ORDER.indexOf(a.stage) - STAGE_ORDER.indexOf(b.stage);

  if (stageDiff !== 0) {
    return stageDiff;
  }

  return a.settings.order - b.settings.order;
};

const isEnabled = ({ settings }: RegisteredHook): boolean =>
  settings.enabled !== false;

const toHandle = ({ path, definition }: RegisteredHook): Handle => {
  const { init, handle } = definition;

  if (init && handle) {
    throw new Error(
      `Hook "${path}" must define exactly one of "init" or "handle", got both.`,
    );
  }

  if (handle) {
    return handle;
  }

  if (!init) {
    throw new Error(
      `Hook "${path}" must define exactly one of "init" or "handle", got neither.`,
    );
  }

  return async ({ event, resolve }) => {
    await init(event);
    return resolve(event);
  };
};

const hooks: RegisteredHook[] = Object.entries(modules).map(
  ([path, module]) => {
    const id = resolveId(path);

    return {
      path,
      id,
      stage: resolveStage(path),
      settings: hookSettings[id],
      definition: module.default,
    };
  },
);

assertNoOrphanSettings(hooks);
assertUniqueOrders(hooks);
hooks.sort(compareHooks);

export const handle = sequence(...hooks.filter(isEnabled).map(toHandle));
