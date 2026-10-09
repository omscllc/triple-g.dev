---
name: create-sdc-component
description: Scaffold a native Drupal 11 Single Directory Component (SDC) adhering to strict BEM, fluid typography, and design tokens.
disable-model-invocation: true
---
Reference the project architecture defined in #file:ARCHITECTURE.md.
Generate a complete Single Directory Component (SDC) inside the custom theme under `components/{{component_name}}/`.

Requirements:
1. **Directory & File Structure:**
   - `components/{{component_name}}/{{component_name}}.component.yml`
   - `components/{{component_name}}/{{component_name}}.twig`
   - `components/{{component_name}}/{{component_name}}.css`

2. **Component Definition (`.component.yml`):**
   - Provide a valid SDC schema specifying all slots, props, and data types (e.g., string, boolean).
   - Reference component library assets if applicable.

3. **Template (`.twig`):**
   - Use strict semantic HTML elements (`<article>`, `<header>`, `<section>`, etc.).
   - Apply BEM class naming conventions (`.{{component_name}}`, `.{{component_name}}__element`, `.{{component_name}}--modifier`).
   - Escape outputs safely using core filters (`|t`, `|clean_class`). Avoid `|raw`.
   - Provide realistic, complex sample text rather than "Lorem Ipsum" to test layout balance.

4. **Styles (`.css`):**
   - Scope all rules strictly to BEM class selectors. Do not target bare tag names or write global overrides.
   - Apply fluid typography and spacing via `clamp()` and viewport units (`vw`).
   - Utilize project theme tokens from `.github/copilot-instructions.md`:
     - Background: `oklch(93% 0.015 84.57)`
     - Charcoal text: `oklch(31.25% 0.0154 264.32)`
     - Accents: Brass (`oklch(75% 0.0861 65.3)`), Copper (`oklch(63.43% 0.0747 48.84)`), Bronze (`oklch(53.12% 0.0786 64.05)`)
     - Fonts: `Inter` for headers/metadata; `Lora` for body copy.
   - Ensure edge gutters maintain a minimum of `5rem` horizontal padding without exceeding 25% of viewport width.