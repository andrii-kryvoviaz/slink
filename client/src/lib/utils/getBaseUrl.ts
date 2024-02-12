export function getBaseUrl() {
  if (typeof window === 'undefined') {
    return process.env.BASE_URL;
  }

  return window.location.origin;
}
