# Geek's Gadgets and Gizmos Architecture Map

This document serves as the ground-truth directory and architectural registry for GitHub Copilot agents.

## Core Namespaces & Locations
- **Custom Modules Directory:** `web/modules/custom/`
- **Active Block Module:** `triple_g_footer` (`Drupal\triple_g_footer\`)
- **Future Custom Code:** All custom modules, blocks, and backend plugins reside in dedicated modules under `web/modules/custom/`
- **Custom Theme Directory:** `web/themes/custom/triple_g/`
- **Theme Components:** `web/themes/custom/triple_g/components/`
- **Config Sync Directory:** `config/sync/`

## Backend Architecture Standards
- **Plugin Declarations:** Block plugins must use core PHP attribute discovery (`#[Drupal\Core\Block\Attribute\Block]`) under `src/Plugin/Block/` inside their respective module.
- **Hook Registrations:** Class-based OOP hooks located under `src/Hook/` using `#[Drupal\Core\Hook\Attribute\Hook]`. Procedural `.module` and `.theme` files are prohibited.
- **Routing & Controllers:** Declared via Symfony Route Attributes (`#[Symfony\Component\Routing\Attribute\Route]`). No custom YAML routing files.
- **Strict Typing:** All PHP classes must declare `declare(strict_types=1);` and target PHP 8.4+.

## Frontend & Component Directory
- **Single Directory Components (SDC):** Every UI unit lives in its own subdirectory under `web/themes/custom/triple_g/components/[component-name]/`.
- **Component File Triad:**
  - Definition: `[component-name].component.yml`
  - Markup: `[component-name].twig`
  - Scoped Styling: `[component-name].css`
- **Design Tokens:** 
  - Defined as CSS custom properties in `web/themes/custom/triple_g/src/scss/theme-tokens.scss`:
  - Surfaces & Ink: `--triple-g-color-paper`, `--triple-g-color-ink` (auto-toggled for light/dark modes)
  - Metallics: `--triple-g-color-brass`, `--triple-g-color-copper`, `--triple-g-color-bronze`
  - Typography: `--triple-g-font-ui` (Inter), `--triple-g-font-reading` (Lora), `--triple-g-font-display` (Steamwreck)
  - Layout Spacing: `--triple-g-gutter` (fluid clamp)
  - Strict Rule: Components must exclusively reference these custom properties via `var(--triple-g-*)`. Never write static color values or raw OKLCH/hex declarations inside component CSS.
- **Dynamic Interactions:** Driven by HTMX attributes (`hx-*`) and vanilla JS (`@drupal/once`, ES6 `fetch()`). Never use jQuery or `core/drupal.ajax`.

## Infrastructure & Runtime Stack
- **Production Stack:** Linux VPS, Nginx (microcaching enabled), MariaDB, Valkey.
- **Local Dev Stack:** Alpine Linux Docker container.
- **Cache Backend:** Valkey via UNIX socket for render, page, and bootstrap bins.
- **Environment Injections:** DotEnv (`.env`) for secrets, database credentials, and cache endpoints.