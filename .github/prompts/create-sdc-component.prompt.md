---
name: create-sdc-component
description: Scaffold a native Drupal 11 Single Directory Component (SDC) adhering to strict BEM, fluid typography, and design tokens.
agent: drupal-frontend
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
     - Background: `#FDFBF7`
     - Charcoal text: `#2D3139`
     - Accents: Brass (`#D4A373`), Copper (`#B07D62`), Bronze (`#8C6239`)
     - Fonts: `Inter` for headers/metadata; `Lora` for body copy.
   - Ensure edge gutters maintain a minimum of `5rem` horizontal padding without exceeding 25% of viewport width.