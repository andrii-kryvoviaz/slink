export async function formData(request: Request) {
  return Object.fromEntries(await request.formData()) as Record<string, string>;
}
