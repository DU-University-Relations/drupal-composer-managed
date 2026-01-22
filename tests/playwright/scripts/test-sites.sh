#!/usr/bin/env bash

# Usage: ./test-sites.sh [tag] [environment]
# Example: ./test-sites.sh @smoke dev

TAG="${1:-@smoke}"
ENVIRONMENT="${2:-test}"

jq -r '.[] | .site' sites.json | while read -r SITE; do
  URL="https://${ENVIRONMENT}-${SITE}.pantheonsite.io/"
  echo "Testing: $URL"
  BASE_URL="$URL" npx playwright test --grep "$TAG"
  echo ""
done