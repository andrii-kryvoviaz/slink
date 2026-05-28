import type { Reroute } from '@sveltejs/kit';

export const sequenceReroute =
  (...rules: Reroute[]): Reroute =>
  async (args) => {
    for (const rule of rules) {
      const result = await rule(args);
      if (result !== undefined) {
        return result;
      }
    }
  };
