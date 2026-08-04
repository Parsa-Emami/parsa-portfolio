# Parsa Emami — Portfolio

A Laravel-powered portfolio and content-management system with a static export pipeline for GitHub Pages.

## Live site

After GitHub Pages is enabled and the deployment workflow completes:

**https://parsa-emami.github.io/parsa-portfolio/**

## Architecture

The repository supports two delivery modes:

- **Laravel application:** full CMS, authentication, project media management, contact inbox, email queue, backups and operational tooling.
- **GitHub Pages edition:** a secure static export generated from the same Blade views, Vite assets and committed content snapshot.

GitHub Pages does not execute PHP, so the Actions workflow creates a temporary SQLite database, imports the public content snapshot, renders every public route and deploys only HTML/CSS/JavaScript/media artifacts.

## Local setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
composer run dev
```

## Content workflow for GitHub Pages

The public Pages build never commits users, passwords, contact messages, activity logs or sessions.

After editing content through the local admin panel, create a public snapshot:

```bash
php artisan portfolio:content-export --seed-if-empty
```

This updates:

```text
content/portfolio.json
content/media/
```

Commit and push those files. GitHub Actions imports the snapshot and publishes the exact public content.

## Static export preview

```bash
npm run build
php artisan portfolio:export-static \
  --output=storage/app/static-export \
  --base-url=https://parsa-emami.github.io/parsa-portfolio
```

Generated output:

```text
storage/app/static-export/
```

## Deployment workflow

The Pages workflow is located at:

```text
.github/workflows/deploy-pages.yml
```

It performs:

1. PHP and Node setup
2. Composer and NPM installation
3. frontend production build
4. automated test suite
5. fresh SQLite migration and seed
6. public content snapshot import
7. static route export
8. artifact validation
9. GitHub Pages deployment

See [`GITHUB-PAGES-SETUP.md`](GITHUB-PAGES-SETUP.md) for the one-time repository configuration.

## Main technologies

Laravel 13, Livewire 4, Blade, Tailwind CSS 4, Vite 7, GSAP, Lenis, SQLite/MySQL and GitHub Actions.

## Security

The static content snapshot intentionally excludes private application records. Never commit `.env`, database credentials, administrator passwords or production database files.
