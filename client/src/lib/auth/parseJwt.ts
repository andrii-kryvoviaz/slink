export function parseJwt<T>(token: string): T {
  return JSON.parse(Buffer.from(token.split('.')[1], 'base64').toString());
}
