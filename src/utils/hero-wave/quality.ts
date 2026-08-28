export type WaveQualityKey = 'economy' | 'balanced' | 'ultra'

export interface WaveQualityPreset {
  key: WaveQualityKey
  lineCount: number
  pointCount: number
  maxDpr: number
  targetFps: number
  softUnderstroke: boolean
  rippleLimit: number
}

export const WAVE_QUALITY: Record<WaveQualityKey, WaveQualityPreset> = {
  economy: {
    key: 'economy',
    lineCount: 30,
    pointCount: 21,
    maxDpr: 1.2,
    targetFps: 45,
    softUnderstroke: false,
    rippleLimit: 2,
  },
  balanced: {
    key: 'balanced',
    lineCount: 48,
    pointCount: 27,
    maxDpr: 1.5,
    targetFps: 60,
    softUnderstroke: true,
    rippleLimit: 3,
  },
  ultra: {
    key: 'ultra',
    lineCount: 66,
    pointCount: 33,
    maxDpr: 1.8,
    targetFps: 120,
    softUnderstroke: true,
    rippleLimit: 4,
  },
}

export const selectInitialWaveQuality = ({
  width,
  coarse,
  hardwareConcurrency,
}: {
  width: number
  coarse: boolean
  hardwareConcurrency: number
}): WaveQualityKey => {
  if (coarse || width < 700 || hardwareConcurrency <= 4) return 'economy'
  if (width < 1180 || hardwareConcurrency <= 8) return 'balanced'
  return 'ultra'
}

const nextLower = (key: WaveQualityKey): WaveQualityKey => {
  if (key === 'ultra') return 'balanced'
  return 'economy'
}

const nextHigher = (key: WaveQualityKey): WaveQualityKey => {
  if (key === 'economy') return 'balanced'
  return 'ultra'
}

/**
 * Conservative runtime governor. It samples render cost rather than rAF
 * interval so a 60 Hz panel is never mistaken for a slow machine.
 */
export class WaveQualityGovernor {
  private emaCost = 0
  private samples = 0
  private lowCostWindows = 0
  private lastChange = -Infinity

  constructor(private key: WaveQualityKey) {}

  setKey(key: WaveQualityKey) {
    this.key = key
    this.emaCost = 0
    this.samples = 0
    this.lowCostWindows = 0
  }

  push(renderCostMs: number, now: number): WaveQualityKey | null {
    const cost = Math.min(40, Math.max(0, renderCostMs))
    this.emaCost = this.samples === 0 ? cost : this.emaCost * 0.94 + cost * 0.06
    this.samples += 1

    if (this.samples < 120 || now - this.lastChange < 7000) return null

    const preset = WAVE_QUALITY[this.key]
    const frameBudget = 1000 / preset.targetFps
    const expensive = this.emaCost > Math.max(5.2, frameBudget * 0.67)
    const veryCheap = this.emaCost < 2.15
    this.samples = 0

    if (expensive && this.key !== 'economy') {
      const changed = nextLower(this.key)
      this.lastChange = now
      this.lowCostWindows = 0
      this.setKey(changed)
      return changed
    }

    if (veryCheap && this.key !== 'ultra') {
      this.lowCostWindows += 1
      if (this.lowCostWindows >= 3) {
        const changed = nextHigher(this.key)
        this.lastChange = now
        this.lowCostWindows = 0
        this.setKey(changed)
        return changed
      }
    } else {
      this.lowCostWindows = 0
    }

    return null
  }
}
