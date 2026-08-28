export type ScrollTelemetrySample = {
  scroll: number
  limit: number
  velocity?: number
  direction?: number
  progress?: number
}

const clamp = (value: number, min: number, max: number) => Math.min(max, Math.max(min, value))
const damp = (from: number, to: number, lambda: number, dt: number) =>
  from + (to - from) * (1 - Math.exp(-lambda * dt))

/**
 * Converts raw scroll data into stable visual signals.
 *
 * UI effects read CSS variables instead of each installing their own scroll
 * listener. This keeps the scroll experience coherent and prevents several
 * independent high-frequency handlers from fighting on the main thread.
 */
export class ScrollKinetics {
  private targetSpeed = 0
  private targetSigned = 0
  private speed = 0
  private signed = 0
  private progress = 0
  private direction = 1
  private raf = 0
  private lastTime = 0
  private destroyed = false

  constructor(private readonly root: HTMLElement = document.documentElement) {
    this.write()
  }

  push(sample: ScrollTelemetrySample) {
    if (this.destroyed) return
    const limit = Math.max(1, sample.limit)
    this.progress = clamp(sample.progress ?? sample.scroll / limit, 0, 1)

    const velocity = Number.isFinite(sample.velocity) ? Number(sample.velocity) : 0
    if (velocity !== 0) this.direction = velocity > 0 ? 1 : -1
    else if (sample.direction) this.direction = sample.direction > 0 ? 1 : -1

    // Lenis velocity is intentionally normalized generously: normal wheel
    // movement lives around 0.15-0.45 while deliberate flicks can reach 1.
    const normalized = clamp(Math.abs(velocity) / 2.4, 0, 1)
    this.targetSpeed = Math.max(this.targetSpeed, normalized)
    this.targetSigned = this.targetSpeed * this.direction
    this.ensureTicking()
  }

  setNativeProgress(scroll: number, limit: number) {
    if (this.destroyed) return
    this.progress = clamp(scroll / Math.max(1, limit), 0, 1)
    this.write()
  }

  destroy() {
    this.destroyed = true
    cancelAnimationFrame(this.raf)
    this.raf = 0
    this.root.style.removeProperty('--scroll-progress')
    this.root.style.removeProperty('--scroll-speed')
    this.root.style.removeProperty('--scroll-signed')
    this.root.style.removeProperty('--scroll-direction')
  }

  private ensureTicking() {
    if (this.raf || this.destroyed) return
    this.lastTime = performance.now()
    this.raf = requestAnimationFrame(this.tick)
  }

  private readonly tick = (now: number) => {
    this.raf = 0
    if (this.destroyed) return

    const dt = clamp((now - this.lastTime) / 1000, 1 / 240, 0.05)
    this.lastTime = now
    this.speed = damp(this.speed, this.targetSpeed, 10, dt)
    this.signed = damp(this.signed, this.targetSigned, 9, dt)
    this.targetSpeed = damp(this.targetSpeed, 0, 4.2, dt)
    this.targetSigned = this.targetSpeed * this.direction
    this.write()

    if (this.speed > 0.002 || this.targetSpeed > 0.002) {
      this.raf = requestAnimationFrame(this.tick)
    } else {
      this.speed = 0
      this.signed = 0
      this.write()
    }
  }

  private write() {
    this.root.style.setProperty('--scroll-progress', this.progress.toFixed(5))
    this.root.style.setProperty('--scroll-speed', this.speed.toFixed(4))
    this.root.style.setProperty('--scroll-signed', this.signed.toFixed(4))
    this.root.style.setProperty('--scroll-direction', String(this.direction))
  }
}
