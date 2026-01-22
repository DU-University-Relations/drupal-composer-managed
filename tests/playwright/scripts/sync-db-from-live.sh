#!/usr/bin/env bash

# Usage: ./sync-db-from-live.sh [environment]
# Example: ./sync-db-from-live.sh dev

ENVIRONMENT="${1:-test}"

# Load sites into a variable to avoid stdin conflicts with terminus
SITES=$(jq -r '.[] | .site' sites.json)

if [ -z "$SITES" ]; then
  echo "No sites found in sites.json"
  exit 1
fi

for SITE in $SITES; do
  echo "Cloning back db from live to: $SITE.$ENVIRONMENT"
  terminus env:clone-content "${SITE}.live" "${ENVIRONMENT}" --db-only
  echo ""
done