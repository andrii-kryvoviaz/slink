import { defineSound } from '../defineSound';

export default defineSound({
  masterGain: 0.8,
  layers: [
    {
      kind: 'oscillator',
      wave: 'sine',
      startFreq: 600,
      endFreq: 1400,
      envelope: { peak: 0.32, attack: 0.004, decay: 0.08 },
    },
  ],
});
