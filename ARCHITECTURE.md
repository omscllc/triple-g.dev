# Geek's Gadgets and Gizmos Architecture Map

This document serves as the ground-truth directory, technical specification, and architectural registry for GitHub Copilot agents.

## Codebase Directory & Ownership Matrix
- **Custom Modules:** `web/modules/custom/` (Owned by `@drupal-backend`)
  - Active Block Module: `triple_g_footer` (`Drupal\triple_g_footer\`)
  - Active Pager Module: `triple_g_pager` (`Drupal\triple_g_pager\`)
  - Future plugins, entities, and backend services live here in dedicated modules.
- **Custom Theme:** `web/themes/custom/triple_g/` (Owned by `@drupal-frontend`)
  - Single Directory Components: `web/themes/custom/triple_g/components/`
  - Theme SCSS / Tokens: `web/themes/custom/triple_g/src/scss/`
  - Golden Reference Component: `web/themes/custom/triple_g/components/blog-post/`
- **Config Sync Directory:** `config/sync/` (Managed by `@drupal-backend`, deployed by `@drupal-devops`)
- **Container & CI Tooling:** `docker/`, `docker-compose.yml`, `.github/` (Owned by `@drupal-devops`)
- **Server Web Tier Configurations:** `docker/web/nginx.conf.template`, `docker/web/php-fpm-socket.conf`, `docker/web/00-php.ini`, VPS configs (Owned by `@linux-admin`)
- **Local macOS Host Runtime:** Homebrew services, `~/Library/LaunchAgents/`, `~/.colima/`, Docker contexts, `/etc/fstab`, and external-volume mounts (Owned by `@mac-admin`)

## Graphic Assets Registry
- **Site Logo:** `web/assets/triple-g-logo.png`
- **File Not Found (404):** `web/assets/triple-g-404.png`
- **Access Denied (403):** `web/assets/triple-g-403.png`
- **Server Error (500):** `web/assets/triple-g-500.png`
- **Under Construction:** `web/assets/triple-g-underconstruction.png`

## Core Drupal Data Model & Configuration Specifications
1. **Content Type ('Blog Post'):**
   - **Fields:** Title, Authored On (use Drupal core built-in `created` timestamp, not an additional field), and Body (Formatted text, long, with summary).
2. **Content Type ('Basic Page'):**
   - **Fields:** Title, Body (simple text-driven informational sheets).
3. **Views Configuration:**
   - **Homepage Feed:** Responsive View displaying `Blog Post` items sorted by "Authored on (descending)".
   - **Display Format:** Unformatted list of fields outputting strictly: Authored On date, Title (clickable link), and Body (trimmed summary or excerpt).

## Backend Architectural Standards
- **Plugin Declarations:** Block plugins must use core PHP attribute discovery (`#[Drupal\Core\Block\Attribute\Block]`) under `src/Plugin/Block/` inside their respective module.
- **Hook Registrations:** Class-based OOP hooks located under `src/Hook/` using `#[Drupal\Core\Hook\Attribute\Hook]`.
- **Routing & Controllers:** Declared via Symfony Route Attributes (`#[Symfony\Component\Routing\Attribute\Route]`).
- **Entity Bundle Classes:** Declared via `#[Drupal\Core\Entity\Attribute\Bundle]` directly on bundle classes.
- **Strict Typing & Runtime:**
  - All custom PHP classes must declare `declare(strict_types=1);`.
  - Minimum PHP version: **8.4**.
  - Minimum Drupal version: **11.3.0**.

## Frontend & Component Directory
- **Minimal Footprint:** Avoid heavy visual layout builders, utility-first layout frameworks, or full multi-column subtheme systems. Use only minimal, theme-native CSS Grid/Flex layouts when required for a page shell or content region; do not introduce framework-like layout abstractions or broad layout conventions that undermine the custom theme.
- **Single Directory Components (SDC):** Every UI unit lives in its own subdirectory under `web/themes/custom/triple_g/components/[component-name]/`.
- **Component File Triad:**
  - Definition: `[component-name].component.yml` (strictly `.yml`, `.json` is prohibited).
  - Markup: `[component-name].twig`
  - Scoped Styling: `[component-name].css`
- **Design Tokens:** Defined in `web/themes/custom/triple_g/src/scss/theme-tokens.scss`:
  - **Surfaces & Ink:** `--triple-g-color-paper` (`oklch(93% 0.015 84.57)`), `--triple-g-color-ink` (`oklch(31.25% 0.0154 264.32)`)
  - **Metallics:** `--triple-g-color-brass` (`oklch(75% 0.0861 65.3)`), `--triple-g-color-copper` (`oklch(57% 0.0747 48.84)`), `--triple-g-color-bronze` (`oklch(49% 0.0786 64.05)`)
  - **Typography:** `--triple-g-font-ui` (`Inter` for headings/UI), `--triple-g-font-reading` (`Lora` for body copy), `--triple-g-font-display` (`Steamwreck` italic for site name; the asset file is `Steamwreck-Italic.ttf`).
  - **Layout Spacing:** `--triple-g-gutter` (fluid `clamp()`, minimum 5rem gutter, not exceeding 25% of viewport width).
  - **Strict Rule:** Components must exclusively reference these custom properties via `var(--triple-g-*)`.
- **Dynamic Interactions:** Driven by HTMX attributes (`hx-*`) and vanilla JS (`@drupal/once`, native ES6 `fetch()`).

## Infrastructure & Runtime Stack
- **Production Stack:** Dedicated Linux VPS, Nginx (microcaching enabled), MariaDB, Valkey.
- **Local Dev Stack:** Alpine Linux Docker container running as container name `tripleg-web`.
- **Cache Backend:** Valkey via UNIX domain socket for render, page, and bootstrap bins.
- **Environment Injections:** DotEnv (`.env`) for secrets, database credentials, and cache endpoints.
