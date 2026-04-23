import { defineSound } from '../defineSound';

export default defineSound({
  repeat: 1,
  interval: 0.065,
  masterGain: 0.9,
  layers: [
    {
      kind: 'oscillator',
      wave: 'triangle',
      startFreq: 4200,
      endFreq: 1100,
      envelope: { peak: 0.42, attack: 0.001, decay: 0.014 },
    },
    {
      kind: 'oscillator',
      wave: 'sine',
      startFreq: 2600,
      endFreq: 1800,
      envelope: { peak: 0.22, attack: 0.001, decay: 0.02 },
    },
    {
      kind: 'noise',
      envelope: { peak: 0.28, attack: 0.0008, decay: 0.008 },
      bandpass: { frequency: 5200, q: 4.5 },
      highpass: { frequency: 2500 },
    },
  ],
});
