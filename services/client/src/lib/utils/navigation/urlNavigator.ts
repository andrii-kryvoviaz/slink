import { goto } from '$app/navigation';

export interface NavigationConfig {
  replaceState?: boolean;
  noScroll?: boolean;
  invalidateAll?: boolean;
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

/**
 * Navigates to a URL using the appropriate method
 * - Internal routes: Uses SvelteKit's goto for SPA navigation
 * - External URLs: Opens in new tab using window.open
 */
export async function navigateToUrl(
  url: string,
  config?: NavigationConfig,
): Promise<void> {
  if (isInternalUrl(url)) {
    await goto(url, config);
  } else {
    window.open(url, '_blank');
  }
}

/**
 * Navigate with replaceState = true (default for internal navigation)
 */
export async function replaceUrl(
  url: string,
  config?: Omit<NavigationConfig, 'replaceState'>,
): Promise<void> {
  await navigateToUrl(url, { ...config, replaceState: true });
}

/**
 * Navigate with replaceState = false
 */
export async function pushUrl(
  url: string,
  config?: Omit<NavigationConfig, 'replaceState'>,
): Promise<void> {
  await navigateToUrl(url, { ...config, replaceState: false });
}
