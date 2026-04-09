# fire-controller-web
Web UI for the Reared In Steel (https://www.facebook.com/rearedinsteel/) flower tower fire controller.

## Overview

This project is the Apache-served web UI for the relay controller.

On this Pi there are two copies of the web app:

- Git checkout: `/home/pi/github/fire-controller-web`
- Live Apache web root: `/var/www/html`

Edit and commit from the git checkout. Deploy to Apache with `live-sync.sh`.

Additional operational docs:

- `RIFFS.md` for riff-file authoring guidance

## Data Directories

The web root must have two read/write directories that are accessible by both
the web server and the default user.

These are:

- `uploads`
- `playdir`

`uploads` is where the riff files and configuration file are stored.

`playdir` is where files may be copied to be played.

## Personality Support

The active personality is selected by pointing `uploads` at one of the
directories under `personality/`.

Examples:

- `personality/medusa`
- `personality/fire_bar`
- `personality/flower_tower`

The active personality is switched through:

- the main page selector in `index.html`
- `personality.php`

Current API behavior:

- `GET /personality.php` returns the current personality and the list of
  available personalities.
- `POST /personality.php` with `action=set&personality=<name>` repoints
  `uploads` to that personality directory.

On the live Apache copy, `uploads` may be either:

- a normal writable directory
- a symlink to `personality/<name>`

When a personality is active, uploads and file edits go directly into that
personality directory because the rest of the UI always reads and writes
through `/uploads/...`.

## Personalities

A personality is a complete set of controller content for one sculpture or
layout. In practice, a personality directory usually contains:

- `config.conf` for the relay/grid layout
- `map.txt` for MIDI note-to-relay mapping
- riff files used by the playlist UI

Examples live under:

- `/var/www/html/personality/medusa`
- `/var/www/html/personality/fire_bar`
- `/var/www/html/personality/flower_tower`

Operational model:

- The UI always reads from `/uploads/...`
- The active personality is selected by making `uploads` a symlink to
  `personality/<name>`
- Uploads and file-manager edits therefore modify the active personality
  directly

If `uploads` is a normal directory instead of a symlink, the UI still works,
but it is operating on that standalone directory rather than on a named
personality.

To check the active personality from the shell:

```sh
readlink -f /var/www/html/uploads
```

To switch personality from the shell:

```sh
curl -X POST -d 'action=set&personality=medusa' http://127.0.0.1/personality.php
```

Creating a new personality is usually easiest by copying an existing one:

```sh
cp -a /var/www/html/personality/medusa /var/www/html/personality/new_show
```

Then switch to it with `personality.php`, the main-page selector, or by
repointing `uploads`.

After switching personality, reload the browser page so the grid config and
riff list refresh from the newly selected files.

## Upload And File Editing

Two web interfaces write to the active `uploads` location:

- `upload.php` for direct uploads
- `fm/` for browser-based file management and editing

Default file-manager login on this image:

- user: `admin`
- pass: `admin`

The file manager is restricted to `../uploads/`, so it automatically follows
the currently selected personality.

## config.conf Format

The grid UI reads:

- `/uploads/config.conf`

Each non-empty line is split on spaces and interpreted as:

```txt
<grid_position> <relay_number> [label] [impulse_time]
```

Meaning:

- `grid_position`: the button position in the UI grid, starting at `1`
- `relay_number`: the relay to fire when that grid position is clicked
- `label`: optional short label shown on the button
- `impulse_time`: optional hold time sent with the `set` command

Behavior:

- If `relay_number` is `0`, the grid position is disabled.
- If `label` is omitted, the grid may show the numeric position instead.
- If `impulse_time` is omitted, the UI defaults to `1`.

Examples:

```txt
1 1 A11 10
2 2 A12 10
3 0
6 7 G 100
```

From those examples:

- grid position `1` fires relay `1`, shows label `A11`, uses impulse `10`
- grid position `3` is disabled
- grid position `6` fires relay `7`, shows label `G`, uses impulse `100`

The current loader uses simple space splitting, so labels should be a single
token with no spaces.

Design guidance:

- Arrange `grid_position` entries so the UI resembles the overhead physical
  layout of the sculpture.
- The goal is not just numeric ordering. The grid should visually map to the
  real-world burner layout so operators can trigger effects by position.

Changes to `config.conf` take effect after reloading the browser page.

## map.txt Format

The MIDI daemon reads:

- `/uploads/map.txt`

Each non-comment line is interpreted as:

```txt
<note> <channel> <relay>
```

Meaning:

- `note`: MIDI note number
- `channel`: zero-based MIDI channel as used by `midi2relay`
- `relay`: relay number to activate

Valid ranges from the current MIDI code and docs:

- `note`: `0` to `127`
- `channel`: `0` to `15`
- `relay`: `1` to `255`

Notes:

- Lines starting with `#` are comments.
- Relay numbering is effectively 1-based.
- Multiple note/channel pairs may map to the same relay.
- The comments in some shipped map files explain the zero-based channel
  convention, for example `our channel 2 = midi channel 3`.

Examples:

```txt
108 0 1
107 0 16
1 3 1
2 3 2
```

Changes to `map.txt` are picked up by `midi2relay` when the file modification
time changes. The daemon checks periodically rather than on every packet, so
reload is not necessarily instant.

## When Changes Take Effect

- `config.conf`: reload the browser page
- riff files: use `Reload Dir` in the UI or reload the page
- `map.txt`: wait for `midi2relay` to reload the file, or restart the monitor
  stack if you want it applied immediately

## Websocket Interface To Relay Controller

The relay controller exposes a WebSocket-compatible backend through
`websocketd` on port `8080`, but browsers on this Pi should use Apache on port
`80` via `/ws/`. The interface is simple. The following commands are supported:

- `set <bitmask> [time]`
- Sets the bitmask.
- Allows the next bitmask to be set after the optional time value in
  milliseconds.
- Returns the newly set bitmask when complete.
- `get`
- Returns the current state bitmask.

On this Pi, the browser does not connect directly to port `8080`. Apache
proxies `/ws/` on port `80` to the backend WebSocket service on `127.0.0.1:8080`.
This keeps the browser UI and WebSocket traffic on the same origin and port.

## Requirements

https://github.com/lowerpower/SainSmartUsbRelay

## Also See

Demo of its operation on the flower tower:
https://www.facebook.com/rearedinsteel/videos/713271008877496/

## Links
* https://github.com/lowerpower/SainSmartUsbRelay - relay driver/websocket backend for this project.
* https://www.facebook.com/rearedinsteel/ - Reared In Steel facebook.  
* https://www.facebook.com/rearedinsteel/videos/713271008877496/ - project in action on flower tower
* [Video Demo](https://www.youtube.com/watch?v=d_1EEWdWekI) of this project integrated into a solution.
* [websocketd](https://github.com/joewalnes/websocketd) - allows websocket control of a stdio program.

## Live Deploy Workflow

On this Pi, Apache serves the deployed copy from `/var/www/html`, while the git
checkout stays in `/home/pi/github/fire-controller-web`.

Use the helper script:

```sh
./live-sync.sh status
./live-sync.sh capture
./live-sync.sh deploy
```

Recommended workflow:

- If you changed files directly in `/var/www/html`, run `./live-sync.sh capture`
  first.
- Commit from the git checkout in `/home/pi/github/fire-controller-web`.
- Run `./live-sync.sh deploy` to push repo changes back to the live web root.

Example:

```sh
cd /home/pi/github/fire-controller-web
./live-sync.sh status
./live-sync.sh capture
git status
git add .
git commit -m "update fire controller web ui"
./live-sync.sh deploy
```

The script intentionally preserves runtime paths:

- `uploads`
- `playdir`
- `uploads.manual-backup-*`

The script also uses `rsync --delete`, which means:

- `deploy` removes non-runtime files from `/var/www/html` if they are not in
  the repo
- `capture` removes non-runtime files from the repo if they exist only in the
  live web root

So if you made ad hoc changes outside the normal tracked files, review
`./live-sync.sh status` carefully before running `deploy` or `capture`.

## Notes For Live Systems

This deploy/capture split exists because production edits often happen on the
Pi itself, and `/var/www/html` can easily drift away from the git checkout.

If the live copy was edited first:

1. Run `./live-sync.sh capture`
2. Review with `git status` and commit from the repo
3. Run `./live-sync.sh deploy`

If the repo was edited first:

1. Commit from `/home/pi/github/fire-controller-web`
2. Run `./live-sync.sh deploy`
