export const clamp = (value: number, min: number, max: number) =>
  Math.min(max, Math.max(min, value))

export const lerp = (from: number, to: number, amount: number) => from + (to - from) * amount

/**
 * Frame-rate independent exponential damping.
 * `lambda` is the response speed in 1/seconds.
 */
export const damp = (from: number, to: number, lambda: number, dt: number) =>
  lerp(from, to, 1 - Math.exp(-lambda * dt))

export const smoothstep = (edge0: number, edge1: number, value: number) => {
  if (edge0 === edge1) return value < edge0 ? 0 : 1
  const t = clamp((value - edge0) / (edge1 - edge0), 0, 1)
  return t * t * (3 - 2 * t)
}

export const hypot2 = (x: number, y: number) => Math.sqrt(x * x + y * y)
