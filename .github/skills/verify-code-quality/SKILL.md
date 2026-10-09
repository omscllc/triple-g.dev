---
name: verify-code-quality
description: Run automated PHPStan and PHPCS linters against targeted files and report or fix errors.
disable-model-invocation: true
---
Reference the project architecture defined in #file:ARCHITECTURE.md.
Execute automated code validation on the target file or directory: `{{file_path}}`

Tasks:
1. Run PHPStan at Level 6+:
   `vendor/bin/phpstan analyse {{file_path}} --level=6`

2. Run Drupal Coder (phpcs) against standard rulesets:
   `vendor/bin/phpcs --standard=Drupal,DrupalPractice {{file_path}}`

3. Inspect command output and exit codes:
   - If warnings or errors are detected, identify the specific line and violation rule.
   - Suggest or directly apply the exact fixes required to restore compliance with Drupal coding standards and strict typing.