import { clamp } from './hero-wave/math'
import Noise from './Noise'

type WavePoint = {
  x: number
  y: number
  waveX: number
  waveY: number
  cursorX: number
  cursorY: number
  cursorVX: number
  cursorVY: number
}

type Density = {
  xGap: number
  yGap: number
}

const DENSITY_DESKTOP: Density = { xGap: 10, yGap: 30 }
const DENSITY_COMPACT: Density = { xGap: 17, yGap: 34 }

/**
 * SVG line-mesh wave field, pointer- and touch-reactive.
 *
 * A lighter sibling of `HeroWaveEngine`: instead of a canvas spring-mesh it
 * draws a grid of `<path>` lines whose vertices are displaced by seeded
 * Perlin noise (ambient motion) plus a velocity-weighted pointer field
 * (interaction), matching the original AW wave technique. It sleeps
 * offscreen, respects reduced-motion/coarse-pointer, and rebuilds its grid
 * on resize without ever recreating the host SVG element.
 */
export class SignalWaveEngine {
  private readonly host: HTMLElement
  private readonly svg: SVGSVGElement

  private readonly noise = new Noise(Math.random())
  private lines: WavePoint[][] = []
  private paths: SVGPathElement[] = []

  private bounding = { left: 0, top: 0, width: 1, height: 1 }
  private density: Density = DENSITY_DESKTOP

  private readonly mouse = {
    x: -100,
    y: -100,
    lx: 0,
    ly: 0,
    sx: 0,
    sy: 0,
    v: 0,
    vs: 0,
    a: 0,
    set: false,
  }

  private raf = 0
  private resizeTimer = 0
  private destroyed = false
  private visible = true

  private readonly reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  private readonly coarsePointer = window.matchMedia('(pointer: coarse)').matches

  private intersectionObserver?: IntersectionObserver

  private readonly onPointerMove = (event: PointerEvent) => this.updateMouse(event.clientX, event.clientY)
  private readonly onTouchMove = (event: TouchEvent) => {
    const touch = event.touches[0]
    if (touch) this.updateMouse(touch.clientX, touch.clientY)
  }
  private readonly onResize = () => {
    window.clearTimeout(this.resizeTimer)
    this.resizeTimer = window.setTimeout(() => this.rebuild(), 120)
  }
  private readonly tick = (time: number) => {
    if (this.destroyed) return
    this.raf = requestAnimationFrame(this.tick)
    if (!this.visible) return
    this.step(time)
  }

  constructor(host: HTMLElement) {
    this.host = host
    const svg = host.querySelector<SVGSVGElement>('.js-signal-svg')
    if (!svg) throw new Error('SignalWaveEngine: expected an svg.js-signal-svg child')
    this.svg = svg

    this.density = this.coarsePointer || window.innerWidth < 760 ? DENSITY_COMPACT : DENSITY_DESKTOP

    this.measure()
    this.svg.setAttribute('viewBox', `0 0 ${this.bounding.width} ${this.bounding.height}`)
    this.build()
    // A fresh grid has no displacement yet — settle it into one organic
    // frame so the field reads as an intentional pattern even when the
    // animation loop never starts (reduced motion) or before first paint.
    this.movePoints(0)
    this.draw()

    if ('IntersectionObserver' in window) {
      this.intersectionObserver = new IntersectionObserver(
        (entries) => {
          const entry = entries[0]
          if (!entry) return
          this.visible = entry.isIntersecting
        },
        { threshold: 0.01 },
      )
      this.intersectionObserver.observe(host)
    }

    window.addEventListener('resize', this.onResize, { passive: true })

    if (!this.reducedMotion) {
      window.addEventListener('pointermove', this.onPointerMove, { passive: true })
      host.addEventListener('touchmove', this.onTouchMove, { passive: true })
      this.raf = requestAnimationFrame(this.tick)
    }
  }

  private measure() {
    const rect = this.host.getBoundingClientRect()
    this.bounding = { left: rect.left, top: rect.top, width: Math.max(1, rect.width), height: Math.max(1, rect.height) }
  }

  private updateMouse(clientX: number, clientY: number) {
    // Re-measured on every move rather than cached: `bounding` is
    // viewport-relative, and this section scrolls under the pointer, so a
    // value cached at mount (or only refreshed on resize) would silently
    // drift out of sync with the cursor the moment the page scrolls.
    this.measure()
    const { mouse, bounding } = this
    mouse.x = clientX - bounding.left
    mouse.y = clientY - bounding.top
    if (!mouse.set) {
      mouse.sx = mouse.x
      mouse.sy = mouse.y
      mouse.lx = mouse.x
      mouse.ly = mouse.y
      mouse.set = true
    }
  }

  private build() {
    const { width, height } = this.bounding
    const { xGap, yGap } = this.density

    this.paths.forEach((path) => path.remove())
    this.paths = []
    this.lines = []

    const overscanW = width + 200
    const overscanH = height + 40
    const totalLines = Math.ceil(overscanW / xGap)
    const totalPoints = Math.ceil(overscanH / yGap)
    const xStart = (width - xGap * totalLines) / 2
    const yStart = (height - yGap * totalPoints) / 2

    const fragment = document.createDocumentFragment()

    for (let i = 0; i <= totalLines; i++) {
      const points: WavePoint[] = []
      for (let j = 0; j <= totalPoints; j++) {
        points.push({
          x: xStart + xGap * i,
          y: yStart + yGap * j,
          waveX: 0,
          waveY: 0,
          cursorX: 0,
          cursorY: 0,
          cursorVX: 0,
          cursorVY: 0,
        })
      }
      const path = document.createElementNS('http://www.w3.org/2000/svg', 'path')
      path.setAttribute('class', 'signal__line')
      fragment.appendChild(path)
      this.paths.push(path)
      this.lines.push(points)
    }

    this.svg.appendChild(fragment)
  }

  private rebuild() {
    if (this.destroyed) return
    this.measure()
    this.density = this.coarsePointer || window.innerWidth < 760 ? DENSITY_COMPACT : DENSITY_DESKTOP
    this.svg.setAttribute('viewBox', `0 0 ${this.bounding.width} ${this.bounding.height}`)
    this.build()
    if (this.reducedMotion) {
      this.movePoints(0)
      this.draw()
    }
  }

  private step(time: number) {
    const { mouse } = this
    mouse.sx += (mouse.x - mouse.sx) * 0.1
    mouse.sy += (mouse.y - mouse.sy) * 0.1

    const dx = mouse.x - mouse.lx
    const dy = mouse.y - mouse.ly
    const dist = Math.hypot(dx, dy)

    mouse.v = dist
    mouse.vs += (dist - mouse.vs) * 0.1
    mouse.vs = Math.min(100, mouse.vs)
    mouse.lx = mouse.x
    mouse.ly = mouse.y
    mouse.a = Math.atan2(dy, dx)

    this.host.style.setProperty('--signal-x', `${mouse.sx}px`)
    this.host.style.setProperty('--signal-y', `${mouse.sy}px`)

    this.movePoints(time)
    this.draw()
  }

  private movePoints(time: number) {
    const { mouse, noise } = this
    for (const points of this.lines) {
      for (const p of points) {
        const move = noise.perlin2((p.x + time * 0.0125) * 0.002, (p.y + time * 0.005) * 0.0015) * 12
        p.waveX = Math.cos(move) * 32
        p.waveY = Math.sin(move) * 16

        if (mouse.set) {
          const dx = p.x - mouse.sx
          const dy = p.y - mouse.sy
          const distance = Math.hypot(dx, dy)
          const radius = Math.max(175, mouse.vs)

          if (distance < radius) {
            const strength = 1 - distance / radius
            const force = Math.cos(distance * 0.001) * strength
            p.cursorVX += Math.cos(mouse.a) * force * radius * mouse.vs * 0.00065
            p.cursorVY += Math.sin(mouse.a) * force * radius * mouse.vs * 0.00065
          }
        }

        p.cursorVX += (0 - p.cursorX) * 0.005
        p.cursorVY += (0 - p.cursorY) * 0.005
        p.cursorVX *= 0.925
        p.cursorVY *= 0.925
        p.cursorX += p.cursorVX * 2
        p.cursorY += p.cursorVY * 2
        p.cursorX = clamp(p.cursorX, -100, 100)
        p.cursorY = clamp(p.cursorY, -100, 100)
      }
    }
  }

  private draw() {
    this.lines.forEach((points, index) => {
      const first = this.moved(points[0], false)
      let d = `M ${first.x} ${first.y}`
      points.forEach((point, pointIndex) => {
        const isLast = pointIndex === points.length - 1
        const current = this.moved(point, !isLast)
        d += ` L ${current.x} ${current.y}`
      })
      this.paths[index]?.setAttribute('d', d)
    })
  }

  private moved(point: WavePoint, withCursor: boolean) {
    const x = point.x + point.waveX + (withCursor ? point.cursorX : 0)
    const y = point.y + point.waveY + (withCursor ? point.cursorY : 0)
    return { x: Math.round(x * 10) / 10, y: Math.round(y * 10) / 10 }
  }

  destroy() {
    this.destroyed = true
    cancelAnimationFrame(this.raf)
    window.clearTimeout(this.resizeTimer)
    window.removeEventListener('resize', this.onResize)
    window.removeEventListener('pointermove', this.onPointerMove)
    this.host.removeEventListener('touchmove', this.onTouchMove as EventListener)
    this.intersectionObserver?.disconnect()
    this.paths.forEach((path) => path.remove())
    this.paths = []
    this.lines = []
  }
}
