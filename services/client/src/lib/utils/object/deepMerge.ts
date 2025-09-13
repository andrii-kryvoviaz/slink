type IndexableObject = Record<string, unknown>;

export function deepMerge<T extends IndexableObject>(target: T, source: T): T {
  const output: T = { ...target };
  const stack: Array<{ target: IndexableObject; source: IndexableObject }> = [
    { target: output, source },
  ];

  while (stack.length > 0) {
    const { target, source } = stack.pop()!;
    const keys = Object.keys(source) as (keyof T & string)[];

    for (const key of keys) {
      const sourceValue = source[key];
      const targetValue = target[key];

      if (isObject(sourceValue)) {
        if (isObject(targetValue)) {
          stack.push({
            target: targetValue as IndexableObject,
            source: sourceValue as IndexableObject,
          });
        } else {
          target[key] = sourceValue;
        }
      } else {
        target[key] = sourceValue;
      }
    }
  }

  return output;
}

export function isObject(item: unknown): item is IndexableObject {
  return !!item && typeof item === 'object' && !Array.isArray(item);
}
