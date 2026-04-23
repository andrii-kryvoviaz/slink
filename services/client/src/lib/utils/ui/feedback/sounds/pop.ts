import { defineSound } from '../defineSound';

export default defineSound({
  masterGain: 0.75,
  layers: [
    {
      kind: 'oscillator',
      wave: 'sine',
      startFreq: 880,
      endFreq: 440,
      envelope: { peak: 0.35, attack: 0.003, decay: 0.04 },
    },
  ],
});
