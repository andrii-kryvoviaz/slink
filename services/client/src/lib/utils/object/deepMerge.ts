type IndexableObject = Record<string, unknown>;

export function deepMerge<T extends IndexableObject>(target: T, source: T): T {
  const output: IndexableObject = { ...target };

  for (const key of Object.keys(source) as (keyof T & string)[]) {
    const sourceValue = source[key];
    const targetValue = target[key];

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
