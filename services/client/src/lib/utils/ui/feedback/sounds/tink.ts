import { defineSound } from '../defineSound';

export default defineSound({
  masterGain: 0.65,
  layers: [
    {
      kind: 'oscillator',
      wave: 'sine',
      startFreq: 2600,
      envelope: { peak: 0.28, attack: 0.002, decay: 0.18 },
    },
    {
      kind: 'oscillator',
      wave: 'sine',
      startFreq: 3900,
      envelope: { peak: 0.15, attack: 0.002, decay: 0.14 },
      delay: 0.005,
    },
  ],
});
