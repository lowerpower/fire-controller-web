# fire-controller-web
Web UI for the Reared In Steel (https://www.facebook.com/rearedinsteel/) flower tower fire controller.

## Overview

This project is the Apache-served web UI for the relay controller.

On this Pi there are two copies of the web app:

- Git checkout: `/home/pi/github/fire-controller-web`
- Live Apache web root: `/var/www/html`

Edit and commit from the git checkout. Deploy to Apache with `live-sync.sh`.

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

## Websocket Interface To Relay Controller

A WebSocket interface is available on port `8080` on the controller. The
interface is simple. The following commands are supported:

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
