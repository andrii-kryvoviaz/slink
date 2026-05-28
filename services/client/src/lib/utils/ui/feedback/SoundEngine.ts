import type {
  Envelope,
  NoiseLayer,
  OscillatorLayer,
  SoundLayer,
  SoundSpec,
} from './types';

const SILENCE = 0.0001;
const STOP_PADDING = 0.02;

export class SoundEngine {
  private _ctx: AudioContext | null = null;
  private _noiseBuffer: AudioBuffer | null = null;

  play(spec: SoundSpec): void {
    const ctx = this._getContext();
    if (!ctx) return;

    if (ctx.state === 'suspended') {
      void ctx.resume();
    }

    const master = ctx.createGain();
    master.gain.value = spec.masterGain ?? 0.85;
    master.connect(ctx.destination);

    const repeat = spec.repeat ?? 1;
    const interval = spec.interval ?? 0;
    const now = ctx.currentTime;

    for (let i = 0; i < repeat; i++) {
      const baseTime = now + i * interval;
      for (const layer of spec.layers) {
        this._scheduleLayer(ctx, master, layer, baseTime);
      }
    }
  }

  private _getContext(): AudioContext | null {
    if (typeof window === 'undefined') return null;
    if (this._ctx) return this._ctx;

    const Ctor =
      window.AudioContext ||
      (window as unknown as { webkitAudioContext?: typeof AudioContext })
        .webkitAudioContext;
    if (!Ctor) return null;

    this._ctx = new Ctor();
    return this._ctx;
  }

  private _getNoiseBuffer(ctx: AudioContext): AudioBuffer {
    if (this._noiseBuffer) return this._noiseBuffer;

    const length = ctx.sampleRate;
    const buffer = ctx.createBuffer(1, length, ctx.sampleRate);
    const data = buffer.getChannelData(0);
    for (let i = 0; i < length; i++) {
      data[i] = Math.random() * 2 - 1;
    }

    this._noiseBuffer = buffer;
    return buffer;
  }

  private _scheduleLayer(
    ctx: AudioContext,
    dest: AudioNode,
    layer: SoundLayer,
    baseTime: number,
  ): void {
    const start = baseTime + (layer.delay ?? 0);

    if (layer.kind === 'oscillator') {
      this._scheduleOscillator(ctx, dest, layer, start);
      return;
    }

    this._scheduleNoise(ctx, dest, layer, start);
  }

  private _applyEnvelope(
    gain: GainNode,
    envelope: Envelope,
    start: number,
  ): number {
    const { peak, attack, decay } = envelope;
    gain.gain.setValueAtTime(SILENCE, start);
    gain.gain.exponentialRampToValueAtTime(peak, start + attack);
    gain.gain.exponentialRampToValueAtTime(SILENCE, start + attack + decay);
    return start + attack + decay;
  }

  private _scheduleOscillator(
    ctx: AudioContext,
    dest: AudioNode,
    layer: OscillatorLayer,
    start: number,
  ): void {
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();

    osc.type = layer.wave;
    osc.frequency.setValueAtTime(layer.startFreq, start);
    if (layer.endFreq !== undefined && layer.endFreq !== layer.startFreq) {
      osc.frequency.exponentialRampToValueAtTime(
        layer.endFreq,
        start + layer.envelope.attack + layer.envelope.decay * 0.4,
      );
    }

    const end = this._applyEnvelope(gain, layer.envelope, start);
    osc.connect(gain);
    gain.connect(dest);

    osc.start(start);
    osc.stop(end + STOP_PADDING);
  }

  private _scheduleNoise(
    ctx: AudioContext,
    dest: AudioNode,
    layer: NoiseLayer,
    start: number,
  ): void {
    const source = ctx.createBufferSource();
    source.buffer = this._getNoiseBuffer(ctx);

    let node: AudioNode = source;

    if (layer.bandpass) {
      const bp = ctx.createBiquadFilter();
      bp.type = 'bandpass';
      bp.frequency.setValueAtTime(layer.bandpass.frequency, start);
      bp.Q.setValueAtTime(layer.bandpass.q ?? 1, start);
      node.connect(bp);
      node = bp;
    }

    if (layer.highpass) {
      const hp = ctx.createBiquadFilter();
      hp.type = 'highpass';
      hp.frequency.setValueAtTime(layer.highpass.frequency, start);
      node.connect(hp);
      node = hp;
    }

    const gain = ctx.createGain();
    const end = this._applyEnvelope(gain, layer.envelope, start);
    node.connect(gain);
    gain.connect(dest);

    source.start(start);
    source.stop(end + STOP_PADDING);
  }
}

export const soundEngine = new SoundEngine();
