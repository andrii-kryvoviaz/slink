export function stripSsrComments(html: string): string {
  return html.replace(/<!--[\s\S]*?-->/g, '');
}

export function ssrViewModeChecked(html: string, label: string): boolean {
  const buttons = html.match(/<button\b[^>]*role="radio"[^>]*>/g) ?? [];
  const target = buttons.find((tag) => tag.includes(`aria-label="${label}"`));
  return target?.includes('aria-checked="true"') ?? false;
}
