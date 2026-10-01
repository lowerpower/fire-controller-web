# Riff Groups — Design

Status: proposal, not implemented.

## Goal

Once a personality has a lot of riff files, building a show means clicking the same riffs into the playlist in the same order over and over. A riff group is a saved, named, ordered list of riffs. It appears in the riff list as a red box, and clicking it appends every riff in the group to the playlist at the bottom of the page, exactly as if each one had been clicked by hand.

## How it fits the current UI

Today `load_dir()` in `index.html` calls `/dir.php`, which returns a flat JSON array of file names from `/var/www/html/uploads` (the active personality, via symlink). Map files, `config.conf`, and `t/` are filtered out. Each name becomes a row in `#data-table`; clicking a row calls `click_dir()` → `playlist_item_append()`, which adds a `<tr>` to `#play-table` with a `data-filename` attribute. At play time each playlist entry is fetched through `/playfile.php?name=…`, split into lines, and each non-comment line longer than four characters is sent to the relay controller over the websocket, with the third field used as the delay.

Groups slot into this without changing playback. The playlist stays a list of individual riff files; a group is only a shortcut for filling it.

## Group file format

A group is a plain text file in the personality directory with a `.group` extension, for example `personality/fire_organ/evac_sequence.group`:

```
# Evacuation sequence, small to large
evac_10foot
evac_20foot
Pause5Sec
evac_30foot
evac_40foot
```

One riff name per line, played in order. Blank lines and lines starting with `#` are ignored. Names are exact file names within the same personality directory (no paths, no `/`). A name may repeat. Groups do not reference other groups.

Keeping groups beside the riffs means they belong to one personality (which matches how riffs and maps already work), they travel through git and `live-sync.sh` with no extra steps, and they can be created or edited through the existing file manager.

## Server changes

`dir.php` must stop returning groups as if they were riffs. This matters for safety, not only display: if a `.group` file were played as a riff, `playlist_play()` would send each riff *name* to the relay controller as a command. The response changes from a bare array to objects so the client can tell the two apart:

```json
[
  {"name": "evac_10foot", "type": "riff"},
  {"name": "evac_sequence.group", "type": "group",
   "members": ["evac_10foot", "evac_20foot", "Pause5Sec", "evac_30foot", "evac_40foot"],
   "missing": []}
]
```

`dir.php` parses each group server-side and reports any member that no longer exists in `missing`. To keep old clients working during rollout, this could be a new endpoint (`dir.php?v=2` or `groups.php`) instead of changing the existing response.

`playfile.php` should refuse names ending in `.group` so a group can never be sent to the relay controller even if something on the client goes wrong.

## Client changes

In `load_dir()`, group entries render as red boxes, labelled with the display name and member count, e.g. **Evac Sequence · 5**. They can be pinned at the top of the list or sorted in with riffs; pinned at the top is easier to find once the list is long. A group with missing members shows a warning marker.

Clicking a group loops over `members` and calls `playlist_item_append()` for each, skipping any listed in `missing`. Each appended row gets an extra attribute (`data-group="evac_sequence"`) and a thin red left border so it is visible where entries came from. Individual entries can still be deleted or reordered like any other playlist entry.

Clicking a group only queues riffs. It never starts playback. Pressing play remains the single deliberate action before anything fires.

`display_name()` strips extensions, so a group named `evac.group` and a riff named `evac` would look identical. The red styling distinguishes them visually, but the save flow should also warn when a group name matches an existing riff.

## Creating groups

**Phase 1:** write `.group` files by hand or in the built-in editor, the same way riffs are authored now. No new UI beyond the red boxes and the click behavior.

**Phase 2:** add a **Save playlist as group** button next to the playlist controls. It prompts for a name, takes the current `data-filename` values from `#play-table` in order, and POSTs them to a small `savegroup.php`, which validates every name against the active personality and writes the `.group` file. This is the natural workflow: build a sequence in the playlist, try it, save it.

A later step could add editing in place (reopen a group into the playlist, adjust, save over it) and deletion from the riff list.

## Edge cases

Missing riffs are the most likely problem, because riffs get renamed (as happened with `10foot-evac` → `evac_10foot`). The UI flags the group and skips missing members rather than failing silently or erroring mid-show. Phase 2's save path rejects names that don't exist at save time.

Groups are scoped to one personality. Switching personality changes which groups are visible, just as it changes the riffs.

Nesting is not supported in the first version. If it is added later, it needs cycle detection.

Pause riffs such as `Pause5Sec` work as ordinary members, which gives a simple way to put gaps between effects without a new syntax. A per-line delay (`evac_10foot 2000`) is a possible extension but is not needed to start.

## Out of scope

This design does not change riff file format, relay commands, timing, the websocket protocol, or `midi2relay`. It touches only the web UI, `dir.php`, `playfile.php`, and (in phase 2) one new PHP endpoint.
