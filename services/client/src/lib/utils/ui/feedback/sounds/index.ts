import chirp from './chirp';
import click from './click';
import pop from './pop';
import thock from './thock';
import tink from './tink';

export const sounds = {
  chirp,
  click,
  pop,
  thock,
  tink,
};

export type SoundName = keyof typeof sounds;
