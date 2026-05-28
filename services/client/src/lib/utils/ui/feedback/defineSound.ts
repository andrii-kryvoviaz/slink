import { soundEngine } from './SoundEngine';
import type { Sound, SoundSpec } from './types';

export const defineSound = (spec: SoundSpec): Sound => {
  return (overrides) => soundEngine.play({ ...spec, ...overrides });
};
