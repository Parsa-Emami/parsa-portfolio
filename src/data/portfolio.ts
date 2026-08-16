export const profile = {
  name: 'Parsa Emami',
  headline: 'Software Engineer · Security Architect · Red Team',
  location: 'Babol, Mazandaran, Iran',
  company: 'Sepehr Afrooz Saba',
  university: 'Mazandaran University of Science and Technology',
  summary:
    'I build and improve software systems where engineering rules, security boundaries, maintainability, and operational reliability all matter at the same time.',
  linkedin: 'https://www.linkedin.com/in/parsaaemm/',
  github: 'https://github.com/Parsa-Emami',
}

export const capabilities = [
  {
    index: '01',
    title: 'Software Architecture',
    description:
      'Modular monoliths, domain boundaries, refactoring legacy Laravel systems, data-flow design, API contracts, deployment structure, and maintainable source-of-truth decisions.',
  },
  {
    index: '02',
    title: 'Security Engineering',
    description:
      'Authentication, authorization, rate limiting, secure operational tooling, tenant isolation, attack-surface reduction, logging, integrity checks, and secure-by-design reviews.',
  },
  {
    index: '03',
    title: 'Red Team & Adversary Thinking',
    description:
      'Threat modeling, offensive security mindset, adversary emulation, social-engineering risk, cloud authorization risk, and translating attacker behavior into defensive architecture.',
  },
  {
    index: '04',
    title: 'Applied AI & Algorithms',
    description:
      'AI-focused graduate study combined with algorithm design, data analysis, engineering rules, optimization, and practical exploration of AI-assisted security workflows.',
  },
  {
    index: '05',
    title: 'Testing & Reliability',
    description:
      'Characterization testing, regression protection, correctness checks, deterministic behavior, release validation, observability, and keeping outputs consistent across multiple representations.',
  },
  {
    index: '06',
    title: 'Laravel / PHP Systems',
    description:
      'Production-oriented Laravel applications, Blade, queues, Redis, relational data models, admin tooling, exports, background processing, CI/CD, Docker, and progressive modernization.',
  },
]

export const systems = [
  {
    number: '01',
    name: 'InfinityDS',
    type: 'Internal engineering platform',
    tagline: 'Configuration, calculation, visualization, pricing, and export tooling for modular lighting design.',
    description:
      'A Laravel-based engineering application that turns design inputs into structured Infinity DS configurations and tracked results. The system combines domain rules with result persistence, per-line reconciliation, visualization, pricing, and engineering exports.',
    highlights: [
      'Centralized geometry and connector rules for open and closed paths, including L-connector behavior.',
      'Result consistency work that reconciles per-line quantities with the final engineering total.',
      'DXF, Excel, PDF, and result-preview workflows for engineering and commercial use.',
      'Security-oriented operational features including throttling, upload inspection, activity logging, and protected Developer Mode tooling.',
    ],
    stack: ['Laravel 8', 'PHP', 'Blade', 'Tailwind CSS', 'Alpine.js', 'Redis', 'Oracle', 'DXF', 'Excel'],
    access: 'Private / enterprise codebase',
  },
  {
    number: '02',
    name: 'Saba CMS',
    type: 'Multi-tenant content platform',
    tagline: 'A modular CMS evolving toward a Blade-first, tenant-safe architecture.',
    description:
      'A mature Laravel CMS with tenant-aware content, themes, media, permissions, queues, search, caching, and modular business areas. Current architecture work moves presentation toward BladePage as the canonical source of truth while keeping domain data in existing business models.',
    highlights: [
      'Blade-first page architecture with a shared runtime for preview and public rendering.',
      'Tenant isolation and explicit tenant context across web, API, queue, and console flows.',
      'Security and permission hardening with policies, honeypot protection, audit logging, and controlled rendering boundaries.',
      'Performance, response cache, Horizon/Redis queues, quality gates, E2E validation, and release packaging discipline.',
    ],
    stack: ['Laravel 8', 'PHP', 'Blade', 'Vue 2', 'Redis', 'Horizon', 'Scout', 'Spatie', 'Docker'],
    access: 'Private / enterprise codebase',
  },
  {
    number: '03',
    name: 'HOMA',
    type: 'Engineering rule engine',
    tagline: 'Lighting design calculations, panel selection, cable sizing, zone layout, and engineering output.',
    description:
      'A Laravel engineering application used to select HOMA lighting configurations, calculate required lights and power supplies, select panels, produce order results, export spreadsheets, and generate zone-layout alternatives.',
    highlights: [
      'Reverse-engineered active business rules across controllers, views, exports, seed data, and legacy logic to establish the real execution path.',
      'Mapped calculation, cable sizing, panel selection, and column/row zone-layout conditions into an explicit rule catalogue.',
      'Identified source-of-truth drift between controller logic, services, graph output, and Excel output.',
      'Defined a target modular-monolith architecture with typed DTOs, domain services, versioned rules, a dedicated layout planner, and characterization tests.',
    ],
    stack: ['Laravel 12', 'PHP 8.2', 'Blade', 'Eloquent', 'Excel', 'Docker', 'GitLab CI'],
    access: 'Private / enterprise codebase',
  },
]

export const openSourceProjects = [
  {
    number: '01',
    name: 'Demian',
    subtitle: 'Deterministic 2D game platform',
    description:
      'A Laravel-backed game platform with a deterministic fixed-update GameRuntime, Canvas2D nearest-neighbor renderer, data-driven shared café, open-world chunks, role-play systems, events, Hide & Seek, and Tetris.',
    highlights: [
      '12-chunk open world with districts, maps, save points, dialogue, quests, inventory, and jobs.',
      'Renderer-independent collision, interaction, and navigation architecture.',
      'Atomic JavaScript deployment strategy and build validation for GitHub Pages reliability.',
    ],
    stack: ['Laravel', 'JavaScript', 'Canvas2D', 'Vite', 'GitHub Pages'],
    href: 'https://github.com/Parsa-Emami/Demian',
  },
  {
    number: '02',
    name: 'Hanna Music Player',
    subtitle: 'Laravel music application',
    description:
      'A modern Laravel 13 music application structured around albums, artists, playlists, songs, and a home experience, with a Vite/Tailwind front end.',
    highlights: [
      'Separated Album, Artist, Playlist, Song, and Home controller responsibilities.',
      'Laravel 13 / PHP 8.3 foundation with Vite 7 and Tailwind CSS 4 tooling.',
      'A clean base for extending catalog, playback, discovery, and collection features.',
    ],
    stack: ['Laravel 13', 'PHP 8.3', 'Vite 7', 'Tailwind CSS 4'],
    href: 'https://github.com/Parsa-Emami/Hanna-Music-Player',
  },
  {
    number: '03',
    name: 'Negar Week Planner',
    subtitle: 'Small focused Laravel utility',
    description:
      'A single-page Laravel tool for recording weekly availability, persisting the response, preparing a copyable result, and exposing stored results through an Artisan command.',
    highlights: [
      'Purpose-built one-screen workflow with persistence rather than a generic CRUD shell.',
      'Owner view plus command-line result inspection through a custom Artisan command.',
      'Simple deployment footprint with SQLite support for lightweight local use.',
    ],
    stack: ['Laravel', 'PHP', 'SQLite', 'Artisan'],
    href: 'https://github.com/Parsa-Emami/negar-week-planner',
  },
]

export const experience = [
  {
    period: 'Sep 2025 — Present',
    role: 'Software Engineer',
    organization: 'Sepehr Afrooz Saba',
    mode: 'Full-time · Hybrid',
    description:
      'Software design and web engineering across production-oriented business systems, with a strong focus on architecture, correctness, security, and maintainable delivery.',
  },
  {
    period: 'Mar 2024 — Jul 2025',
    role: 'Cyber Security Architect',
    organization: 'Freelance',
    mode: 'Remote',
    description:
      'Security architecture, cyber-risk analysis, secure-system thinking, and practical defensive design informed by offensive security experience.',
  },
  {
    period: 'Sep 2021 — Mar 2025',
    role: 'Red Team',
    organization: 'Freelance',
    mode: 'Hybrid',
    description:
      'Red-team practice and adversary-oriented security work spanning offensive techniques, threat thinking, and attack-path analysis.',
  },
  {
    period: 'Dec 2023 — Jan 2025',
    role: 'Software Test Engineer',
    organization: 'Freelance',
    mode: 'Remote',
    description:
      'Testing and quality work with an emphasis on correctness, edge cases, secure behavior, and dependable software outcomes.',
  },
  {
    period: 'Jan 2022 — Jul 2025',
    role: 'Teaching / Graduate Teaching Assistant',
    organization: 'Mazandaran University of Science and Technology',
    mode: 'Part-time · On-site',
    description:
      'Teaching support across Analysis & Design of Algorithms, Engineering Mathematics, Digital Systems, Signals & Systems, Computer Simulation, MIS, Mathematics, and Project Management.',
  },
]

export const education = [
  {
    period: '2025 — 2027',
    degree: 'Master of Engineering · Artificial Intelligence',
    school: 'Mazandaran University of Science and Technology',
  },
  {
    period: '2021 — 2025',
    degree: "Bachelor's Degree · Computer Engineering",
    school: 'Mazandaran University of Science and Technology',
  },
]

export const researchThemes = [
  'AI-powered adversary emulation',
  'Red teaming agentic AI systems',
  'Authorization sprawl in cloud environments',
  'Deepfakes in social engineering',
  'Cyber threat intelligence',
  'Secure system architecture',
]
