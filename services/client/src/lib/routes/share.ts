export const shareRoutes = {
  locked: (shareId: string) => `/share/locked/${encodeURIComponent(shareId)}`,
  unavailable: '/image/unavailable',
} as const;
