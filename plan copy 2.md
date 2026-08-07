# Plan — Markdown hierarchy app

Supersedes `idea.md`. v1 scope: **one database (`/data/demo`), no auth, no users.**

---

## 1. Gaps + conflicts in idea.md, and how they are resolved

| # | Problem in idea.md | Resolution |
|---|---|---|
| C1 | `MY_ENTRY.md` and `MY_ENTRY/-this.md` are "the same" — undefined if both exist | Folder form wins, plain file is shadowed and reported by the DB check. Adding a child auto-promotes `X.md` -> `X/-this.md` |
| C2 | `speaking-identifier_YYYY-MM-DD-HHMM` sits under *type* config but is an **instance id** | Renamed to `idPattern` in type config. It is only the **generator** for new ids, never a validation rule — any string is a valid id |
| C3 | No stable identity, but objects can be linked from elsewhere | `id:` in front matter (free-form string, unique per DB) + `.sys/index.json` mapping id -> path |
| C4 | "Resource = file without front matter" **and** type `File` exists | Resources stay implicit objects; type `File` is only the *handler* that renders/edits them. Optional sidecar `NAME.ext.md` carries metadata |
| C5 | Linking mechanism undefined | A link is identified by `type: Link` in its front matter. The **file name is free** — `Anna Meier (lnk).md` — where `(lnk)` is only a marker inside the name, not a second file extension |
| C6 | Links could create cycles | A link is always a **leaf**: it never expands children. Click navigates to the target's real location |
| C7 | `/themes` per type — themes would fragment | Themes are app level (`/themes/*.css` = token sets). A type may ship *optional* overrides `types/X/themes/<theme>.css`, but must primarily consume tokens |
| C8 | No ordering rule, yet the list has "load more" | Explicit sort (`order` field, then sort config), deterministic, paged by offset |
| C9 | Deep navigation defined, no way back | Back arrow in the left app header. Breadcrumb deferred — decided later, once we know where it fits |
| C10 | Delete semantics for linked / linking objects missing | Delete link = target untouched. Delete target = referring links shown first, then become broken-link cards |
| C11 | ASCII diagram draws an input row in the AI column, text says footer is empty there | Both exist: header and footer bands have **two segments** (nav column, content area). `FOO_2` runs the full content width under the mid *and* the AI column, so the strip below the composer is simply that band continuing |
| C12 | Unknown / missing `type:` | Two different cases: **no front matter at all -> resource file** (idea.md rule, applies to `.md` too). Front matter present but `type` unknown or missing -> `defaultType` (`Info`), raw keys stay editable and are never lost on save |
| C13 | Concurrent writes not considered | Save carries the `mtime` it was loaded with; mismatch -> 409, no silent overwrite |
| C14 | Tabs listed only as example values | Generalized: a tab = a filter over children (types + inline/link), declared in the container's type config |

Decisions taken from clarification: free-form `id` string, `NAME (lnk).md` link files, **keep** per-type mobile renderer, single DB / no auth.

---

## 2. Data model

### 2.1 Database layout

File and folder names may contain spaces and are shown to the user as they are.

```
/data/demo
  .sys/
    config.yml               db settings
    index.json               id -> path cache (generated, git-ignorable)
  Kickoff process/
    -this.md                 type: Activity
    01 Preparation.md        type: Activity
    02 Workshop/
      -this.md               type: Activity
      Agenda.md              type: Info
      Anna Meier (lnk).md    type: Link  -> person id
      slides.pdf             resource
      slides.pdf.md          resource metadata (optional sidecar)
    Notes/                   no -this.md -> implicit Group
      First idea.md
  Persons/
    -this.md                 type: Group
    Anna Meier.md            type: Person
    Ben Orlov.md             type: Person
```

### 2.2 Path rules

| Path form | Meaning |
|---|---|
| `NAME.md` | leaf object |
| `NAME/-this.md` | same object, but with children |
| `NAME/` without `-this.md` | implicit **Group** |
| `NAME (lnk).md` | link stub -> another object. `(lnk)` is a marker **inside** the name, not a file extension. Any name works; the type comes from the front matter |
| `NAME.ext` | resource file (implicit object, handled by type `File`) |
| `NAME.md` **without front matter** | also a resource file — being markdown is not enough, the type must be declared |
| `NAME.ext.md` | sidecar metadata for that resource |
| `.sys/`, `.*` | hidden, never listed |

```
is it an object?
  front matter present?  --no-->  resource file  (any extension, incl. .md)
        |
       yes
        v
  type known?  --no-->  defaultType (Info), original keys kept
        |
       yes -> that type
```

- Spaces are allowed everywhere: `Anna Meier.md`, `02 Workshop/`.
- Only characters the file system forbids (`\ / : * ? " < > |`) are replaced when the app writes a name.

### 2.3 Object front matter

```markdown
---
id: workshop-2026-08-07-1030      # any string, unique per DB, stable
type: Activity                    # folder name under /types
title: Workshop                   # optional, falls back to file/folder name
created: 2026-08-07T10:30:00
modified: 2026-08-07T11:02:00
# ... type specific fields follow
---

Markdown body = the "description" field.
```

| Key | Req | Written by | Note |
|---|---|---|---|
| `id` | yes | app on create | **any string**, only uniqueness is enforced. New ids are generated from the type `idPattern` (currently `my-id-YYYY-MM-...`), hand-written ids are accepted unchanged. Never rewritten on rename |
| `type` | yes | app | unknown -> `defaultType`, value preserved |
| `title` | no | user | fallback: file / folder name |
| `created` / `modified` | auto | app | ISO 8601. **The sort source** — never taken from OS file times, so ordering is identical on every computer |
| unknown keys | — | foreign tools | kept verbatim on save |

### 2.4 Link stub

`Anna Meier (lnk).md` — the name is the user's choice, `(lnk)` is only a marker the app adds when it creates the file so links are recognizable in Explorer / git. What makes it a link is the front matter, and that is all it holds:

```markdown
---
type: Link
target: anna-meier-2026-08-01-0900   # id of the target
---
```

- **Label = the link file's own name**, freely editable and independent of the target's name. No `title` key, nothing to keep in sync.
- Rendered by the **target's** list/mobile renderer, with a link marker.
- Broken target -> broken-link card with "remove" action.

### 2.5 Index (`.sys/index.json`)

```json
{
  "builtAt": 1754563200,
  "objects": {
    "workshop-2026-08-07-1030": { "path": "Kickoff process/02 Workshop", "type": "Activity", "title": "Workshop", "mtime": 1754563100 }
  }
}
```

- Rebuild: full rescan when any DB dir mtime > `builtAt`; incremental update on app writes.
- Purpose: id -> path resolution, backlink lookup, duplicate-id detection.
- JSON is the v1 storage, likely replaced by SQLite or similar when the DB grows. Therefore the `Index` class is the **only** code that knows the storage format — everything else asks it for ids, paths and backlinks.

### 2.6 DB config (`.sys/config.yml`)

```yml
thisFile: '-this'
linkMarker: ' (lnk)'       # appended to generated link file names, cosmetic only
defaultType: 'Info'
pageSize: 50
sort:
  by: 'created'            # front matter timestamp, not the OS file time
  dir: 'desc'              # newest first
```

### 2.7 Ordering + paging

v1 has exactly one sort: **newest first**, from the front matter. More sort options and manual ordering come later.

```
sort key = (created ?? modified ?? file mtime, filename)   desc, filename breaks ties
page     = offset / pageSize, "load more" raises offset
```

- `created` missing (file written by hand or another tool) -> fall back to `modified`, then to the file mtime, and write `created` back on the next save.
- **No `order` field.** Manual ordering gets its own solution later; nothing is stored for it now.

### 2.8 Rename

Renaming the title **renames the file or folder**.

```
title "Workshop" -> "Kickoff workshop"
  02 Workshop/-this.md   ->   Kickoff workshop/-this.md
  Agenda.md              ->   (children move with the folder)

file name             =  the title verbatim, spaces kept, only forbidden chars replaced
id stays              -> every link keeps working
index updated         -> path entry rewritten, no link rewriting needed
name taken            -> suffix " 2", " 3", ...
manual file rename    -> id unchanged, next index refresh picks up the new path
```

- Renaming a link file changes only its label — the target is untouched.

---

## 3. Type system

```
/types/Activity
  config.yml
  controller.php          type specific server logic (class)
  list_renderer.php       desktop card
  mobile_renderer.php     mobile cell (iOS TableViewCell style)
  detail_renderer.php     editor
  header_renderer.php     content of the app header segment (NAME_2), optional
  footer_renderer.php     content of the footer segment (FOO_2), optional
  controller.js           type specific client logic
  styles.css
  /themes/<theme>.css     optional overrides
  /ajax/<action>.php      type specific endpoints
```

### 3.1 `types/Activity/config.yml`

```yml
name: 'Activity'
label: 'Activity'
description: 'A step in a process'
idPattern: '{slug}-{YYYY}-{MM}-{DD}-{HHmm}'   # generator for new ids only, ids are free strings
icon: 'activity'

fields:                                   # hierarchical
  title:  { type: 'text',     required: true }
  status: { type: 'select',   options: ['open', 'running', 'done'] }
  body:   { type: 'markdown', label: 'Description' }
  schedule:
    fields:
      start: { type: 'date' }
      end:   { type: 'date' }

tabs:                                     # children filters, left column
  entries:      { label: 'Entries',      types: ['Activity', 'Info', 'Group'], include: ['inline', 'link'], tools: true }
  participants: { label: 'Participants', types: ['Person'],                    include: ['link', 'inline'] }
  resources:    { label: 'Resources',    types: ['File'] }

create:                                   # [+] dropdown, per tab
  entries:      ['Activity', 'Info']
  participants: ['__link:Person']
  resources:    []                        # no upload in v1, files are placed in the folder manually

header: true                              # header_renderer.php fills NAME_2, else default (title)
footer: true                              # footer_renderer.php fills FOO_2, else empty

detailTabs:                               # optional tabs *inside* the editor, omit for a single pane
  - { id: 'content',  label: 'Content' }
  - { id: 'schedule', label: 'Schedule' }
  - { id: 'raw',      label: 'Front matter' }

menu:                                     # type entries of the "..." dropdown, above the divider
  - { id: 'setDone',   label: 'Mark as done', ajax: 'set_done' }
  - { id: 'duplicate', label: 'Duplicate',    ajax: 'duplicate', confirm: false }
  - { id: 'exportMd',  label: 'Export as md', js: 'exportMd' }    # handled in the type controller.js
```

### 3.2 Initial types

| Type | Purpose | Tabs | Notes |
|---|---|---|---|
| `Activity` | process step, title + description | entries, participants, resources | container |
| `Info` | text entry | entries | container-capable |
| `Person` | participant | resources | link target |
| `File` | resource handler | — | no stored instances, handles `NAME.ext` |
| `Group` | pure grouping | entries | implicit for folders without `-this.md` |
| `Link` | link stub | — | never rendered by itself |

---

## 4. Application structure

```
/ajax                 core endpoints
/data/demo            the database
/lib                  core classes
/styles               default styles (split by concern)
/themes/default.css   theme tokens
/types/<Type>
/view                 php/html partials
ajax.php              router  -> /ajax/* or /types/X/ajax/*
composer.json         symfony/yaml
config.yml            app config (dbPath, theme, ...)
controller.js         app controller
index.php             single page shell
```

### 4.1 Core classes (`/lib`)

| Class | Responsibility |
|---|---|
| `Config` | app `config.yml` + db `.sys/config.yml` |
| `Db` | database root, path safety, health check |
| `ObjPath` | path <-> object rules (`-this`, `.lnk.`, folder vs file, promotion) |
| `FrontMatter` | parse / serialize md + yml, preserves unknown keys |
| `Obj` | one object: id, type, path, fields, body, flags (isLink, isResource, isContainer) |
| `ObjStore` | read, create, save (mtime guard), delete, move, promote |
| `Index` | `.sys/index.json`: build, refresh, resolve id, backlinks |
| `ChildQuery` | children of a container: tab filter, sort, paging |
| `TypeRegistry` | discovers `/types/*`, caches configs, resolves type -> `Type` |
| `Type` | one type config + factory for its renderers and controller |
| `Renderer` | abstract base; `ListRenderer`, `MobileRenderer`, `DetailRenderer`, `HeaderRenderer`, `FooterRenderer` subclasses per type. Header and footer have a working default in the base, so a type only overrides what it needs |
| `Menu` | builds the `...` dropdown: type entries from `config.menu` -> divider -> common entries |
| `Ajax` | request parsing, JSON response, error codes |

### 4.2 Request flow

```
browser (controller.js)
   |  fetch ajax.php?a=list&path=Kickoff%20process/02%20Workshop&tab=entries&offset=0
   v
ajax.php ------> Ajax::route()
                    |
                    +-- core action?  -> /ajax/list.php
                    +-- type action?  -> /types/<T>/ajax/<action>.php
                    v
              ObjStore + ChildQuery + TypeRegistry
                    v
              per-object renderer (list | mobile | detail)
                    v
              { html, meta }  ->  JS injects into the column
```

Rule (global config): big HTML rendered by PHP and delivered via ajax; only small fragments built in JS.

### 4.3 Core ajax actions

| Action | Params | Returns |
|---|---|---|
| `nav` | `path` | header info, tabs, parent path for the back arrow |
| `list` | `path, tab, offset, filter, sort` | rendered cards + `hasMore`, `total` |
| `detail` | `id` or `path` | rendered editor + `mtime` |
| `save` | `id, fields, body, mtime` | new `mtime`, re-rendered card |
| `create` | `parentPath, type, tab` | new object + card + editor |
| `delete` | `id, recursive` | removed ids, list of affected links |
| `link` | `parentPath, targetId` | created stub + card |
| `move` | `id, targetPath` | new path (id unchanged) |

`save` may return a changed `path` when the title was renamed — the client updates its hash state from the response. Upload is v2; resource files are put into the folder with the file system for now.

Errors: `409` stale mtime, `404` unknown id, `422` validation, `423` id collision.

---

## 5. UI

### 5.1 Desktop (>= 1200px)

```
+------------------+--------------------------------------------------+
| < Kickoff  [edit]| Workshop                             [AI]    [:] |  48px  app header
+------------------+---------------------------+----------------------+
| Entries Part Res+|                           | Chat 1        [+][v] |  36px  tabs | AI header
+------------------+                           +----------------------+
| [filter] [sort]  |   detail editor           |                      |  36px  tools (tab Entries)
+------------------+   (detail_renderer)       |       messages       |
|card              |                           |                      |
|card              |                           |                      |
|card              |                           |                      |
|card              |                           |                      |
|card              |                           +----------------------+
|[ load more ]     |                           | [ input ]      [ > ] |  composer
+------------------+---------------------------+----------------------+
| FOO              | FOO_2  (full content width)                      |  28px  footer
+------------------+--------------------------------------------------+
       320px                flex, min 480px            360px
   (280 .. 420, resiz.)                              (foldable)
```

Header and footer are **two segments**, not three: nav column | content area.

| Band | Nav segment | Content segment |
|---|---|---|
| App header | `<` back, container title, `[edit]` right | `NAME_2` from `header_renderer` (default: title of the selection), **`[AI]` and `[:]` pinned to the far right edge of the window** |
| Footer | list state, e.g. "12 entries" | `FOO_2` from `footer_renderer`, spanning mid **and** AI column, empty if the type defines none |

- Both bands share one design; only the content differs — that is exactly why they get their own renderers.
- The AI column sits **inside** the content area, right of the editor. `[AI]` folds it in and out and the editor resizes — it never overlays anything.
- The AI column's own chrome (small header, composer) lives between the two bands. The strip below the composer is the footer band continuing.
- `[:]` = vertical dots dropdown: type entries from `config.menu` -> divider -> common entries (rename, move, delete, reveal path).
- The editor may carry **its own tab row** below the app header if the type declares `detailTabs` — independent of the nav column tabs.
- The shell is built as a **generic column stack**, not as three hardcoded areas: `[nav] [content ...] [aux]`. Adding further foldable columns later (main app functions, left or right) is then a matter of registering a column, not a rewrite.

### 5.2 Breakpoints

**Desktop and tablet: the AI sidebar is never an overlay.** It is a column inside the content area and takes its own space; the editor gives that space up. **Mobile: everything is an overlay**, because there is only one column.

| Width | Layout | `[AI]` unfolds |
|---|---|---|
| >= 1200 | nav \| mid \| AI | content area splits, mid shrinks to min 480px |
| 900 .. 1199 | nav \| mid | content area splits; if mid would fall under 480px, the **nav column collapses** to an icon rail first |
| < 900 | single column | editor and AI slide in as **full-screen layers** over the list |

```
>= 1200, AI folded              >= 1200, AI unfolded
+------+---------------+        +------+---------+-----+
| nav  |     mid       |   ->   | nav  |   mid   | AI  |
+------+---------------+        +------+---------+-----+

900..1199, AI unfolded when mid would get too narrow
+--+------------+-----+
|[]|    mid     | AI  |   nav collapsed to a rail
+--+------------+-----+
```

### 5.3 Mobile (< 900px)

Editor and AI are **full width, full height layers** that slide in over the list — nothing is visible behind them, and the layer brings its own header and footer band.

```
   list layer                editor layer               AI layer
+---------------------+   +---------------------+   +---------------------+
| < Kickoff  [AI] [:] |   | < Workshop [AI] [:] |   | < Chat 1     [+][v] |
+---------------------+   +---------------------+   +---------------------+
| Entries Part Res  + |   |                     |   |                     |
+---------------------+   |   detail editor     |   |     messages        |
| [filter]            |   |                     |   |                     |
+---------------------+   |                     |   |                     |
| cell                | > |                     | > |                     |
| cell                |   |                     |   |                     |
| cell                |   |                     |   +---------------------+
| [ load more ]       |   |                     |   | [ input ]     [ > ] |
+---------------------+   +---------------------+   +---------------------+
| 12 entries          |   | FOO_2               |   | (empty)             |
+---------------------+   +---------------------+   +---------------------+

mobile_renderer.php draws the cells: compact, fewer fields, one tap target
```

- Stack: `list -> editor -> ai`. `<` in the header pops one layer; from the AI layer back returns to the editor.
- Each layer pushes a history entry, so the device back gesture / button works and a reload restores the top layer from the hash.
- Drilling into a sub container replaces the list layer instead of stacking, so the stack stays shallow.
| 12 entries                |
+---------------------------+
```

### 5.4 Navigation model

```
container  = object whose children the left list shows
selection  = object opened in the mid column

click on card
  |- leaf                -> select, render in mid column
  |- container, depth 1  -> unfold inline (one level, indented)
  |- container, depth >1 -> drill in: container = that object, "<" appears
  |- link                -> jump to target's real location, then select
[edit] in left header    -> select the container itself ("Current")
"<"    in left header    -> back to the parent container (hidden at root)
```

- No breadcrumb in v1 — the back arrow carries navigation. Revisit when we know where it fits.
- URL state (deep link, reload safe): `index.php#/<path>?tab=entries&sel=<id>`

### 5.5 Cards

```
|<------------- full column width ------------->|
|card                                           |   no outer padding on the list
|-----------------------------------------------|   1px separator, no gap
|card                                           |
```

- The list container has **no padding, margin or gap**; cards run edge to edge and are separated by a line only. Inner padding belongs to the card itself, so a type can render flush content (e.g. a cover strip).
- Per-type layout, may show many attributes -> left column must not go below 280px.
- Row actions: hover (desktop) / swipe or `...` (mobile): open, link, move, delete.

---

## 6. Design

- `/styles` — structure, layout, components (token based, no literal colors).
- `/themes/default.css` — token set only.

| Token group | Choice |
|---|---|
| Background | dark, layered: `--bg`, `--bg-2` (columns), `--bg-3` (cards, hover) |
| Text | `--fg`, `--fg-dim`, `--fg-faint` |
| Accent | amber / gold (`--accent`, `--accent-dim`) |
| Semantic | `--ok` green only for success, `--warn`, `--danger`; blue avoided |
| Lines | `--line`, `--line-soft` |
| Radius / space | `--r-1..3`, 4px spacing scale |
| Fonts | UI: Inter / system stack; monospace for ids and paths |

Rule: type CSS may only use tokens; no hardcoded colors, so a new theme file is enough to reskin everything.

---

## 7. Demo data

```
/data/demo
  Kickoff process/                Activity
    01 Preparation/               Activity  (promoted: has 2 Info children)
      -this.md
      Room booking.md             Info
      Checklist.md                Info
    02 Workshop/                  Activity
      -this.md
      Agenda.md                   Info
      Anna Meier (lnk).md         Link -> Persons/Anna Meier.md
      Ben Orlov (lnk).md          Link -> Persons/Ben Orlov.md
      slides.pdf                  resource
      slides.pdf.md               sidecar
    03 Follow up.md               Activity
  Persons/                        Group
    Anna Meier.md                 Person
    Ben Orlov.md                  Person
    Carla Vogt.md                 Person
  Notes/                          implicit Group (no -this.md)
    First idea.md                 Info
```

Covers: nesting, both file forms, implicit group, links, resource, spaces in names, load-more (pad `03 Follow up` with entries).

- `slides.pdf` + its sidecar `slides.pdf.md` are added by hand — there is no upload in v1.
- All demo objects carry `created` timestamps so the default sort is reproducible on any machine.

---

## 8. Build order

| M | Content | Done when |
|---|---|---|
| M1 | `/lib` core: Config, Db, ObjPath, FrontMatter, Obj, ObjStore + demo data | objects read from disk in a test script |
| M2 | TypeRegistry, Type, Renderer base, `Activity`/`Info` list renderers, `index.php` shell as a **generic column stack**, left column | list renders from the real DB |
| M3 | Tabs, tools/filter, ChildQuery, sort, load more, navigation + back arrow + hash routing | hierarchy fully browsable |
| M4 | Detail renderer, header/footer renderers, `...` menu, save/create/delete, rename, mtime guard | full CRUD on Activity and Info |
| M5 | Index, `Person`, `Link` stubs, participants tab, backlinks on delete | linking works both directions |
| M6 | `File` type, resources tab, sidecars (no upload) | manually placed resources are listed and openable |
| M7 | Mobile renderers, breakpoints, view stack, touch actions | usable on a phone |
| M8 | AI sidebar dummy (header, session dropdown, composer, no backend) | UI complete |
| M9 | Theme pass, polish, `Db::check()` health report | ships |

---

## 9. Deferred

| Topic | Decision |
|---|---|
| Upload | v2. Resource files are placed in the folder manually, incl. the demo data |
| Manual order, sort menu | v2, with its own mechanism — no `order` field is written in v1. v1 sorts newest first, nothing else |
| Index storage | `.sys/index.json` for now, possibly SQLite later. Only the `Index` class touches the format |
| Type `requires` | v2. The "requirements e.g. libraries" idea — no mechanism designed yet |
| Breadcrumb | Later. Placement open: dropdown on the left header title, footer band, or not at all |
| AI backend | Later. Sessions proposed in `/data/demo/.sys/ai/` |
| Search | Later. The index already carries title + type, so it is cheap to add |
| More foldable columns | Later, for main app functions (left or right). Prepared for by the generic column stack in §5.1, nothing built in v1 |
| Multi DB, users, auth | Out of v1 scope by decision |
