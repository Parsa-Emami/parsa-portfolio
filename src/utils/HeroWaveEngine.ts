import { clamp, damp, hypot2, smoothstep } from './hero-wave/math'
import {
  WAVE_QUALITY,
  WaveQualityGovernor,
  selectInitialWaveQuality,
  type WaveQualityKey,
  type WaveQualityPreset,
} from './hero-wave/quality'

type MeshState = {
  dx: Float32Array
  dy: Float32Array
  vx: Float32Array
  vy: Float32Array
}

type Ripple = {
  x: number
  y: number
  age: number
  strength: number
}

type PointerState = {
  x: number
  y: number
  targetX: number
  targetY: number
  inputVx: number
  inputVy: number
  velocityX: number
  velocityY: number
  energy: number
  inside: boolean
  touching: boolean
  lastInputTime: number
  lastInputX: number
  lastInputY: number
}

const FIXED_STEP = 1 / 120
const MAX_SUBSTEPS = 4
const MAX_ELAPSED = 0.05

export class HeroWaveEngine {
  private readonly host: HTMLElement
  private readonly interactionRoot: HTMLElement
  private readonly canvas: HTMLCanvasElement
  private readonly ctx: CanvasRenderingContext2D

  private width = 1
  private height = 1
  private dpr = 1
  private visible = true
  private destroyed = false
  private raf = 0
  private lastTick = 0
  private lastRender = 0
  private accumulator = 0
  private resizeQueued = false
  private paletteDirty = true

  private qualityKey: WaveQualityKey = 'balanced'
  private quality: WaveQualityPreset = WAVE_QUALITY.balanced
  private qualityGovernor = new WaveQualityGovernor('balanced')
  private mesh: MeshState = this.makeMesh(this.quality)
  private lineBuffer = new Float32Array(this.quality.pointCount * 2)
  private ripples: Ripple[] = []

  private reducedMotion = false
  private coarsePointer = false
  private strokeRgb = '255,255,255'

  private readonly pointer: PointerState = {
    x: 0,
    y: 0,
    targetX: 0,
    targetY: 0,
    inputVx: 0,
    inputVy: 0,
    velocityX: 0,
    velocityY: 0,
    energy: 0,
    inside: false,
    touching: false,
    lastInputTime: 0,
    lastInputX: 0,
    lastInputY: 0,
  }

  private readonly reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)')
  private readonly coarsePointerQuery = window.matchMedia('(pointer: coarse)')
  private readonly resizeObserver: ResizeObserver
  private readonly intersectionObserver: IntersectionObserver

  constructor(host: HTMLElement) {
    this.host = host
    this.canvas = host.querySelector<HTMLCanvasElement>('canvas') ?? this.createCanvas()

    const context = this.canvas.getContext('2d', { alpha: true, desynchronized: true })
    if (!context) throw new Error('HeroWaveEngine: 2D canvas is unavailable')
    this.ctx = context

    this.interactionRoot = host.closest<HTMLElement>('.hero') ?? host.parentElement ?? host
    this.reducedMotion = this.reducedMotionQuery.matches
    this.coarsePointer = this.coarsePointerQuery.matches

    this.resizeObserver = new ResizeObserver(this.queueResize)
    this.intersectionObserver = new IntersectionObserver(this.onIntersection, {
      rootMargin: '160px 0px',
      threshold: 0.01,
    })

    this.bind()
    this.resizeObserver.observe(host)
    this.intersectionObserver.observe(host)
    this.resize()
    this.render(0, true)

    if (!this.reducedMotion) this.raf = requestAnimationFrame(this.tick)
  }

  destroy() {
    if (this.destroyed) return
    this.destroyed = true
    cancelAnimationFrame(this.raf)
    this.resizeObserver.disconnect()
    this.intersectionObserver.disconnect()

    this.interactionRoot.removeEventListener('pointermove', this.onPointerMove)
    this.interactionRoot.removeEventListener('pointerdown', this.onPointerDown)
    this.interactionRoot.removeEventListener('pointerup', this.onPointerUp)
    this.interactionRoot.removeEventListener('pointercancel', this.onPointerUp)
    this.interactionRoot.removeEventListener('pointerleave', this.onPointerLeave)
    document.removeEventListener('visibilitychange', this.onVisibilityChange)
    this.reducedMotionQuery.removeEventListener('change', this.onReducedMotionChange)
    this.coarsePointerQuery.removeEventListener('change', this.onCoarsePointerChange)
    window.removeEventListener('themechange', this.onThemeChange as EventListener)

    this.host.style.removeProperty('--wave-x')
    this.host.style.removeProperty('--wave-y')
    this.host.style.removeProperty('--wave-energy')
  }

  private createCanvas() {
    const canvas = document.createElement('canvas')
    canvas.className = 'hero-wave__canvas'
    canvas.setAttribute('aria-hidden', 'true')
    this.host.prepend(canvas)
    return canvas
  }

  private bind() {
    this.interactionRoot.addEventListener('pointermove', this.onPointerMove, { passive: true })
    this.interactionRoot.addEventListener('pointerdown', this.onPointerDown, { passive: true })
    this.interactionRoot.addEventListener('pointerup', this.onPointerUp, { passive: true })
    this.interactionRoot.addEventListener('pointercancel', this.onPointerUp, { passive: true })
    this.interactionRoot.addEventListener('pointerleave', this.onPointerLeave, { passive: true })
    document.addEventListener('visibilitychange', this.onVisibilityChange, { passive: true })
    this.reducedMotionQuery.addEventListener('change', this.onReducedMotionChange)
    this.coarsePointerQuery.addEventListener('change', this.onCoarsePointerChange)
    window.addEventListener('themechange', this.onThemeChange as EventListener, { passive: true })
  }

  private readonly queueResize = () => {
    if (this.resizeQueued || this.destroyed) return
    this.resizeQueued = true
    requestAnimationFrame(() => {
      this.resizeQueued = false
      if (!this.destroyed) this.resize()
    })
  }

  private readonly onIntersection = (entries: IntersectionObserverEntry[]) => {
    this.visible = entries.some((entry) => entry.isIntersecting)
    if (this.visible && !this.reducedMotion && !this.destroyed && !this.raf) {
      this.lastTick = 0
      this.lastRender = 0
      this.raf = requestAnimationFrame(this.tick)
    }
  }

  private readonly onVisibilityChange = () => {
    if (document.hidden) {
      cancelAnimationFrame(this.raf)
      this.raf = 0
      return
    }

    if (this.visible && !this.reducedMotion && !this.destroyed && !this.raf) {
      this.lastTick = 0
      this.lastRender = 0
      this.raf = requestAnimationFrame(this.tick)
    }
  }

  private readonly onReducedMotionChange = (event: MediaQueryListEvent) => {
    this.reducedMotion = event.matches
    cancelAnimationFrame(this.raf)
    this.raf = 0
    this.accumulator = 0

    if (this.reducedMotion) {
      this.pointer.energy = 0
      this.ripples.length = 0
      this.render(0, true)
    } else if (this.visible && !document.hidden && !this.destroyed) {
      this.lastTick = 0
      this.lastRender = 0
      this.raf = requestAnimationFrame(this.tick)
    }
  }

  private readonly onCoarsePointerChange = (event: MediaQueryListEvent) => {
    this.coarsePointer = event.matches
    this.pickQuality(true)
  }

  private readonly onThemeChange = () => {
    this.paletteDirty = true
  }

  private readonly onPointerMove = (event: PointerEvent) => {
    if (this.reducedMotion) return
    const samples = event.getCoalescedEvents?.() ?? [event]
    for (const sample of samples) this.capturePointerSample(sample)
  }

  private readonly onPointerDown = (event: PointerEvent) => {
    if (this.reducedMotion) return
    this.pointer.touching = event.pointerType === 'touch'
    this.capturePointerSample(event)

    const strength = event.pointerType === 'touch' ? 0.78 : 0.56
    this.ripples.push({
      x: this.pointer.targetX,
      y: this.pointer.targetY,
      age: 0,
      strength,
    })
    if (this.ripples.length > this.quality.rippleLimit) this.ripples.shift()
  }

  private readonly onPointerUp = () => {
    this.pointer.touching = false
  }

  private readonly onPointerLeave = () => {
    this.pointer.inside = false
    this.pointer.touching = false
  }

  private capturePointerSample(event: PointerEvent) {
    const rect = this.interactionRoot.getBoundingClientRect()
    const x = clamp(event.clientX - rect.left, 0, rect.width)
    const y = clamp(event.clientY - rect.top, 0, rect.height)
    const scaleX = this.width / Math.max(1, rect.width)
    const scaleY = this.height / Math.max(1, rect.height)
    const localX = x * scaleX
    const localY = y * scaleY
    const time = event.timeStamp || performance.now()

    if (this.pointer.lastInputTime > 0) {
      const dt = clamp((time - this.pointer.lastInputTime) / 1000, 1 / 240, 0.05)
      const vx = (localX - this.pointer.lastInputX) / dt
      const vy = (localY - this.pointer.lastInputY) / dt
      this.pointer.inputVx = damp(this.pointer.inputVx, vx, 28, dt)
      this.pointer.inputVy = damp(this.pointer.inputVy, vy, 28, dt)
    }

    this.pointer.targetX = localX
    this.pointer.targetY = localY
    this.pointer.lastInputX = localX
    this.pointer.lastInputY = localY
    this.pointer.lastInputTime = time
    this.pointer.inside = true
  }

  private resize() {
    const rect = this.host.getBoundingClientRect()
    const nextWidth = Math.max(1, Math.round(rect.width))
    const nextHeight = Math.max(1, Math.round(rect.height))
    this.width = nextWidth
    this.height = nextHeight

    this.pickQuality(false)
    this.dpr = Math.min(window.devicePixelRatio || 1, this.quality.maxDpr)
    this.canvas.width = Math.max(1, Math.round(this.width * this.dpr))
    this.canvas.height = Math.max(1, Math.round(this.height * this.dpr))
    this.canvas.style.width = `${this.width}px`
    this.canvas.style.height = `${this.height}px`
    this.ctx.setTransform(this.dpr, 0, 0, this.dpr, 0, 0)

    if (!this.pointer.lastInputTime) {
      this.pointer.x = this.pointer.targetX = this.width * 0.5
      this.pointer.y = this.pointer.targetY = this.height * 0.46
    } else {
      this.pointer.x = clamp(this.pointer.x, 0, this.width)
      this.pointer.y = clamp(this.pointer.y, 0, this.height)
      this.pointer.targetX = clamp(this.pointer.targetX, 0, this.width)
      this.pointer.targetY = clamp(this.pointer.targetY, 0, this.height)
    }

    this.paletteDirty = true
    if (this.reducedMotion) this.render(0, true)
  }

  private pickQuality(force: boolean) {
    const key = selectInitialWaveQuality({
      width: this.width,
      coarse: this.coarsePointer,
      hardwareConcurrency: navigator.hardwareConcurrency || 4,
    })

    if (!force && key === this.qualityKey) return
    this.setQuality(key, true)
  }

  private setQuality(key: WaveQualityKey, preserveState: boolean) {
    if (key === this.qualityKey && this.mesh.dx.length > 0) return

    const oldQuality = this.quality
    const oldMesh = this.mesh
    this.qualityKey = key
    this.quality = WAVE_QUALITY[key]
    this.qualityGovernor.setKey(key)
    this.mesh = preserveState
      ? this.resampleMesh(oldMesh, oldQuality, this.quality)
      : this.makeMesh(this.quality)
    this.lineBuffer = new Float32Array(this.quality.pointCount * 2)

    const nextDpr = Math.min(window.devicePixelRatio || 1, this.quality.maxDpr)
    if (Math.abs(nextDpr - this.dpr) > 0.01 && this.width > 1 && this.height > 1) {
      this.dpr = nextDpr
      this.canvas.width = Math.max(1, Math.round(this.width * this.dpr))
      this.canvas.height = Math.max(1, Math.round(this.height * this.dpr))
      this.ctx.setTransform(this.dpr, 0, 0, this.dpr, 0, 0)
    }
  }

  private makeMesh(quality: WaveQualityPreset): MeshState {
    const size = quality.lineCount * quality.pointCount
    return {
      dx: new Float32Array(size),
      dy: new Float32Array(size),
      vx: new Float32Array(size),
      vy: new Float32Array(size),
    }
  }

  private resampleMesh(
    source: MeshState,
    from: WaveQualityPreset,
    to: WaveQualityPreset,
  ): MeshState {
    if (!source.dx.length || from.lineCount < 2 || from.pointCount < 2) return this.makeMesh(to)

    const target = this.makeMesh(to)
    const sourceIndex = (x: number, y: number) => x * from.pointCount + y

    const sample = (array: Float32Array, fx: number, fy: number) => {
      const x = fx * (from.lineCount - 1)
      const y = fy * (from.pointCount - 1)
      const x0 = Math.floor(x)
      const y0 = Math.floor(y)
      const x1 = Math.min(from.lineCount - 1, x0 + 1)
      const y1 = Math.min(from.pointCount - 1, y0 + 1)
      const tx = x - x0
      const ty = y - y0
      const a = array[sourceIndex(x0, y0)]
      const b = array[sourceIndex(x1, y0)]
      const c = array[sourceIndex(x0, y1)]
      const d = array[sourceIndex(x1, y1)]
      const top = a + (b - a) * tx
      const bottom = c + (d - c) * tx
      return top + (bottom - top) * ty
    }

    for (let i = 0; i < to.lineCount; i += 1) {
      const fx = i / Math.max(1, to.lineCount - 1)
      for (let j = 0; j < to.pointCount; j += 1) {
        const fy = j / Math.max(1, to.pointCount - 1)
        const index = i * to.pointCount + j
        target.dx[index] = sample(source.dx, fx, fy)
        target.dy[index] = sample(source.dy, fx, fy)
        target.vx[index] = sample(source.vx, fx, fy)
        target.vy[index] = sample(source.vy, fx, fy)
      }
    }

    return target
  }

  private readonly tick = (now: number) => {
    this.raf = 0
    if (this.destroyed || this.reducedMotion || document.hidden || !this.visible) return

    this.raf = requestAnimationFrame(this.tick)
    const frameBudget = 1000 / this.quality.targetFps
    if (this.lastRender && now - this.lastRender < frameBudget * 0.82) return

    const elapsed = this.lastTick ? Math.min(MAX_ELAPSED, (now - this.lastTick) / 1000) : FIXED_STEP
    this.lastTick = now
    this.accumulator = Math.min(this.accumulator + elapsed, FIXED_STEP * MAX_SUBSTEPS)

    let substeps = 0
    while (this.accumulator >= FIXED_STEP && substeps < MAX_SUBSTEPS) {
      this.simulate(FIXED_STEP, now / 1000)
      this.accumulator -= FIXED_STEP
      substeps += 1
    }

    const renderStart = performance.now()
    this.render(now / 1000, false)
    const renderCost = performance.now() - renderStart
    this.lastRender = now

    const newQuality = this.qualityGovernor.push(renderCost, now)
    if (newQuality && newQuality !== this.qualityKey) this.setQuality(newQuality, true)
  }

  private simulate(dt: number, time: number) {
    const pointer = this.pointer
    const previousX = pointer.x
    const previousY = pointer.y
    pointer.x = damp(pointer.x, pointer.targetX, pointer.touching ? 30 : 22, dt)
    pointer.y = damp(pointer.y, pointer.targetY, pointer.touching ? 30 : 22, dt)
    pointer.velocityX = damp(pointer.velocityX, (pointer.x - previousX) / dt, 16, dt)
    pointer.velocityY = damp(pointer.velocityY, (pointer.y - previousY) / dt, 16, dt)

    const inputSpeed = hypot2(pointer.inputVx, pointer.inputVy)
    const targetEnergy = pointer.inside ? clamp(inputSpeed / 1450, 0.08, 1) : 0
    pointer.energy = damp(pointer.energy, targetEnergy, pointer.inside ? 7 : 2.8, dt)
    pointer.inputVx = damp(pointer.inputVx, 0, 4.4, dt)
    pointer.inputVy = damp(pointer.inputVy, 0, 4.4, dt)

    const { lineCount, pointCount } = this.quality
    const { dx, dy, vx, vy } = this.mesh
    const radius = Math.max(120, Math.min(this.width, this.height) * (this.coarsePointer ? 0.27 : 0.22))
    const radiusInv = 1 / radius
    const coupling = this.qualityKey === 'economy' ? 18 : 21
    const restoring = 17
    const damping = Math.exp(-6.8 * dt)

    for (let i = 0; i < lineCount; i += 1) {
      const fx = i / Math.max(1, lineCount - 1)
      const baseX = fx * this.width
      for (let j = 0; j < pointCount; j += 1) {
        const fy = j / Math.max(1, pointCount - 1)
        const baseY = fy * this.height
        const index = i * pointCount + j

        const ambientX =
          Math.sin(time * 0.62 + fy * 5.1 + fx * 2.4) * 2.5 +
          Math.sin(time * 0.31 - fy * 3.7 + fx * 7.2) * 1.35
        const ambientY =
          Math.cos(time * 0.48 + fy * 3.8 + fx * 4.1) * 1.35 +
          Math.sin(time * 0.24 + fx * 5.6) * 0.75

        let forceX = (ambientX - dx[index]) * restoring
        let forceY = (ambientY - dy[index]) * restoring

        if (i > 0) {
          const left = index - pointCount
          forceX += (dx[left] - dx[index]) * coupling
          forceY += (dy[left] - dy[index]) * coupling
        }
        if (i < lineCount - 1) {
          const right = index + pointCount
          forceX += (dx[right] - dx[index]) * coupling
          forceY += (dy[right] - dy[index]) * coupling
        }
        if (j > 0) {
          const above = index - 1
          forceX += (dx[above] - dx[index]) * coupling * 0.72
          forceY += (dy[above] - dy[index]) * coupling * 0.72
        }
        if (j < pointCount - 1) {
          const below = index + 1
          forceX += (dx[below] - dx[index]) * coupling * 0.72
          forceY += (dy[below] - dy[index]) * coupling * 0.72
        }

        if (pointer.inside || pointer.energy > 0.015) {
          const px = baseX + dx[index] - pointer.x
          const py = baseY + dy[index] - pointer.y
          const distance = hypot2(px, py)
          if (distance < radius) {
            const influence = 1 - smoothstep(0.08, 1, distance * radiusInv)
            const invDistance = 1 / Math.max(18, distance)
            const nx = px * invDistance
            const ny = py * invDistance
            const speedForce = 10 + pointer.energy * 38
            const directionalX = clamp(pointer.inputVx / 900, -1.2, 1.2)
            const directionalY = clamp(pointer.inputVy / 900, -1.2, 1.2)

            forceX += (nx * speedForce + directionalX * 26) * influence
            forceY += (ny * speedForce * 0.58 + directionalY * 15) * influence
          }
        }

        for (const ripple of this.ripples) {
          const rx = baseX - ripple.x
          const ry = baseY - ripple.y
          const distance = hypot2(rx, ry)
          const ring = ripple.age * 270
          const band = 1 - smoothstep(0, 78, Math.abs(distance - ring))
          if (band > 0) {
            const invDistance = 1 / Math.max(22, distance)
            const fade = Math.exp(-ripple.age * 2.25) * ripple.strength * band
            forceX += rx * invDistance * fade * 44
            forceY += ry * invDistance * fade * 31
          }
        }

        vx[index] = (vx[index] + forceX * dt) * damping
        vy[index] = (vy[index] + forceY * dt) * damping
        dx[index] += vx[index] * dt
        dy[index] += vy[index] * dt
      }
    }

    for (let i = this.ripples.length - 1; i >= 0; i -= 1) {
      this.ripples[i].age += dt
      if (this.ripples[i].age > 1.55) this.ripples.splice(i, 1)
    }

    this.updateCssPointer()
  }

  private updateCssPointer() {
    const x = `${clamp((this.pointer.x / Math.max(1, this.width)) * 100, 0, 100).toFixed(2)}%`
    const y = `${clamp((this.pointer.y / Math.max(1, this.height)) * 100, 0, 100).toFixed(2)}%`
    this.host.style.setProperty('--wave-x', x)
    this.host.style.setProperty('--wave-y', y)
    this.host.style.setProperty('--wave-energy', this.pointer.energy.toFixed(3))
  }

  private refreshPalette() {
    if (!this.paletteDirty) return
    this.paletteDirty = false
    const value = getComputedStyle(this.host).getPropertyValue('--hero-wave-rgb').trim()
    if (/^\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}$/.test(value)) this.strokeRgb = value
  }

  private render(time: number, staticFrame: boolean) {
    this.refreshPalette()
    const ctx = this.ctx
    ctx.clearRect(0, 0, this.width, this.height)

    const { lineCount, pointCount, softUnderstroke } = this.quality
    const { dx, dy } = this.mesh
    const gradient = ctx.createLinearGradient(0, 0, 0, this.height)
    gradient.addColorStop(0, `rgba(${this.strokeRgb},0)`)
    gradient.addColorStop(0.1, `rgba(${this.strokeRgb},0.16)`)
    gradient.addColorStop(0.42, `rgba(${this.strokeRgb},0.24)`)
    gradient.addColorStop(0.78, `rgba(${this.strokeRgb},0.16)`)
    gradient.addColorStop(1, `rgba(${this.strokeRgb},0)`)

    const underGradient = ctx.createLinearGradient(0, 0, 0, this.height)
    underGradient.addColorStop(0, `rgba(${this.strokeRgb},0)`)
    underGradient.addColorStop(0.2, `rgba(${this.strokeRgb},0.055)`)
    underGradient.addColorStop(0.74, `rgba(${this.strokeRgb},0.045)`)
    underGradient.addColorStop(1, `rgba(${this.strokeRgb},0)`)

    ctx.lineCap = 'round'
    ctx.lineJoin = 'round'

    for (let i = 0; i < lineCount; i += 1) {
      const fx = i / Math.max(1, lineCount - 1)
      const depth = 0.72 + Math.sin(fx * Math.PI) * 0.28
      const baseX = fx * this.width

      for (let j = 0; j < pointCount; j += 1) {
        const fy = j / Math.max(1, pointCount - 1)
        const index = i * pointCount + j
        const stillX = staticFrame
          ? Math.sin(fy * 5.1 + fx * 2.4) * 2.2 + Math.sin(-fy * 3.7 + fx * 7.2) * 1.1
          : dx[index]
        const stillY = staticFrame ? Math.cos(fy * 3.8 + fx * 4.1) * 1.1 : dy[index]
        const perspective = Math.sin(fy * Math.PI) * Math.sin(fx * Math.PI * 2 + time * 0.08) * 0.7
        this.lineBuffer[j * 2] = baseX + stillX + perspective
        this.lineBuffer[j * 2 + 1] = fy * this.height + stillY
      }

      if (softUnderstroke) {
        ctx.globalAlpha = 0.82 * depth
        ctx.strokeStyle = underGradient
        ctx.lineWidth = this.qualityKey === 'ultra' ? 2.1 : 1.8
        this.strokeBufferedLine()
      }

      ctx.globalAlpha = depth
      ctx.strokeStyle = gradient
      ctx.lineWidth = i % 12 === 0 ? 0.92 : 0.64
      this.strokeBufferedLine()
    }

    ctx.globalAlpha = 1
  }

  private strokeBufferedLine() {
    const count = this.quality.pointCount
    const points = this.lineBuffer
    const ctx = this.ctx
    if (count < 2) return

    ctx.beginPath()
    ctx.moveTo(points[0], points[1])

    for (let i = 1; i < count - 1; i += 1) {
      const x = points[i * 2]
      const y = points[i * 2 + 1]
      const nextX = points[(i + 1) * 2]
      const nextY = points[(i + 1) * 2 + 1]
      ctx.quadraticCurveTo(x, y, (x + nextX) * 0.5, (y + nextY) * 0.5)
    }

    const last = count - 1
    ctx.quadraticCurveTo(
      points[(last - 1) * 2],
      points[(last - 1) * 2 + 1],
      points[last * 2],
      points[last * 2 + 1],
    )
    ctx.stroke()
  }
}
