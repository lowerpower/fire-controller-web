# Codex Project Notes

Read this file first for a fast handoff, then use `README.md` for the fuller project description.

## Repo and Live Paths

- Git checkout: `/home/pi/github/fire-controller-web`
- Live Apache root: `/var/www/html`
- Deploy command from repo root: `./live-sync.sh deploy`

Edit and commit in the git checkout. The browser is served from `/var/www/html`.

## Current Operating Model

- The main UI is in `index.html`.
- Riff list is served by `dir.php`.
- Personality switching is handled by `personality.php`.
- The active content path used by the UI is `/var/www/html/uploads`.
- In normal personality mode, `uploads` is a symlink to `personality/<name>`.

Useful checks:

- Active personality target: `readlink -f /var/www/html/uploads`
- Repo status: `git status --short`
- Quick deploy: `./live-sync.sh deploy`

## Important UI Behavior

`/uploads/config.conf` drives the clickable grid.

Expected line format:

```txt
<grid_position> <relay_number> [label] [impulse_time]
```

Important nuance:

- A line with only the grid position means that cell is intentionally unused.
- Unused cells must be inert in the UI.

## Recent Regression and Fix

Fixed in commit `757150e`:

- Symptom: clicking some grey or unmapped cells could fire relay `#1`.
- Cause: the grid parser treated partial `config.conf` lines as active cells, and the click path did not hard-reject unmapped positions.
- Fix: `index.html` now:
  - ignores blank/comment lines and partial config entries
  - marks cells with missing relay mappings as disabled
  - returns early in the click handler for disabled or unmapped cells

If this specific bug appears again, inspect:

- `index.html`
- the active `/uploads/config.conf`
- whether `/var/www/html/index.html` matches the repo copy

## Known Project Quirks

- The repo copy and live Apache copy can drift if a change is deployed outside git flow.
- `personality.php` has changed over time on the live tree; if behavior looks inconsistent, compare repo vs live copies directly.
- Playlist behavior can still appear healthy even when the grid mapping is broken, because they exercise different paths.

## Good First Checks For Any Web UI Regression

1. Confirm active personality: `readlink -f /var/www/html/uploads`
2. Compare repo and live files if behavior is surprising.
3. Read the active `config.conf`.
4. Check whether the issue is grid-click specific, playlist specific, or personality-switch specific.
5. Deploy after repo edits and verify the live file matches.

