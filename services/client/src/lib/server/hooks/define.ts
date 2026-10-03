import type { RequestEvent } from '@sveltejs/kit';
import type { Handle } from '@sveltejs/kit/hooks';

export type LocalsInitializer = (event: RequestEvent) => void | Promise<void>;

interface InitHookDefinition {
  init: LocalsInitializer;
  handle?: never;
}

interface HandleHookDefinition {
  handle: Handle;
  init?: never;
}

export type HookDefinition = InitHookDefinition | HandleHookDefinition;

export const defineHook = (definition: HookDefinition): HookDefinition =>
  definition;
