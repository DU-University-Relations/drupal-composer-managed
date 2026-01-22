#!/usr/bin/env bash

# Usage: ./enable-qa-on-sites.sh [environment]
# Example: ./enable-qa-on-sites.sh dev

ENVIRONMENT="${1:-test}"

# Load sites into a variable to avoid stdin conflicts with terminus
SITES=$(jq -r '.[] | .site' sites.json)

if [ -z "$SITES" ]; then
  echo "No sites found in sites.json"
  exit 1
fi

for SITE in $SITES; do
  echo "Turning on QA features for: $SITE.$ENVIRONMENT"
  terminus drush "${SITE}.${ENVIRONMENT}" -- en du_functional_testing -y
  echo ""
done