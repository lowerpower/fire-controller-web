#!/bin/bash
set -euo pipefail

SRC_DIR="/home/pi/github/fire-controller-web"
LIVE_DIR="/var/www/html"

usage() {
    cat <<'EOF'
Usage: live-sync.sh <status|deploy|capture>

  status   Show pending file changes between repo and live web root.
  deploy   Push repo files to /var/www/html.
  capture  Pull live web-root changes back into the repo.

Notes:
  - Runtime paths are preserved: uploads, playdir, uploads.manual-backup-*
  - .git is never copied.
  - If you edited files directly on the live system, run "capture" first.
EOF
}

require_dirs() {
    if [ ! -d "$SRC_DIR" ]; then
        echo "Missing source dir: $SRC_DIR" >&2
        exit 1
    fi

    if [ ! -d "$LIVE_DIR" ]; then
        echo "Missing live dir: $LIVE_DIR" >&2
        exit 1
    fi
}

rsync_common=(
    --archive
    --human-readable
    --itemize-changes
    --delete
    --exclude=.git/
    --exclude=uploads
    --exclude=playdir
    --exclude=uploads.manual-backup-*
)

status_cmd() {
    echo "Repo -> live"
    rsync "${rsync_common[@]}" --dry-run "$SRC_DIR"/ "$LIVE_DIR"/
    echo
    echo "Live -> repo"
    rsync "${rsync_common[@]}" --dry-run "$LIVE_DIR"/ "$SRC_DIR"/
}

deploy_cmd() {
    echo "Deploying repo to live web root"
    rsync "${rsync_common[@]}" "$SRC_DIR"/ "$LIVE_DIR"/
    echo "Deploy complete"
}

capture_cmd() {
    echo "Capturing live web-root changes back into repo"
    rsync "${rsync_common[@]}" "$LIVE_DIR"/ "$SRC_DIR"/
    echo "Capture complete"
}

main() {
    require_dirs

    case "${1:-}" in
        status)
            status_cmd
            ;;
        deploy)
            deploy_cmd
            ;;
        capture)
            capture_cmd
            ;;
        *)
            usage
            exit 1
            ;;
    esac
}

main "${1:-}"
