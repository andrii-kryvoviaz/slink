const callback = '/profile/sso/callback';

export const ssoRoutes = {
  callback,
  callbackUrl: (origin: string) => new URL(callback, origin).href,
  login: (slug: string) => `/profile/sso/login/${slug}`,
};
