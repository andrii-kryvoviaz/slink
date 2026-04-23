export type Envelope = {
  peak: number;
  attack: number;
  decay: number;
};

export type OscillatorWave = 'sine' | 'square' | 'sawtooth' | 'triangle';

export type OscillatorLayer = {
  kind: 'oscillator';
  wave: OscillatorWave;
  startFreq: number;
  endFreq?: number;
  envelope: Envelope;
  delay?: number;
};

export type NoiseLayer = {
  kind: 'noise';
  envelope: Envelope;
  bandpass?: { frequency: number; q?: number };
  highpass?: { frequency: number };
  delay?: number;
};

export type SoundLayer = OscillatorLayer | NoiseLayer;

export type SoundSpec = {
  layers: SoundLayer[];
  repeat?: number;
  interval?: number;
  masterGain?: number;
};

export type SoundOverrides = Partial<SoundSpec>;
export type Sound = (overrides?: SoundOverrides) => void;
