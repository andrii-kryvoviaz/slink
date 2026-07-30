export async function withMinDuration<T>(
  promise: Promise<T>,
  minMs: number,
): Promise<T> {
  const [result] = await Promise.all([
    promise,
    new Promise((resolve) => setTimeout(resolve, minMs)),
  ]);

  return result;
}
