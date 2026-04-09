# Riff File Guide

This document explains the riff file format used by the Reared In Steel fire
controller UI.

Riff files are plain-text playlists of relay commands. The web UI loads a riff
file from the active `uploads` directory, then sends each line to the
WebSocket relay backend in sequence.

The active riff directory is:

- `/var/www/html/uploads`

When personalities are enabled, `uploads` is usually a symlink to the active
personality directory.

## What A Riff File Does

A riff file is a timed sequence of relay states.

Each playable line usually looks like:

```txt
set <bitmask> <time_ms>
```

Examples from live files:

```txt
set 000001111111 200
set 000200000000 1500
set 0 100
set 0
```

Meaning:

- `set` is the command
- `<bitmask>` is the relay state to send
- `<time_ms>` is the optional hold time in milliseconds before the next line is
  processed

## Parsing Rules

The current player behavior in `index.html` is simple:

- blank lines are ignored
- lines starting with `#` are ignored
- only lines longer than 4 characters are played
- lines are sent exactly as written to the WebSocket backend

Practical guidance:

- use one command per line
- use `#` for comments
- keep commands lowercase `set`
- avoid inline comments on the same line as a command

## Command Format

Supported command style for riffs:

```txt
set <bitmask>
set <bitmask> <time_ms>
```

Examples:

```txt
set 0
set 0 300
set 000000000007 800
set 000000000000 2000
```

Notes:

- `set 0` means all relays off
- `set 0 300` means all relays off, then wait about 300 ms
- a line without a time value advances immediately after the backend responds
- in practice, most authored riffs include an explicit time value

## Bitmask Notes

The bitmask is a hexadecimal string representing the relay state.

Observed examples use different widths depending on the installation:

- `000000000007`
- `000001111111`
- `000010000000`
- `0`

Guidelines:

- preserve the style already used by the current personality
- prefer the same bitmask width as existing riff files in that personality
- use lowercase or uppercase hex consistently within a file
- `0` is always safe shorthand for all off

The exact mapping from bit positions to physical relays depends on the active
personality and hardware layout.

## Timing Notes

The final number on a line is a hold time in milliseconds.

Examples:

- `100` = short pulse
- `200` = medium pulse
- `800` = long pulse
- `1500` = long sustained event
- `30000` = long pause

Observed conventions from shipped riffs:

- `100` to `300` ms for chases and stepping patterns
- `500` to `1500` ms for hits and accents
- `2000+` ms for pauses or scene separation

## Safe Authoring Guidelines

When generating new riffs:

- start and end with a clear all-off state when possible
- use pauses between dense bursts
- avoid unnecessarily long all-on patterns
- prefer short pulses and moving patterns over sustained full-output scenes
- build from existing riffs in the same personality instead of inventing a
  style from scratch

If unsure, author conservative patterns first:

- fewer simultaneous relays
- shorter hold times
- explicit off steps between bursts

## Large Fire Effects

If an effect is labeled `BOMB`, `Bomb`, or `bomb`, treat it as a large fire
effect.

Operational guidance:

- do not trigger it as rapidly as smaller effects
- give it additional recharge and recovery time
- avoid placing repeated `BOMB` activations in fast chase patterns
- prefer longer pauses before the next large hit

For AI authoring and show design, `BOMB`-type effects should be treated as
high-impact accents rather than fast rhythmic elements.

## Authoring Workflow

Recommended process:

1. Pick the active personality.
2. Inspect a few existing riff files in that personality.
3. Copy a similar riff and edit it.
4. Upload or save the new file into `/uploads`.
5. Reload the directory in the UI.
6. Test with short timings before increasing intensity.

Example shell workflow:

```sh
cp "/var/www/html/uploads/Boom Boom" /var/www/html/uploads/new_test_riff.txt
```

## AI Authoring Rules

If an AI is asked to write a riff file for this system, it should follow these
rules:

- Base the new riff on the active personality's existing files.
- Reuse the same bitmask width already used by that personality.
- Output plain text only, one command per line.
- Use only `set <bitmask>` and `set <bitmask> <time_ms>` commands unless told
  otherwise.
- Use `#` comments only on separate lines.
- Include explicit off steps such as `set 0 100` between bursts when writing
  aggressive sequences.
- End the riff with `set 0` or `set 0 <time_ms>` unless there is a clear reason
  not to.
- Do not invent relay meanings; infer style from existing riffs in the same
  personality.
- Treat any effect labeled `BOMB` or `bomb` as a large effect that needs extra
  spacing and recharge time.

## Examples

Simple pulse:

```txt
# Simple single hit
set 000200000000 300
set 0 200
```

Stepping pattern:

```txt
# Two-step chase
set 000001000000 150
set 000002000000 150
set 000004000000 150
set 0 300
```

Burst with recovery:

```txt
# Short burst, then reset
set 000001111111 200
set 000002222222 200
set 000003333333 200
set 0 500
```

## Related Files

- `/var/www/html/uploads`
- `/var/www/html/personality/*`
- `/var/www/html/uploads/config.conf`
- `/var/www/html/uploads/map.txt`
- `/home/pi/github/fire-controller-web/README.md`
