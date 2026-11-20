#!/bin/sh
set -eu

cd "$(dirname "$0")/.."
KEKIDI_STUDIO_URL="${1:-http://localhost:8885}" exec npx --yes browser-sync@3.0.4 start \
  --config scripts/browser-sync.cjs \
  --listen 127.0.0.1 \
  --no-ui --no-ghost-mode --no-open --no-online \
  --files 'wordpress/kekidi/**/*,wordpress/kekidi-*/**/*'
