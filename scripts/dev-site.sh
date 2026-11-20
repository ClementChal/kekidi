#!/bin/sh
set -eu

site_path="$HOME/Studio/kekidi-dev"

case "${1:-}" in
  start) exec studio start --path "$site_path" --skip-browser --skip-log-details ;;
  dev)
    shift
    studio start --path "$site_path" --skip-browser --skip-log-details
    exec "$(dirname "$0")/dev-reload.sh" "$@"
    ;;
  stop)  exec studio stop --path "$site_path" ;;
  *)     printf 'Usage : %s {dev|start|stop}\n' "$0" >&2; exit 2 ;;
esac
