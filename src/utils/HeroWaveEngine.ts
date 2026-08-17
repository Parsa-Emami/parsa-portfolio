type WavePointer = {
  x: number
  y: number
  tx: number
  ty: number
  lastX: number
  lastY: number
  speed: number
  energy: number
  inside: boolean
}

type Ripple = {
  x: number
  y: number
  born: number
  strength: number
}

type Palette = {
  primary: string
  secondary: string
  accent: string
}

const clamp = (value: number, min: number, max: number) => Math.min(max, Math.max(min, value))
const lerp = (from: number, to: number, amount: number) => from + (to - from) * amount
const smoothstep = (value: number) => {
  const t = clamp(value, 0, 1)
  return t * t * (3 - 2 * t)
}

/**
 * Adaptive 2D wave field for the portfolio hero.
 *
 * Design goals:
 * - No WebGL dependency, so the effect remains dependable on Safari/iOS and
 *   browsers with conservative GPU settings.
 * - Pointer velocity changes both wave radius and force, giving fast gestures
 *   a different character from slow hovering.
 * - Work is suspended when the hero is off screen / tab is hidden.
 * - Density, DPR and frame budget scale down on coarse-pointer devices.
 */
export class HeroWaveEngine {
  host: HTMLElement
  canvas: HTMLCanvasElement
  ctx: CanvasRenderingContext2D
  hero: HTMLElement

  width = 1
  height = 1
  dpr = 1
  raf = 0
  lastFrame = 0
  running = false
  intersecting = true
  visible = true
  destroyed = false

  pointer: WavePointer = {
    x: 0,
    y: 0,
    tx: 0,
    ty: 0,
    lastX: 0,
    lastY: 0,
    speed: 0,
    energy: 0,
    inside: false,
  }

  ripples: Ripple[] = []
  palette: Palette = { primary: '#07080a', secondary: '#f3f0e9', accent: '#ff365d' }

  reduceMotion: MediaQueryList
  coarsePointer: MediaQueryList
  resizeObserver?: ResizeObserver
  intersectionObserver?: IntersectionObserver
  frameBudget = 1000 / 60
  lineCount = 48
  pointCount = 25
  startTime = performance.now()

  constructor(host: HTMLElement) {
    const canvas = host.querySelector('canvas')
    const hero = host.closest('.hero')
    if (!(canvas instanceof HTMLCanvasElement) || !(hero instanceof HTMLElement)) {
      throw new Error('HeroWaveEngine requires a canvas inside .hero')
    }

    const ctx = canvas.getContext('2d', { alpha: true })
    if (!ctx) throw new Error('2D canvas is unavailable')

    this.host = host
    this.canvas = canvas
    this.hero = hero
    this.ctx = ctx
    this.reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)')
    this.coarsePointer = window.matchMedia('(pointer: coarse)')

    this.updatePalette()
    this.updateQuality()
    this.resize()
    this.bind()

    if (this.reduceMotion.matches) {
      this.render(performance.now(), true)
    } else {
      this.start()
    }
  }

  bind() {
    const ResizeObserverCtor = (window as unknown as { ResizeObserver?: typeof ResizeObserver }).ResizeObserver
    if (ResizeObserverCtor) {
      this.resizeObserver = new ResizeObserverCtor(() => this.resize())
      this.resizeObserver.observe(this.hero)
    }
    window.addEventListener('resize', this.onResize, { passive: true })

    if ('IntersectionObserver' in window) {
      this.intersectionObserver = new IntersectionObserver(
        ([entry]) => {
          this.intersecting = !!entry?.isIntersecting
          this.syncRunningState()
        },
        { threshold: 0.01 }
      )
      this.intersectionObserver.observe(this.hero)
    }

    window.addEventListener('pointermove', this.onPointerMove, { passive: true })
    window.addEventListener('pointerdown', this.onPointerDown, { passive: true })
    window.addEventListener('blur', this.onPointerLeave)
    document.addEventListener('visibilitychange', this.onVisibilityChange, { passive: true })
    window.addEventListener('portfolio-theme-change', this.onThemeChange)

    if (this.reduceMotion.addEventListener) this.reduceMotion.addEventListener('change', this.onMotionPreference)
    else this.reduceMotion.addListener?.(this.onMotionPreference)
    if (this.coarsePointer.addEventListener) this.coarsePointer.addEventListener('change', this.onQualityPreference)
    else this.coarsePointer.addListener?.(this.onQualityPreference)
  }

  onResize = () => this.resize()

  onThemeChange = () => {
    this.updatePalette()
    if (this.reduceMotion.matches) this.render(performance.now(), true)
  }

  onVisibilityChange = () => {
    this.visible = !document.hidden
    this.syncRunningState()
  }

  onMotionPreference = () => {
    if (this.reduceMotion.matches) {
      this.stop()
      this.render(performance.now(), true)
    } else {
      this.start()
    }
  }

  onQualityPreference = () => {
    this.updateQuality()
    this.resize()
  }

  onPointerLeave = () => {
    this.pointer.inside = false
    this.pointer.tx = this.width * 0.5
    this.pointer.ty = this.height * 0.46
  }

  onPointerMove = (event: PointerEvent) => {
    const rect = this.hero.getBoundingClientRect()
    const inside =
      event.clientX >= rect.left &&
      event.clientX <= rect.right &&
      event.clientY >= rect.top &&
      event.clientY <= rect.bottom

    this.pointer.inside = inside
    if (!inside) return

    const x = event.clientX - rect.left
    const y = event.clientY - rect.top
    const travel = Math.hypot(x - this.pointer.lastX, y - this.pointer.lastY)

    this.pointer.tx = x
    this.pointer.ty = y
    this.pointer.speed = lerp(this.pointer.speed, clamp(travel * 5.5, 0, 220), 0.3)
    this.pointer.lastX = x
    this.pointer.lastY = y
    this.pointer.energy = clamp(this.pointer.energy + Math.min(0.45, travel / 220), 0, 1)
  }

  onPointerDown = (event: PointerEvent) => {
    const rect = this.hero.getBoundingClientRect()
    if (
      event.clientX < rect.left ||
      event.clientX > rect.right ||
      event.clientY < rect.top ||
      event.clientY > rect.bottom
    ) return

    const x = event.clientX - rect.left
    const y = event.clientY - rect.top
    this.ripples.push({ x, y, born: performance.now(), strength: event.pointerType === 'touch' ? 0.75 : 1 })
    this.pointer.energy = 1
    this.pointer.tx = x
    this.pointer.ty = y
  }

  updateQuality() {
    const coarse = this.coarsePointer.matches
    const cores = navigator.hardwareConcurrency || 4
    const constrained = cores <= 4

    if (coarse) {
      this.frameBudget = 1000 / 32
      this.lineCount = constrained ? 24 : 30
      this.pointCount = 19
    } else if (window.innerWidth < 980 || constrained) {
      this.frameBudget = 1000 / 48
      this.lineCount = 40
      this.pointCount = 23
    } else {
      this.frameBudget = 1000 / 60
      this.lineCount = 58
      this.pointCount = 27
    }
  }

  updatePalette() {
    const style = getComputedStyle(document.documentElement)
    this.palette = {
      primary: style.getPropertyValue('--color-primary').trim() || '#07080a',
      secondary: style.getPropertyValue('--color-secondary').trim() || '#f3f0e9',
      accent: style.getPropertyValue('--color-accent').trim() || '#ff365d',
    }
  }

  resize() {
    this.updateQuality()
    const rect = this.hero.getBoundingClientRect()
    this.width = Math.max(1, rect.width)
    this.height = Math.max(1, rect.height)

    const coarse = this.coarsePointer.matches
    const maxDpr = coarse ? 1.2 : 1.55
    this.dpr = Math.min(window.devicePixelRatio || 1, maxDpr)

    this.canvas.width = Math.max(1, Math.round(this.width * this.dpr))
    this.canvas.height = Math.max(1, Math.round(this.height * this.dpr))
    this.canvas.style.width = `${this.width}px`
    this.canvas.style.height = `${this.height}px`
    this.ctx.setTransform(this.dpr, 0, 0, this.dpr, 0, 0)

    if (!this.pointer.x && !this.pointer.y) {
      this.pointer.x = this.pointer.tx = this.width * 0.52
      this.pointer.y = this.pointer.ty = this.height * 0.44
      this.pointer.lastX = this.pointer.x
      this.pointer.lastY = this.pointer.y
    }

    if (this.reduceMotion.matches) this.render(performance.now(), true)
  }

  start() {
    if (this.destroyed || this.running || !this.visible || !this.intersecting || this.reduceMotion.matches) return
    this.running = true
    this.lastFrame = 0
    this.raf = requestAnimationFrame(this.tick)
  }

  stop() {
    this.running = false
    cancelAnimationFrame(this.raf)
  }

  syncRunningState() {
    if (this.visible && this.intersecting && !this.reduceMotion.matches) this.start()
    else this.stop()
  }

  tick = (now: number) => {
    if (!this.running || this.destroyed) return
    this.raf = requestAnimationFrame(this.tick)
    if (now - this.lastFrame < this.frameBudget) return
    this.lastFrame = now
    this.render(now, false)
  }

  render(now: number, staticFrame: boolean) {
    const ctx = this.ctx
    const time = staticFrame ? 0.8 : (now - this.startTime) * 0.001

    if (!staticFrame) {
      const follow = this.coarsePointer.matches ? 0.09 : 0.075
      this.pointer.x = lerp(this.pointer.x, this.pointer.tx, follow)
      this.pointer.y = lerp(this.pointer.y, this.pointer.ty, follow)
      this.pointer.speed *= 0.9
      this.pointer.energy *= 0.945

      if (!this.pointer.inside) {
        const driftX = this.width * (0.5 + Math.sin(time * 0.32) * 0.075)
        const driftY = this.height * (0.45 + Math.cos(time * 0.26) * 0.06)
        this.pointer.tx = lerp(this.pointer.tx, driftX, 0.012)
        this.pointer.ty = lerp(this.pointer.ty, driftY, 0.012)
      }
    }

    const px = this.pointer.x
    const py = this.pointer.y
    const xPercent = clamp((px / this.width) * 100, 0, 100)
    const yPercent = clamp((py / this.height) * 100, 0, 100)
    this.host.style.setProperty('--wave-x', `${xPercent.toFixed(2)}%`)
    this.host.style.setProperty('--wave-y', `${yPercent.toFixed(2)}%`)
    this.host.style.setProperty('--wave-energy', (this.pointer.energy * 0.22).toFixed(3))

    ctx.clearRect(0, 0, this.width, this.height)

    // Accent atmosphere follows the pointer but stays subtle enough to keep
    // the large hero typography readable.
    const auraRadius = Math.max(180, Math.min(this.width, this.height) * 0.34)
    const aura = ctx.createRadialGradient(px, py, 0, px, py, auraRadius)
    aura.addColorStop(0, this.palette.accent)
    aura.addColorStop(1, 'transparent')
    ctx.save()
    ctx.globalAlpha = staticFrame ? 0.045 : 0.055 + this.pointer.energy * 0.035
    ctx.fillStyle = aura
    ctx.fillRect(0, 0, this.width, this.height)
    ctx.restore()

    const overscan = Math.max(80, this.width * 0.055)
    const left = -overscan
    const usableWidth = this.width + overscan * 2
    const lineGap = usableWidth / Math.max(1, this.lineCount - 1)
    const pointGap = this.height / Math.max(1, this.pointCount - 1)
    const maxRadius = Math.max(180, Math.min(430, this.width * 0.34))
    const radius = clamp(230 + this.pointer.speed * 0.75, 180, maxRadius)
    const force = 46 + this.pointer.energy * 46 + Math.min(34, this.pointer.speed * 0.12)

    for (let lineIndex = 0; lineIndex < this.lineCount; lineIndex++) {
      const baseX = left + lineIndex * lineGap
      const phase = lineIndex * 0.21
      const points: { x: number; y: number }[] = []

      for (let pointIndex = 0; pointIndex < this.pointCount; pointIndex++) {
        const baseY = pointIndex * pointGap
        const normalizedY = pointIndex / Math.max(1, this.pointCount - 1)

        const ambientX =
          Math.sin(normalizedY * 9.2 + time * 0.72 + phase) * (11 + Math.sin(phase * 0.7) * 3) +
          Math.cos(normalizedY * 4.4 - time * 0.44 + phase * 1.6) * 5
        const ambientY = Math.sin(normalizedY * 6.5 + time * 0.4 + phase) * 3.5

        const dx = baseX + ambientX - px
        const dy = baseY + ambientY - py
        const distance = Math.max(0.001, Math.hypot(dx, dy))
        const proximity = smoothstep(1 - distance / radius)
        const speedGain = 1 + Math.min(0.55, this.pointer.speed / 320)
        const normalX = dx / distance
        const normalY = dy / distance

        // A radial push plus a small rotational component produces the soft
        // "fabric folding around the cursor" feeling without chaotic motion.
        const radial = proximity * proximity * force * speedGain
        const swirl = proximity * (10 + this.pointer.energy * 18)
        const rippleForce = this.sampleRippleForce(baseX, baseY, now)

        const x = baseX + ambientX + normalX * radial + -normalY * swirl + normalX * rippleForce
        const y = baseY + ambientY + normalY * radial * 0.34 + normalX * swirl * 0.32 + normalY * rippleForce * 0.28
        points.push({ x, y })
      }

      const distanceFromPointer = Math.abs(baseX - px)
      const nearPointer = smoothstep(1 - distanceFromPointer / Math.max(160, radius * 0.9))
      const accentLine = lineIndex % 9 === 0

      ctx.save()
      ctx.lineCap = 'round'
      ctx.lineJoin = 'round'
      ctx.strokeStyle = accentLine ? this.palette.accent : this.palette.secondary
      ctx.lineWidth = accentLine ? 0.9 + nearPointer * 0.35 : 0.62 + nearPointer * 0.42
      ctx.globalAlpha = accentLine
        ? 0.11 + nearPointer * 0.18 + this.pointer.energy * 0.04
        : 0.105 + nearPointer * 0.12

      this.strokeSmoothPath(points)
      ctx.restore()
    }

    this.drawRipples(now)
  }

  sampleRippleForce(x: number, y: number, now: number) {
    let total = 0
    for (const ripple of this.ripples) {
      const age = (now - ripple.born) / 1000
      const life = 1 - age / 1.15
      if (life <= 0) continue
      const distance = Math.hypot(x - ripple.x, y - ripple.y)
      const ring = age * 330
      const band = Math.exp(-Math.pow((distance - ring) / 54, 2))
      total += band * life * 34 * ripple.strength
    }
    return total
  }

  strokeSmoothPath(points: { x: number; y: number }[]) {
    if (points.length < 2) return
    const ctx = this.ctx
    ctx.beginPath()
    ctx.moveTo(points[0].x, points[0].y)

    for (let i = 1; i < points.length - 1; i++) {
      const current = points[i]
      const next = points[i + 1]
      const midX = (current.x + next.x) * 0.5
      const midY = (current.y + next.y) * 0.5
      ctx.quadraticCurveTo(current.x, current.y, midX, midY)
    }

    const last = points[points.length - 1]
    ctx.lineTo(last.x, last.y)
    ctx.stroke()
  }

  drawRipples(now: number) {
    const ctx = this.ctx
    this.ripples = this.ripples.filter((ripple) => now - ripple.born < 1200)

    for (const ripple of this.ripples) {
      const age = (now - ripple.born) / 1000
      const life = clamp(1 - age / 1.2, 0, 1)
      const radius = 12 + age * 330
      ctx.save()
      ctx.strokeStyle = this.palette.accent
      ctx.globalAlpha = life * 0.28
      ctx.lineWidth = 1 + life * 0.8
      ctx.beginPath()
      ctx.arc(ripple.x, ripple.y, radius, 0, Math.PI * 2)
      ctx.stroke()
      ctx.restore()
    }
  }

  destroy() {
    this.destroyed = true
    this.stop()
    this.resizeObserver?.disconnect()
    this.intersectionObserver?.disconnect()
    window.removeEventListener('resize', this.onResize)
    window.removeEventListener('pointermove', this.onPointerMove)
    window.removeEventListener('pointerdown', this.onPointerDown)
    window.removeEventListener('blur', this.onPointerLeave)
    window.removeEventListener('portfolio-theme-change', this.onThemeChange)
    document.removeEventListener('visibilitychange', this.onVisibilityChange)
    if (this.reduceMotion.removeEventListener) this.reduceMotion.removeEventListener('change', this.onMotionPreference)
    else this.reduceMotion.removeListener?.(this.onMotionPreference)
    if (this.coarsePointer.removeEventListener) this.coarsePointer.removeEventListener('change', this.onQualityPreference)
    else this.coarsePointer.removeListener?.(this.onQualityPreference)
  }
}
