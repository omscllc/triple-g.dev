#!/usr/bin/env bash
set -euo pipefail

repo_root="$(git rev-parse --show-toplevel)"
cd "$repo_root"

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker is required to check Drupal configuration before committing." >&2
  exit 1
fi

if ! config_status="$(docker compose exec -T web vendor/bin/drush config:status --format=json)"; then
  echo "Unable to run Drush config:status; start the local Docker stack before committing." >&2
  exit 1
fi

if ! printf '%s' "$config_status" | node -e '
let output = "";
process.stdin.setEncoding("utf8");
process.stdin.on("data", (chunk) => output += chunk);
process.stdin.on("end", () => {
  try {
    const status = JSON.parse(output);
    const differences = Array.isArray(status) ? status : Object.keys(status ?? {});
    if (differences.length > 0) {
      console.error("Drupal configuration differs from config/sync:");
      console.error(JSON.stringify(status, null, 2));
      process.exitCode = 1;
    }
  }
  catch (error) {
    console.error("Could not parse Drush config:status JSON:", error.message);
    process.exitCode = 1;
  }
});'
then
  echo "Export Drupal configuration with 'drush config:export' before committing." >&2
  exit 1
fi