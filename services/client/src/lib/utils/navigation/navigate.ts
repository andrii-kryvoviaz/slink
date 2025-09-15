import { goto } from '$app/navigation';

/**
 * Navigates to a URL using the appropriate method
 * - Internal routes: Uses SvelteKit's goto for SPA navigation
 * - External URLs: Opens in new tab using window.open
 */
export function navigateToUrl(url: string): void {
  if (isInternalUrl(url)) {
    goto(url);
  } else {
    window.open(url, '_blank');
  }
}

/**
 * Checks if a URL is an internal route
 */
export function isInternalUrl(url: string): boolean {
  return url.startsWith('/') && !url.startsWith('//');
}

/**
 * Checks if a URL is an external link
 */
export function isExternalUrl(url: string): boolean {
  return url.startsWith('http') || url.startsWith('//');
}
