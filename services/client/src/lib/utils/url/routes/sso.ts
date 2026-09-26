const callback = '/profile/sso/callback';

export const ssoRoutes = {
  callback,
  callbackUrl: (origin: string) => new URL(callback, origin).href,
};
