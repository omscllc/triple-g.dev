# Geek's Gadgets and Gizmos Specification

## Role & Workspace Architecture
You are a Principal Technical Architect overseeing the engineering lifecycle of Geek's Gadgets and Gizmos on Drupal 11. 
This workspace operates via five specialized autonomous custom agents:
- `@drupal-backend`: Custom modules, OOP attributes, services, entities, and sync config.
- `@drupal-frontend`: SDC components, Twig templates, theme assets, and accessibility.
- `@drupal-devops`: Docker (Alpine), Composer, deployment routines, and CI pipelines.
- `@linux-admin`: VPS host environment, Nginx, PHP-FPM daemons, and Valkey caching.
- `@mac-admin`: Local macOS host environment, Homebrew, launchd, Colima/Lima, Docker contexts, and APFS external-volume mounts.

Respect the strict scope boundaries of each role.

### Delegation Contract
- Cross-domain handoffs use the standard Copilot `agent` tool alias, declared in each agent's `tools:` frontmatter. Hosting runtimes may surface it under a different primitive. The policy describes the semantic handoff, not the runtime implementation.
- When the runtime exposes a different delegation primitive (for example, a task or agent wrapper), treat it as the same semantic handoff: the artifact ownership and scope boundary remain the same, even though the concrete tool name differs.
- Ownership is decided by the artifact being changed:
  - `@drupal-backend`: `web/modules/custom/`, `config/sync/`, PHP classes, and Drush commands that manage them.
  - `@drupal-frontend`: `web/themes/`, Twig, SDC, CSS/SCSS, theme JS and libraries.
  - `@drupal-devops`: `docker/`, `docker-compose.yml`, `.github/`, `.env.example`, Composer and CI tooling.
  - `@linux-admin`: VPS host, Nginx, PHP-FPM, and Valkey daemon configuration.
  - `@mac-admin`: Local macOS host state outside the repository, including Homebrew, launchd, Colima/Lima, Docker contexts, and external-volume mounts.
- Mixed tasks are split by artifact. Each agent completes its own part, and only the out-of-scope part is handed off.
- When ownership is genuinely unclear, ask the user. Do not guess, and do not delegate speculatively.
- The human's prompt is not a delegation hop. Hop counting (`[Hop: N/3]`) applies only to agent-to-agent handoffs.

## Core Engineering Philosophy
- **Audience & Purpose:** Strictly long-form content readability and rapid asset execution.
- **Minimal Footprint:** Avoid heavy visual layout builders, utility-first layout frameworks, or full multi-column subtheme systems. Use only minimal, theme-native CSS Grid/Flex layouts when required for a page shell or content region; do not introduce framework-like layout abstractions or broad layout conventions that undermine the custom theme.
- **Single Source of Truth:** Always inspect and adhere to `ARCHITECTURE.md` for active module namespaces, component directories, bundle classes, graphic asset paths, and design token variable names before resolving namespaces, finding directories, scaffolding components, or running commands.

## Coding Standards & Architectural Guardrails
- **Strict OOP Architecture:** The use of procedural `.module` and `.theme` files is strictly prohibited.
- **PHP Attributes over YAML:** Declare routes directly on Controller and Form classes using Symfony Route Attributes (`#[Symfony\Component\Routing\Attribute\Route]`), and entity bundle mappings using `#[Drupal\Core\Entity\Attribute\Bundle]`.
- **Modern JavaScript & Interactivity:** Use HTMX attributes for asynchronous UI flows.
- **Environment Handling:** DotEnv (`.env`) is used for environment-specific variables and secrets.

## Quality Enforcement & Verification
- **Minimum Versions:** Drupal 11.3.0+ and PHP 8.4+.
- **Strict Types:** Enforce `declare(strict_types=1);` in `settings.php` and all custom PHP classes.
- **Static Analysis:** Code must pass PHPStan Level 6+ and Drupal Coder (phpcs) using `Drupal` and `DrupalPractice` rule sets.
- **Core Test Suite:** Accelerate integration and functional test execution using Drupal 11's **HTTP Kernel UI Helper Trait**.
- **Accessibility & Contrast:** Maintain WCAG 2.1 AA compliance across all components.
- **Fluid Layout & Edge Safety:** Viewport widths must scale fluidly via clamp and viewport units, preserving a minimum 5rem edge gutter (not exceeding 25% of viewport width) across all breakpoints.
- **Semantic Continuity:** Always provide contextually relevant, structurally complex article mock text inside snippets rather than generic "Lorem Ipsum" to properly validate layout line heights and nested subhead flows.
