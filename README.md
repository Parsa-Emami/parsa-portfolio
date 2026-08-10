# Parsa Emami — Premium Portfolio

A cinematic, interaction-led portfolio for **Parsa Emami** focused on software engineering, software architecture, security engineering, Red Team thinking, applied AI, and production systems.

## Experience design

The page is intentionally structured as a changing scroll journey rather than a stack of identical sections:

- Interactive canvas hero with pointer-reactive engineering field
- Fast branded loader and editorial title choreography
- Lenis smooth scrolling integrated with GSAP ScrollTrigger
- Pinned **READ → MODEL → BREAK → PROVE** operating-philosophy sequence
- Split-line editorial reveals and depth-based parallax
- Capability cards with magnetic/tilt micro-interactions
- Pinned enterprise-system card deck
- Horizontal open-source gallery on desktop with mobile stacked fallback
- Scroll-driven timeline, CTA choreography, section HUD, progress rail, and cursor feedback
- Automatic fixed-navigation contrast across dark, light, and accent scenes
- Motion-reduced and coarse-pointer fallbacks for accessibility and mobile stability

## Stack

- Astro 5
- GSAP 3 + ScrollTrigger + SplitText
- Lenis
- Sass
- TypeScript-flavoured client scripts

## Local development

```bash
npm install
npm run dev
```

Then open the URL printed by Astro.

## Production build

```bash
npm run build
npm run preview
```

The Astro config is currently prepared for GitHub Pages at:

```text
https://parsa-emami.github.io/parsa-portfolio/
```

If the deployment path changes, update `site` and `base` in `astro.config.mjs` and the canonical URL in `src/pages/index.astro`.

## Main content

Portfolio content is centralized in:

```text
src/data/portfolio.ts
```

That file contains profile information, capabilities, enterprise systems, open-source projects, experience, education, and research themes.

## Motion system

The main interaction orchestration lives in:

```text
src/components/MotionSystem.astro
```

Section-specific layouts remain inside their own Astro components so animation logic and content structure do not collapse into one monolithic page file.

## Accessibility and performance notes

- `prefers-reduced-motion` disables cinematic animation paths.
- Desktop-only pinning/tilt behavior falls back to stacked content on smaller screens.
- The hero canvas pauses when it is outside the viewport.
- ScrollTrigger ignores mobile address-bar resize churn to reduce stutter.
- Pointer-only effects are skipped for coarse/touch input.

## Attribution / license

This portfolio started from concepts/code adapted from Antoine Wodniack's AW 2025 Portfolio open-source release. The original attribution is preserved in the site footer and `LICENSE.md`. Review the license before any commercial use.
