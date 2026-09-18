export type IndexableObject = Record<string, unknown>;

export type DeepPartial<T> = {
  [P in keyof T]?: T[P] extends object ? DeepPartial<T[P]> : T[P];
};

export function deepMerge<T extends object>(
  target: T,
  source: DeepPartial<T>,
): T {
  const output: IndexableObject = Object.fromEntries(Object.entries(target));

  for (const [key, sourceValue] of Object.entries(source)) {
    const targetValue: unknown = Reflect.get(target, key);

    if (!isObject(sourceValue) || !isObject(targetValue)) {
      output[key] = sourceValue;
      continue;
    }

    output[key] = deepMerge(targetValue, sourceValue);
  }

  return output as T;
}

export function isObject(item: unknown): item is IndexableObject {
  return !!item && typeof item === 'object' && !Array.isArray(item);
}
