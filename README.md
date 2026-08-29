# Sys

Markdown hierarchy viewer. Everything below is behaviour the UI does not spell out.

## Keys

- **Ctrl/Cmd K** — search overlay (also the magnifier in the header)
  - **↑ ↓** move, wraps around
  - **Enter** — container: go into it; entry: go to its container and open it
  - **Esc** or click outside — close
- **Enter** in a title field — commits the rename (blurs)
- **Esc** — closes a dropdown

## Search

- Matches the **whole path**, not just the title
  - `kick/fol/mig` finds `Kickoff process / 03 Follow up / Write the migration notes`
  - characters need only appear in order
- Ranking: title hits beat ancestor hits, tight runs beat scattered, shallow beats deep
- Empty field shows the last 8 opened entries
- Capped at 20 results

## Navigation column

- **Chevron** on a card — unfolds one level inline
  - only one level ever unfolds; a chevron on an unfolded child goes into it
- **Double click** a container — goes into it, at any level
  - the container itself lands in the editor, its children in the list
  - on a phone a single tap does this
- **Filter** box — the list in front of you, by title only
- **The row at the top of the column** is the container you are in
  - click it to open that container in the editor
  - it heads every tab and every list, and never moves
  - not shown at the root, which is no entry
- **Drag the column edge** to resize; **double click it** resets the width
- Arriving at a container puts **that container** in the editor
  - applies to every route in: double click, chevron, back, breadcrumb, search, path line
  - switching tab does not, it stays in the same container

## Breadcrumb

- A segment — goes to that container
- The trail **stops before the container you are in** — the column names that
  - except at the root, which the column does not name
- **Chevron between segments** — lists the containers of that level (sideways jump)
- **…** in a deep trail — the skipped levels, click to jump
  - collapses past 4 segments, keeps root and the last two

## Main / Recent / Pinned

- Pills sit under the list, only on the **Entries** tab
- Recent and Pinned ignore the current container
  - each row shows where it lives; clicking that line goes there
  - navigating anywhere resets to **Main**
- **Pin** lives in the card's ⋮ menu, not on the card
- Both store **ids**, not paths
  - survive move and rename
  - deleting drops the entry silently
- Recent holds 50, the overlay shows 8

## Not reachable

Objects without an id are invisible to search, Recent and Pinned.

- A folder with no `-this.md` has no id
- The ⋮ menu hides **Pin** for those

## Data on disk

- Everything is files under `/data`, no database
- **Title = file name.** Renaming renames the file or folder
- `MY_ENTRY.md` and `MY_ENTRY/-this.md` are the **same object**
  - a folder gets its `-this.md` the first time it is saved
  - folders made outside the app have none until then
- The **database root is not an entry** — it cannot be opened or saved
- `(lnk)` in a name marks a **link stub**
  - opening a link opens its target
  - pinning a link pins the stub, not the target
- Resources keep their extension; fields live in a sidecar `NAME.ext.md`
- Front matter keys the app owns: `id`, `type`, `created`, `modified`, `target` — the rest belongs to the type
- `<db>/.sys/` — runtime state, not content
  - `index.json` rebuilds itself, safe to delete
  - `recent.json`, `pinned.json` do not — deleting them loses the lists

## Behaviour that surprises

- Lists are sorted **newest first**, not alphabetically
- **Delete is permanent.** No trash
- Autosave 800 ms after the last keystroke
  - title and dropdowns save at once, the title on leaving the field
- The index rebuilds itself when any folder changes on disk
- **Check database** (⋮ menu) finds duplicate ids, shadowed files, broken links

## Config

- `config.yml` — `dbPath`, `theme`, `appName`
- `<db>/.sys/config.yml` — `thisFile`, `linkMarker`, `defaultType`, `pageSize`, `sort`
- Tabs, fields, icons and menu entries per type — `types/<Type>/config.yml`
  - `create: { tab: ['__link:Person'] }` makes **+** add a link, not a new object

## URLs

- `index.php?path=A/B` — first paint at a container
- `#/A/B?tab=entries&sel=A/B/Entry` — kept in sync, shareable
