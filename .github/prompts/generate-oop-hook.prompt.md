---
name: generate-oop-hook
description: Implement Drupal 11 class-based hooks using the #[Hook] attribute instead of procedural hook files.
agent: drupal-backend
---

Reference the project architecture defined in #file:ARCHITECTURE.md.
Implement the hook `{{hook_name}}` using Drupal 11's class-based OOP hook system.

Requirements:
1. **File Location & Namespace:**
   - Path: `src/Hook/{{hook_class_name}}.php`
   - Namespace: `Drupal\{{module_or_theme_name}}\Hook`

2. **OOP Hook Declaration:**
   - Include `declare(strict_types=1);`.
   - Add the core hook attribute directly to the handling method:
     `#[Drupal\Core\Hook\Attribute\Hook('{{hook_name}}')]`
   - No procedural `.module` or `.theme` files are permitted.

3. **Dependency Injection & Execution:**
   - Use constructor dependency injection for all required services.
   - Avoid `\Drupal::service()` calls.
   - Log errors gracefully using an injected `LoggerChannelInterface`.
   - Wrap dynamic database or remote operations in defensive `try/catch` blocks.