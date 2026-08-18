type WavePointer = {
  x: number
  y: number
  tx: number
  ty: number
  vx: number
  vy: number
  inputVX: number
  inputVY: number
  rawX: number
  rawY: number
  lastInputAt: number
  speed: number
  energy: number
  inside: boolean
  touching: boolean
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

const expSmoothing = (speed: number, dt: number) => 1 - Math.exp(-speed * dt)

/**
 * A lightweight spring-mesh wave field for the portfolio hero.
 *
 * The visible lines are backed by a real displacement field. Pointer energy
 * enters the mesh locally, travels through neighbouring nodes and decays with
 * damping, so the response feels closer to a soft membrane / water surface
 * than a collection of lines simply following the cursor.
 *
 * The engine intentionally stays on Canvas 2D. It gives us dependable Safari
 * and iOS behaviour without WebGL context-loss edge cases, while the adaptive
 * mesh keeps the desktop effect rich and mobile rendering inexpensive.
 */
export class HeroWaveEngine {
  host: HTMLElement
  canvas: HTMLCanvasElement
  ctx: CanvasRenderingContext2D
  hero: HTMLElement
  focus: HTMLElement | null
  focusHalfWidth = 0
  focusHalfHeight = 0

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
    vx: 0,
    vy: 0,
    inputVX: 0,
    inputVY: 0,
    rawX: 0,
    rawY: 0,
    lastInputAt: 0,
    speed: 0,
    energy: 0,
    inside: false,
    touching: false,
  }

  ripples: Ripple[] = []
  palette: Palette = { primary: '#07080a', secondary: '#f3f0e9', accent: '#ff365d' }

  reduceMotion: MediaQueryList
  coarsePointer: MediaQueryList
  resizeObserver?: ResizeObserver
  intersectionObserver?: IntersectionObserver

  frameBudget = 1000 / 60
  lineCount = 58
  pointCount = 29
  startTime = performance.now()

  // Physical displacement mesh. Values are CSS pixels and pixels/second.
  displacementX = new Float32Array(0)
  displacementY = new Float32Array(0)
  velocityX = new Float32Array(0)
  velocityY = new Float32Array(0)
  accelerationX = new Float32Array(0)
  accelerationY = new Float32Array(0)
  pathX = new Float32Array(0)
  pathY = new Float32Array(0)

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
    this.focus = host.querySelector<HTMLElement>('.hero-wave__focus')
    this.reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)')
    this.coarsePointer = window.matchMedia('(pointer: coarse)')

    this.updatePalette()
    this.updateQuality()
    this.resize()
    this.bind()

    if (this.reduceMotion.matches) this.render(performance.now(), 1 / 60, true)
    else this.start()
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
    window.addEventListener('pointerup', this.onPointerUp, { passive: true })
    window.addEventListener('pointercancel', this.onPointerUp, { passive: true })
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
    if (this.reduceMotion.matches) this.render(performance.now(), 1 / 60, true)
  }

  onVisibilityChange = () => {
    this.visible = !document.hidden
    this.syncRunningState()
  }

  onMotionPreference = () => {
    if (this.reduceMotion.matches) {
      this.stop()
      this.resetMesh()
      this.render(performance.now(), 1 / 60, true)
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
    this.pointer.touching = false
  }

  onPointerUp = (event: PointerEvent) => {
    if (event.pointerType === 'touch' || event.pointerType === 'pen') {
      this.pointer.touching = false
      this.pointer.inside = false
    }
  }

  onPointerMove = (event: PointerEvent) => {
    // Touch pointermove is useful only while the finger/stylus is actually in
    // contact. This avoids fighting the page's natural inertial scrolling.
    if ((event.pointerType === 'touch' || event.pointerType === 'pen') && !this.pointer.touching) return
    this.capturePointer(event)
  }

  onPointerDown = (event: PointerEvent) => {
    const point = this.localPoint(event)
    if (!point) return

    this.pointer.touching = event.pointerType === 'touch' || event.pointerType === 'pen'
    this.capturePointer(event, true)

    const strength = event.pointerType === 'touch' ? 0.72 : event.pointerType === 'pen' ? 0.84 : 1
    this.ripples.push({ x: point.x, y: point.y, born: performance.now(), strength })
    if (this.ripples.length > 5) this.ripples.shift()
    this.pointer.energy = clamp(this.pointer.energy + 0.46 * strength, 0, 1)
    this.injectImpulse(point.x, point.y, 0, 0, 1650 * strength)
  }

  localPoint(event: PointerEvent) {
    const rect = this.hero.getBoundingClientRect()
    if (
      event.clientX < rect.left ||
      event.clientX > rect.right ||
      event.clientY < rect.top ||
      event.clientY > rect.bottom
    ) return null

    return { x: event.clientX - rect.left, y: event.clientY - rect.top }
  }

  capturePointer(event: PointerEvent, force = false) {
    const point = this.localPoint(event)
    this.pointer.inside = !!point
    if (!point) return

    const now = performance.now()
    const hadPreviousInput = this.pointer.lastInputAt > 0
    const inputDt = hadPreviousInput ? Math.max(8, now - this.pointer.lastInputAt) / 1000 : 1 / 60
    const rawVX = hadPreviousInput ? (point.x - this.pointer.rawX) / inputDt : 0
    const rawVY = hadPreviousInput ? (point.y - this.pointer.rawY) / inputDt : 0
    const rawSpeed = Math.hypot(rawVX, rawVY)

    this.pointer.tx = point.x
    this.pointer.ty = point.y
    this.pointer.rawX = point.x
    this.pointer.rawY = point.y
    this.pointer.lastInputAt = now

    // Input velocity is intentionally filtered before it reaches the mesh.
    // Mouse sensors often deliver tiny high-frequency position jitter; feeding
    // that into the physical system is what usually makes cursor waves twitchy.
    const inputBlend = force ? 0.58 : 0.3
    this.pointer.inputVX = lerp(this.pointer.inputVX, clamp(rawVX, -1900, 1900), inputBlend)
    this.pointer.inputVY = lerp(this.pointer.inputVY, clamp(rawVY, -1900, 1900), inputBlend)
    this.pointer.speed = lerp(this.pointer.speed, clamp(rawSpeed, 0, 1900), 0.22)

    const motionEnergy = clamp(rawSpeed / 1050, 0, 1)
    this.pointer.energy = clamp(this.pointer.energy + motionEnergy * 0.13 + (force ? 0.12 : 0), 0, 1)
  }

  updateQuality() {
    const coarse = this.coarsePointer.matches
    const cores = navigator.hardwareConcurrency || 4
    const constrained = cores <= 4
    const narrow = window.innerWidth < 720

    if (coarse) {
      // Keep rAF fluid on phones and save work by reducing geometry instead of
      // visibly stepping the animation down to ~30fps.
      this.frameBudget = 1000 / (constrained ? 50 : 60)
      this.lineCount = narrow ? (constrained ? 25 : 29) : (constrained ? 29 : 34)
      this.pointCount = narrow ? 21 : 23
    } else if (window.innerWidth < 980 || constrained) {
      this.frameBudget = 1000 / 60
      this.lineCount = constrained ? 42 : 48
      this.pointCount = 25
    } else {
      this.frameBudget = 1000 / 60
      this.lineCount = 64
      this.pointCount = 31
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
    const maxDpr = coarse ? 1.35 : 1.7
    this.dpr = Math.min(window.devicePixelRatio || 1, maxDpr)

    this.canvas.width = Math.max(1, Math.round(this.width * this.dpr))
    this.canvas.height = Math.max(1, Math.round(this.height * this.dpr))
    this.canvas.style.width = `${this.width}px`
    this.canvas.style.height = `${this.height}px`
    this.ctx.setTransform(this.dpr, 0, 0, this.dpr, 0, 0)

    if (this.focus) {
      this.focusHalfWidth = this.focus.offsetWidth * 0.5
      this.focusHalfHeight = this.focus.offsetHeight * 0.5
    }

    const initialX = this.width * 0.52
    const initialY = this.height * 0.44
    if (!this.pointer.lastInputAt) {
      this.pointer.x = this.pointer.tx = this.pointer.rawX = initialX
      this.pointer.y = this.pointer.ty = this.pointer.rawY = initialY
    } else {
      this.pointer.x = clamp(this.pointer.x, 0, this.width)
      this.pointer.y = clamp(this.pointer.y, 0, this.height)
      this.pointer.tx = clamp(this.pointer.tx, 0, this.width)
      this.pointer.ty = clamp(this.pointer.ty, 0, this.height)
    }

    this.allocateMesh()
    if (this.reduceMotion.matches) this.render(performance.now(), 1 / 60, true)
  }

  allocateMesh() {
    const size = this.lineCount * this.pointCount
    this.displacementX = new Float32Array(size)
    this.displacementY = new Float32Array(size)
    this.velocityX = new Float32Array(size)
    this.velocityY = new Float32Array(size)
    this.accelerationX = new Float32Array(size)
    this.accelerationY = new Float32Array(size)
    this.pathX = new Float32Array(this.pointCount)
    this.pathY = new Float32Array(this.pointCount)
  }

  resetMesh() {
    this.displacementX.fill(0)
    this.displacementY.fill(0)
    this.velocityX.fill(0)
    this.velocityY.fill(0)
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

    const elapsed = this.lastFrame ? now - this.lastFrame : this.frameBudget
    this.lastFrame = now
    const dt = clamp(elapsed / 1000, 1 / 120, 1 / 30)
    this.render(now, dt, false)
  }

  render(now: number, dt: number, staticFrame: boolean) {
    const ctx = this.ctx
    const time = staticFrame ? 0.8 : (now - this.startTime) * 0.001

    if (!staticFrame) {
      this.updatePointer(time, dt)
      this.simulateMesh(now, time, dt)
    }

    const px = this.pointer.x
    const py = this.pointer.y
    if (this.focus) {
      // Transform-only cursor halo: the gradient is rasterised once and moved
      // by the compositor instead of repainting a full-screen radial gradient.
      const fx = px - this.focusHalfWidth
      const fy = py - this.focusHalfHeight
      this.focus.style.transform = `translate3d(${fx.toFixed(2)}px, ${fy.toFixed(2)}px, 0)`
      this.focus.style.opacity = (0.54 + this.pointer.energy * 0.16).toFixed(3)
    }

    ctx.clearRect(0, 0, this.width, this.height)
    this.drawMesh(time, staticFrame)
    this.drawRipples(now)
  }

  updatePointer(time: number, dt: number) {
    if (!this.pointer.inside && !this.pointer.touching) {
      // Slow autonomous breathing keeps the hero alive when idle. The target
      // itself moves slowly; the spring below handles all interpolation.
      const driftX = this.width * (0.5 + Math.sin(time * 0.29) * 0.067 + Math.sin(time * 0.11) * 0.018)
      const driftY = this.height * (0.45 + Math.cos(time * 0.24) * 0.052)
      this.pointer.tx = lerp(this.pointer.tx, driftX, expSmoothing(0.55, dt))
      this.pointer.ty = lerp(this.pointer.ty, driftY, expSmoothing(0.55, dt))
    }

    // Critically damped-ish spring follower. Unlike a fixed lerp, this keeps
    // motion time-correct across 50/60/120Hz displays and gives the cursor a
    // natural, soft amount of inertia instead of a robotic delay.
    const coarse = this.coarsePointer.matches
    const spring = coarse ? 39 : 46
    const damping = coarse ? 12.8 : 13.6
    const ax = (this.pointer.tx - this.pointer.x) * spring - this.pointer.vx * damping
    const ay = (this.pointer.ty - this.pointer.y) * spring - this.pointer.vy * damping

    this.pointer.vx += ax * dt
    this.pointer.vy += ay * dt
    this.pointer.x += this.pointer.vx * dt
    this.pointer.y += this.pointer.vy * dt

    // Velocity reported by raw input can briefly be much higher than the
    // spring follower. Blend toward physical velocity so fast flicks still
    // produce energy without making the visual focus jump.
    const physicalSpeed = Math.hypot(this.pointer.vx, this.pointer.vy)
    const inputSpeed = Math.hypot(this.pointer.inputVX, this.pointer.inputVY)
    this.pointer.speed = lerp(this.pointer.speed, Math.max(physicalSpeed, inputSpeed * 0.72), expSmoothing(5.4, dt))
    const inputDecay = Math.exp(-4.4 * dt)
    this.pointer.inputVX *= inputDecay
    this.pointer.inputVY *= inputDecay
    this.pointer.energy *= Math.exp(-1.55 * dt)
  }

  simulateMesh(now: number, time: number, dt: number) {
    const cols = this.lineCount
    const rows = this.pointCount
    const total = cols * rows
    if (!total) return

    const overscan = Math.max(70, this.width * 0.05)
    const left = -overscan
    const usableWidth = this.width + overscan * 2
    const colGap = usableWidth / Math.max(1, cols - 1)
    const rowGap = this.height / Math.max(1, rows - 1)

    const px = this.pointer.x
    const py = this.pointer.y
    const speed01 = clamp(this.pointer.speed / 1200, 0, 1)
    const radius = clamp(Math.min(this.width, this.height) * (0.25 + speed01 * 0.075), 150, 320)
    const pointerForce = 1850 + this.pointer.energy * 2350 + speed01 * 900
    const dragForce = 0.52 + speed01 * 0.38

    const coupling = 62
    const restoring = 11.8
    const damping = 5.5
    const maxOffset = Math.min(92, this.width * 0.095)

    // Calculate all accelerations from the same previous state before any node
    // is integrated. This is what makes propagation stable and symmetrical.
    for (let col = 0; col < cols; col++) {
      for (let row = 0; row < rows; row++) {
        const index = col * rows + row
        const dx0 = this.displacementX[index]
        const dy0 = this.displacementY[index]

        let neighborX = 0
        let neighborY = 0
        let neighbors = 0

        if (col > 0) {
          const i = (col - 1) * rows + row
          neighborX += this.displacementX[i]
          neighborY += this.displacementY[i]
          neighbors++
        }
        if (col < cols - 1) {
          const i = (col + 1) * rows + row
          neighborX += this.displacementX[i]
          neighborY += this.displacementY[i]
          neighbors++
        }
        if (row > 0) {
          const i = col * rows + row - 1
          neighborX += this.displacementX[i]
          neighborY += this.displacementY[i]
          neighbors++
        }
        if (row < rows - 1) {
          const i = col * rows + row + 1
          neighborX += this.displacementX[i]
          neighborY += this.displacementY[i]
          neighbors++
        }

        const lapX = neighbors ? neighborX / neighbors - dx0 : 0
        const lapY = neighbors ? neighborY / neighbors - dy0 : 0

        const baseX = left + col * colGap
        const baseY = row * rowGap
        const worldX = baseX + dx0
        const worldY = baseY + dy0
        const toX = worldX - px
        const toY = worldY - py
        const distance = Math.max(0.001, Math.hypot(toX, toY))
        const influence = smoothstep(1 - distance / radius)
        const softInfluence = influence * influence
        const nx = toX / distance
        const ny = toY / distance

        // Directional drag creates the beautiful trailing fold behind the
        // cursor; radial pressure prevents it from looking like a rigid magnet.
        const dragVX = this.pointer.vx * 0.48 + this.pointer.inputVX * 0.52
        const dragVY = this.pointer.vy * 0.48 + this.pointer.inputVY * 0.52
        const directionalX = dragVX * dragForce * influence
        const directionalY = dragVY * dragForce * influence * 0.42
        const radialX = nx * pointerForce * softInfluence
        const radialY = ny * pointerForce * softInfluence * 0.32

        // Tiny coherent breathing force. It is intentionally far below cursor
        // force and exists only to stop idle lines from appearing computer-flat.
        const edgeFadeY = Math.sin((row / Math.max(1, rows - 1)) * Math.PI)
        const ambient = Math.sin(time * 0.74 + row * 0.41 + col * 0.19) * 4.8 * edgeFadeY

        this.accelerationX[index] =
          lapX * coupling -
          dx0 * restoring -
          this.velocityX[index] * damping +
          radialX +
          directionalX +
          ambient

        this.accelerationY[index] =
          lapY * coupling -
          dy0 * (restoring * 1.15) -
          this.velocityY[index] * (damping * 1.05) +
          radialY +
          directionalY
      }
    }

    // Click/tap rings feed a soft travelling impulse into the same mesh, so
    // the visible ring and the line deformation agree spatially.
    for (const ripple of this.ripples) {
      const age = (now - ripple.born) / 1000
      const life = 1 - age / 1.3
      if (life <= 0) continue
      const ringRadius = age * 315

      for (let col = 0; col < cols; col++) {
        for (let row = 0; row < rows; row++) {
          const index = col * rows + row
          const baseX = left + col * colGap
          const baseY = row * rowGap
          const rx = baseX - ripple.x
          const ry = baseY - ripple.y
          const distance = Math.max(0.001, Math.hypot(rx, ry))
          const band = Math.exp(-Math.pow((distance - ringRadius) / 56, 2))
          const impulse = band * life * 950 * ripple.strength
          this.accelerationX[index] += (rx / distance) * impulse
          this.accelerationY[index] += (ry / distance) * impulse * 0.34
        }
      }
    }

    for (let index = 0; index < total; index++) {
      this.velocityX[index] += this.accelerationX[index] * dt
      this.velocityY[index] += this.accelerationY[index] * dt

      // Very small integration drag deals with long-tab-resume and unusual
      // frame pacing without visibly overdamping normal motion.
      const integrationDrag = Math.exp(-0.55 * dt)
      this.velocityX[index] *= integrationDrag
      this.velocityY[index] *= integrationDrag

      this.displacementX[index] = clamp(this.displacementX[index] + this.velocityX[index] * dt, -maxOffset, maxOffset)
      this.displacementY[index] = clamp(this.displacementY[index] + this.velocityY[index] * dt, -maxOffset * 0.46, maxOffset * 0.46)
    }
  }

  injectImpulse(x: number, y: number, dirX: number, dirY: number, strength: number) {
    const cols = this.lineCount
    const rows = this.pointCount
    const overscan = Math.max(70, this.width * 0.05)
    const left = -overscan
    const usableWidth = this.width + overscan * 2
    const colGap = usableWidth / Math.max(1, cols - 1)
    const rowGap = this.height / Math.max(1, rows - 1)
    const radius = clamp(Math.min(this.width, this.height) * 0.16, 90, 180)

    for (let col = 0; col < cols; col++) {
      for (let row = 0; row < rows; row++) {
        const index = col * rows + row
        const baseX = left + col * colGap
        const baseY = row * rowGap
        const dx = baseX - x
        const dy = baseY - y
        const distance = Math.max(0.001, Math.hypot(dx, dy))
        const influence = smoothstep(1 - distance / radius)
        if (influence <= 0) continue

        const nx = dirX || dx / distance
        const ny = dirY || dy / distance
        this.velocityX[index] += nx * influence * strength * 0.11
        this.velocityY[index] += ny * influence * strength * 0.035
      }
    }
  }

  drawMesh(time: number, staticFrame: boolean) {
    const ctx = this.ctx
    const cols = this.lineCount
    const rows = this.pointCount
    const overscan = Math.max(70, this.width * 0.05)
    const left = -overscan
    const usableWidth = this.width + overscan * 2
    const colGap = usableWidth / Math.max(1, cols - 1)
    const rowGap = this.height / Math.max(1, rows - 1)
    const px = this.pointer.x
    const radius = clamp(Math.min(this.width, this.height) * 0.28, 160, 340)
    const drawSoftUnderstroke = !this.coarsePointer.matches && this.width > 720

    for (let col = 0; col < cols; col++) {
      const baseX = left + col * colGap
      let localMotion = 0

      for (let row = 0; row < rows; row++) {
        const index = col * rows + row
        const normalizedY = row / Math.max(1, rows - 1)
        const edgeFade = Math.sin(normalizedY * Math.PI)

        // Rendering-only micro undulation sits on top of the physical mesh.
        // Keeping it out of the simulation means it never accumulates energy.
        const phase = col * 0.19
        const ambientX = staticFrame
          ? Math.sin(normalizedY * 8.6 + phase + 0.7) * 5.5 * edgeFade
          : (
              Math.sin(normalizedY * 8.8 + time * 0.52 + phase) * 5.2 +
              Math.sin(normalizedY * 4.1 - time * 0.29 + phase * 1.7) * 2.4
            ) * edgeFade
        const ambientY = staticFrame ? 0 : Math.sin(normalizedY * 5.5 + time * 0.31 + phase) * 1.3 * edgeFade

        const dx = this.displacementX[index]
        const dy = this.displacementY[index]
        localMotion += Math.abs(dx) + Math.abs(dy) * 1.7
        this.pathX[row] = baseX + ambientX + dx
        this.pathY[row] = row * rowGap + ambientY + dy
      }

      const nearPointer = smoothstep(1 - Math.abs(baseX - px) / radius)
      const motion01 = clamp(localMotion / Math.max(1, rows * 26), 0, 1)
      const accentLine = col % 11 === 0

      // Desktop gets a very soft depth pass. Mobile skips it entirely; this
      // halves line stroke work there while preserving the actual wave physics.
      if (drawSoftUnderstroke) {
        ctx.save()
        ctx.lineCap = 'round'
        ctx.lineJoin = 'round'
        ctx.strokeStyle = accentLine ? this.palette.accent : this.palette.secondary
        ctx.lineWidth = accentLine ? 1.9 : 1.45
        ctx.globalAlpha = accentLine
          ? 0.022 + nearPointer * 0.045 + motion01 * 0.018
          : 0.018 + nearPointer * 0.032 + motion01 * 0.012
        this.strokeFluidPath(this.pathX, this.pathY, rows)
        ctx.restore()
      }

      // Crisp hairline pass. No per-frame point objects are allocated; the
      // Float32Array path buffers are reused for every line and frame.
      ctx.save()
      ctx.lineCap = 'round'
      ctx.lineJoin = 'round'
      ctx.strokeStyle = accentLine ? this.palette.accent : this.palette.secondary
      ctx.lineWidth = accentLine ? 0.82 + nearPointer * 0.18 : 0.56 + nearPointer * 0.26
      ctx.globalAlpha = accentLine
        ? 0.095 + nearPointer * 0.115 + motion01 * 0.045
        : 0.092 + nearPointer * 0.085 + motion01 * 0.035
      this.strokeFluidPath(this.pathX, this.pathY, rows)
      ctx.restore()
    }
  }

  strokeFluidPath(x: Float32Array, y: Float32Array, count: number) {
    if (count < 2) return
    const ctx = this.ctx
    ctx.beginPath()
    ctx.moveTo(x[0], y[0])

    // Catmull-Rom -> cubic Bézier conversion. It removes the tiny angular
    // changes that quadratic midpoint paths reveal on large Retina displays.
    for (let i = 0; i < count - 1; i++) {
      const i0 = Math.max(0, i - 1)
      const i1 = i
      const i2 = i + 1
      const i3 = Math.min(count - 1, i + 2)
      const tension = 0.17

      const cp1x = x[i1] + (x[i2] - x[i0]) * tension
      const cp1y = y[i1] + (y[i2] - y[i0]) * tension
      const cp2x = x[i2] - (x[i3] - x[i1]) * tension
      const cp2y = y[i2] - (y[i3] - y[i1]) * tension
      ctx.bezierCurveTo(cp1x, cp1y, cp2x, cp2y, x[i2], y[i2])
    }

    ctx.stroke()
  }

  drawRipples(now: number) {
    const ctx = this.ctx
    this.ripples = this.ripples.filter((ripple) => now - ripple.born < 1300)

    for (const ripple of this.ripples) {
      const age = (now - ripple.born) / 1000
      const life = clamp(1 - age / 1.3, 0, 1)
      const radius = 10 + age * 315

      ctx.save()
      ctx.strokeStyle = this.palette.accent
      ctx.globalAlpha = life * 0.12
      ctx.lineWidth = 0.75 + life * 0.5
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
    window.removeEventListener('pointerup', this.onPointerUp)
    window.removeEventListener('pointercancel', this.onPointerUp)
    window.removeEventListener('blur', this.onPointerLeave)
    window.removeEventListener('portfolio-theme-change', this.onThemeChange)
    document.removeEventListener('visibilitychange', this.onVisibilityChange)
    if (this.reduceMotion.removeEventListener) this.reduceMotion.removeEventListener('change', this.onMotionPreference)
    else this.reduceMotion.removeListener?.(this.onMotionPreference)
    if (this.coarsePointer.removeEventListener) this.coarsePointer.removeEventListener('change', this.onQualityPreference)
    else this.coarsePointer.removeListener?.(this.onQualityPreference)
  }
}
