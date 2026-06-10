import type { Handle, RequestEvent } from '@sveltejs/kit';

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
