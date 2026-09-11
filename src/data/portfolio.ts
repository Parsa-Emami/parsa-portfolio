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
      'Refactored the Arak storefront theme into a self-contained Laravel module with isolated routes, config, views, and migrations, then traced a silent CI cache-invalidation bug back to an unquoted glob in the deploy script.',
    ],
    stack: ['Laravel 8', 'PHP', 'Blade', 'Vue 2', 'Redis', 'Horizon', 'Scout', 'Spatie', 'Docker'],
    access: 'Private / enterprise codebase',
  },
  {
    number: '03',
    name: 'Saba CMS NG',
    type: 'Next-generation content & commerce platform',
    tagline: 'A ground-up Laravel 13 rewrite unifying content management and e-commerce behind one Admin + API surface.',
    description:
      'A parallel-track rebuild of Saba CMS on Laravel 13, pairing a tenant-scoped content domain (posts, categories, attributes, comments, tags, sliders, media) with a full commerce domain (orders, payments, inventory, coupons, shipping, currency, wishlist) behind mirrored Admin and API controllers.',
    highlights: [
      'Company-scoped global query scope enforced at the Eloquent layer so every model resolves tenant boundaries automatically instead of relying on per-controller checks.',
      'Parallel Admin and API controller surfaces kept in sync across both the content and commerce domains from day one.',
      'Spatie media-library and permission packages layered onto a Sanctum-authenticated API for headless consumption.',
      'Built as an incremental successor track alongside the production Saba CMS codebase rather than a disruptive big-bang rewrite.',
    ],
    stack: ['Laravel 13', 'PHP 8.3', 'Sanctum', 'Spatie Media Library', 'Spatie Permission', 'MySQL'],
    access: 'Private / enterprise codebase',
  },
  {
    number: '04',
    name: 'Radiant',
    type: 'Internal sales & pricing console',
    tagline: 'A Persian RTL admin panel for step-based sales configuration, pricing, and Excel-driven reporting.',
    description:
      'A Laravel admin console used internally to manage step-based product configuration, pricing, and sales-header/line workflows, with Excel export for commercial reporting. Recent work delivered a full responsive and RTL-accessibility pass across the panel without touching backend logic.',
    highlights: [
      'Rebuilt table, modal, sidebar, and form-control behavior for reliable small-screen and touch use across the whole panel.',
      'Eliminated horizontal-overflow and layout-breaking issues across dashboard, list, and detail views.',
      'Shipped as an isolated UI/responsive patch release, kept deliberately separate from domain logic to keep the change low-risk.',
      'Excel-driven export pipeline for sales and pricing data via a dedicated spreadsheet package.',
    ],
    stack: ['Laravel 8', 'Blade', 'Tailwind CSS', 'Alpine.js', 'Redis', 'Excel'],
    access: 'Private / enterprise codebase',
  },
  {
    number: '05',
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
  {
    number: '06',
    name: 'TINV',
    type: 'Regulatory e-invoicing & tax compliance platform',
    tagline: 'Invoice lifecycle, digital signing, and ERP bridging for statutory tax-invoice compliance.',
    description:
      'A Laravel platform that manages the full invoice lifecycle — creation, line items, refunds, and organization/taxpayer records — with digital-signature handling and Excel-based statutory reporting. A dedicated ERP bridge synchronizes invoice data with an external IFS system, and SMS OTP secures sensitive user actions.',
    highlights: [
      'End-to-end invoice, invoice-line, and refund models paired with dedicated Excel export controllers for statutory reporting.',
      'Digital signature and organization/taxpayer record management aligned with regulatory e-invoicing requirements.',
      'A dedicated bridge service synchronizing invoice data with an external IFS ERP system.',
      'SMS-based OTP verification and structured API request logging for auditability.',
    ],
    stack: ['Laravel 8', 'PHP', 'MySQL', 'SOAP', 'Excel', 'REST API'],
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

// A single coherent security-platform suite, specified before any of it is
// built. Each repository is a frozen architecture: contracts, runtime
// boundaries, and an explicit build-order recommendation — deliberately no
// implementation yet. Shown as design work, not shipped software.
export const labProjects = [
  {
    order: '01',
    name: 'RiskWeft',
    purpose: 'Deterministic STRIDE-style architecture-risk scoring with a reproducible, hash-verified replay of every evaluation.',
    stack: ['Laravel', 'React', 'PostgreSQL'],
    href: 'https://github.com/Parsa-Emami/Riskweft',
  },
  {
    order: '02',
    name: 'CipherLint',
    purpose: 'A Rust-first static-analysis security linter with deterministic findings, stable IDs, and SARIF export.',
    stack: ['Rust', 'Tree-sitter', 'SARIF'],
    href: 'https://github.com/Parsa-Emami/Cipherlint',
  },
  {
    order: '03',
    name: 'FlowWeft',
    purpose: 'Network-flow anomaly detection on Zeek telemetry with a deterministic baseline before any ML is introduced.',
    stack: ['Python', 'Zeek', 'ClickHouse'],
    href: 'https://github.com/Parsa-Emami/Flowweft',
  },
  {
    order: '04',
    name: 'Privattice',
    purpose: 'A privilege/identity graph analyzer projecting inventory data into bounded access-path queries.',
    stack: ['Go', 'PostgreSQL', 'Apache AGE'],
    href: 'https://github.com/Parsa-Emami/Privattice',
  },
  {
    order: '05',
    name: 'RiftRange',
    purpose: 'On-demand cyber-range orchestration: immutable lab templates, agent heartbeats, deterministic teardown.',
    stack: ['Go', 'Podman', 'Laravel'],
    href: 'https://github.com/Parsa-Emami/Riftrange',
  },
  {
    order: '06',
    name: 'PolicyHelix',
    purpose: 'A fail-closed OIDC + OPA + Envoy authorization gateway that rejects stale policy bundles by design.',
    stack: ['Go', 'OPA', 'Envoy'],
    href: 'https://github.com/Parsa-Emami/Policyhelix',
  },
  {
    order: '07',
    name: 'Attestara',
    purpose: 'Sandboxed supply-chain attestation: SBOM generation and policy-gated verification inside disposable runners.',
    stack: ['Go', 'Firecracker', 'OPA'],
    href: 'https://github.com/Parsa-Emami/Attestara',
  },
  {
    order: '08',
    name: 'MirageMesh',
    purpose: 'A deception mesh of signed decoy sensors that spool telemetry offline and replay it once reconnected.',
    stack: ['Go', 'Podman', 'OpenBao PKI'],
    href: 'https://github.com/Parsa-Emami/Miragemesh',
  },
  {
    order: '09',
    name: 'KeyHelix',
    purpose: 'Envelope-encryption key management with step-up reveal, KEK rewrap, and a signed, tamper-evident audit trail.',
    stack: ['PHP/libsodium', 'OpenBao', 'Laravel'],
    href: 'https://github.com/Parsa-Emami/Keyhelix',
  },
  {
    order: '10',
    name: 'BreachAtlas',
    purpose: 'Digital-forensics case management with cryptographic evidence sealing and full chain-of-custody tracking.',
    stack: ['Laravel', 'React', 'OpenSearch'],
    href: 'https://github.com/Parsa-Emami/Breachatlas',
  },
]
