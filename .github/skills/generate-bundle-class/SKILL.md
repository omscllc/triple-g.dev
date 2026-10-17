---
name: generate-bundle-class
description: Generate a modern Drupal 11 Entity Bundle Class using PHP 8.4+ attributes and strict typing.
disable-model-invocation: true
---
Reference the project architecture defined in #file:ARCHITECTURE.md.
Create a dedicated Entity Bundle Class for the bundle `{{bundle_name}}` of entity type `{{entity_type}}`.

Requirements:
1. **File Location & Namespace:**
   - Path: `src/Entity/{{bundle_class_name}}.php`
   - Namespace: `Drupal\{{module_name}}\Entity`

2. **Code Standards:**
   - Place `declare(strict_types=1);` immediately after the opening PHP tag.
   - Extend the appropriate core base class (e.g., `Drupal\node\Entity\Node`).
   - Decorate the class with the core bundle attribute:
     `#[Drupal\Core\Entity\Attribute\Bundle(entity_type: '{{entity_type}}', bundle: '{{bundle_name}}', label: '{{bundle_label}}')]`
   - Target modern PHP 8.4+ patterns with strict parameter and return types.

3. **Encapsulation & Methods:**
   - Implement typed getter and helper methods for custom fields belonging to this bundle rather than raw field access.
   - Inject dependencies via proper factory/container methods where necessary.
   - Ensure the class passes PHPStan Level 6+.