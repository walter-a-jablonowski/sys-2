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
- **Filter** box — the list in front of you, by title and tags (`best` lists everything tagged `best`)
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

## Ids

Search, Recent, Pinned and links find objects by the `id` in their front matter. A missing one is added:

- **Every `.md` entry** — when the index is built, so a database made outside the app gets its ids on first use
  - only the `id` line is added (a plain `.md` gets a front matter holding just that), body and file time stay
  - a front matter that does not parse is left alone, it needs fixing by hand first
- **A folder with no `-this.md`, a resource with no sidecar** — only when needed: on **Pin**, when linked to, or on the first save
  - that writes the `-this.md` or the sidecar
  - search finds them all the same (by path), Recent does not list them until then
- Generated ids are unique: a taken one gets `-2`, `-3`, …

## Objects

Every file and folder under the database is an **object** — no separate registry.

- `MY_ENTRY.md` and `MY_ENTRY/-this.md` are the **same object**
  - leaf: `MY_ENTRY.md`; container: folder `MY_ENTRY/` with its content in `-this.md`
  - adding a child to a leaf turns it into a folder (`X.md` → `X/-this.md`)
  - a folder gets its `-this.md` the first time it is saved
  - folders made outside the app have none until then — type **Group**, no id
- The content file name is set **per database**, for all its folders
  - `thisFile` — the name the app writes (default `-this`)
  - `thisFileMatch` — regex on the name without `.md`, also read as content file
    - `thisFile: '- DESC'` + `thisFileMatch: '-\s*DESC.*'` accepts `- DESC.md`, `-DESC.md`, `- DESC Kickoff notes.md`
  - always `.md`: `- DESC.pdf` is an ordinary resource
  - several matches in one folder — the first alphabetically wins, **Check database** lists the rest
  - an entry cannot be created or renamed to a matching name
- Every `.md` is an entry, with or without front matter — its `type` decides
  - missing `type`: the name may give one (e.g. `2 - My task` is an Activity), else `defaultType`
  - such a type is **not written** to the front matter, so the name keeps deciding: remove the number, it is an Info again
  - a type picked in the editor's **Front matter** tab is written, and then wins over the name
- Any other file is a **resource** (type **File**)
  - keeps its extension; fields live in a sidecar `NAME.ext.md`
- Not objects: hidden names (leading `.`), `-this.md`, sidecars, `X.md` next to a folder `X` (shadowed)
- `(lnk)` in a name marks a **link stub** (type **Link**, front matter `target` = id)
  - opening a link opens its target
  - pinning a link pins its target, like Recent keeps the target; a broken link has nothing to pin
  - links made in the app always take this form

## Fields in the name

A database may read fields out of file and folder names — `nameFields` in `<db>/.sys/config.yml`, example there.

- Rules run **in their order**, each takes its tokens out before the next one looks; what is left is the **title**
  - so `(lnk-K12)` and `(id-K12)` must come before a catch-all like `\(([^()]+)\)`
- A token stands alone: blank or start/end of the name around it
  - `match` — regex, group 1 is the value, else the whole token
  - `list: true` — every token counts (e.g. tags), otherwise only the first
- **Fixed values**, e.g. tags — names put no mark on them, so the strings are listed: `values: ['best', 'draft']`
  - `(best)` is a tag, `(some text)` stays in the title
  - `format: '({value})'` is how a tag is written and also how it is found; or `match: '\(?{value}\)?'` places them freely
  - global in `<db>/.sys/config.yml`; a type adds its own in `types/<Type>.yml`: `nameFields: { tags: ['urgent'] }`
    - the database decides how a field is written, a type only adds words
    - `(urgent)` is a tag on an Activity, on an Info it stays in the title
    - path lines (search, Recent, Pinned) know only the global tags
  - editor: toggle chips for a list, a dropdown for a single value; nothing else is accepted
  - cards show them right after the title, leading ones (`at: 'start'`, e.g. the prio) right before it
- **Leading fields, selection and priority**, e.g. `2 - My task` — see the `prio` rule in `data/gd_demo/.sys/config.yml`
  - `at: 'start'` — the token leads the name, the title follows right away: `2 - x`, `2-x`, `2 -x` all read as `2`
    - written as `format` says, before the title
    - a title like `3-D printing` would read as prio 3, so it is refused
  - `type: 'Activity'` — a name with this field makes an entry or a folder an Activity, unless its front matter names a type
  - `sort: true` — lists are sorted by it in the order of `values`, entries without one last, newest first within
  - `selected: { '1': 'global', '2': 'local' }` — selected for the whole database or within its parent; filled chips
    - the priorities 3–5 are relative to the parent as well
  - `labels` — the editor's dropdown (`1 – Selected`, `3 – Important`) and the tooltip of a chip; the chip shows the value
- `colors: { '1': 'gold', '3': 'red' }` — chip color per fixed value: `gold` (default), `red`, `orange`, `yellow`,
  `green`, `blue`, `purple`, `gray`, `lightgray`; the theme defines them (`--tag-red`, …)
- `role: 'id'` — **alternative id** (`id2`), unique across the database
  - `id` in front matter stays the primary id: Pinned and Recent store that one
  - duplicates show in **Check database**, the editor refuses a taken one
- `role: 'link'` — the entry is a **link** to the one with that alternative id, whatever its type
  - `My file (lnk-K12).md` — own title, front matter and body are allowed
- Shown in the editor after the title, unless the type places the field itself
  - editing one **renames** the file, written with `format` (e.g. `(id-{value})`); no `format` — read-only
  - a list field without fixed values is edited as `a, b`
  - renaming the title keeps every token as written, moved behind the title
  - whatever is written must read back the same, otherwise the save is refused (e.g. a title containing `(best)`)
- A **title is required** besides the fields
  - a name holding only fields, like `(id-K12).md`, shows as **No title**
  - the editor saves nothing until one is entered, **Check database** lists such names
- Resources: fields come from the part before the extension
- Not in front matter: these fields live only in the name
- The **database root is not an entry** — it cannot be opened or saved
- Move and rename carry the sidecar along; delete removes a folder with everything inside

## Data on disk

- Everything is files under `/data`, no database
- **Title = file name** (minus the fields in the name). Renaming renames the file or folder
  - characters the file system refuses become `-`; a taken name gets ` 2`, ` 3`, …
- Front matter keys the app owns: `id`, `type`, `created`, `modified`, `target` — the rest belongs to the type
- `<db>/.sys/` — runtime state, not content
  - `index.json` rebuilds itself, safe to delete
  - `recent.json`, `pinned.json` do not — deleting them loses the lists

## Behaviour that surprises

- Lists are sorted **newest first**, not alphabetically — after a sorting field in the name, if the database has one
- **Delete is permanent.** No trash
- Autosave 800 ms after the last keystroke
  - title and dropdowns save at once, the title on leaving the field — same for fields in the name
- The index rebuilds itself when any folder or `<db>/.sys/config.yml` changes
- **Check database** (⋮ menu) finds duplicate ids and alternative ids, shadowed files, extra content files, names without a title, broken links

## Config

- `config.yml` — `dbPath`, `theme`, `appName`
- `<db>/.sys/config.yml` — `thisFile`, `thisFileMatch`, `linkMarker`, `nameFields`, `defaultType`, `pageSize`, `sort`
- Fields, tabs and icon per type — `types/<Type>.yml`
  - `create: { tab: ['__link:Person'] }` makes **+** add a link, not a new object
  - `viewer: 'NAME'` — the viewer used unless the user picked another one
- Viewers — `type_viewer/<Type>/<Viewer>/`, a type may have several
  - `config.yml` — `label`, `detailTabs`, `header`, `footer`, `menu`
  - optional `list_renderer.php`, `mobile_renderer.php`, `detail_renderer.php`, … with class `<Type><Viewer>ListRenderer` etc.
  - optional `toolbar_renderer.php` — buttons in the header next to the entry's name, folded into an **Actions** dropdown on a phone
  - optional `statusbar_renderer.php` — facts in the footer next to the save state, may hold tiny buttons
  - toolbar and status bar buttons are entries like the ⋮ menu: `ajax` (plus `params`) or `js`
  - demo: **Activity → Workflow** has both
  - optional `styles.css`, `controller.js`, `ajax/<fn>.php`
  - shown: the user's choice, else the type's `viewer`, else `default`
- `data/users/default/settings.yml` — user settings, `viewers: { Activity: 'compact' }` is written by the switch
  - the switch sits in the header next to the entry's name, only when its type has more than one viewer

## URLs

- `index.php?path=A/B` — first paint at a container
- `#/A/B?tab=entries&sel=A/B/Entry` — kept in sync, shareable
