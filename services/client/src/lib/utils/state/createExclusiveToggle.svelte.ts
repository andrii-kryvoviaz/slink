export function createExclusiveToggle<K extends string>(...keys: K[]) {
  let _active = $state<K | null>(null);
  let _suspended = $state<K | null>(null);

  const group: Record<string, unknown> = {};

  for (const key of keys) {
    Object.defineProperty(group, key, {
      get: () => _active === key,
      set: (v: boolean) => {
        _active = v ? key : null;
      },
      enumerable: true,
      configurable: true,
    });
  }

  group.suspend = () => {
    _suspended = _active;
    _active = null;
  };
  group.restore = () => {
    _active = _suspended;
    _suspended = null;
  };

  return group as { [P in K]: boolean } & {
    suspend: () => void;
    restore: () => void;
  };
}
