import { defineSound } from '../defineSound';

export default defineSound({
  masterGain: 0.85,
  layers: [
    {
      kind: 'oscillator',
      wave: 'triangle',
      startFreq: 1200,
      endFreq: 400,
      envelope: { peak: 0.38, attack: 0.001, decay: 0.018 },
    },
    {
      kind: 'noise',
      envelope: { peak: 0.2, attack: 0.0008, decay: 0.006 },
      bandpass: { frequency: 3000, q: 3 },
      highpass: { frequency: 1500 },
    },
  ],
});
