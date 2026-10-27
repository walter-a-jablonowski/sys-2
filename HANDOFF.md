# Handoff: extending the database layer

Notes for the next agent (Claude Code) continuing this work. Everything below is committed
(`9abbd51 Improving db`, `4ef5712 Ectending db`). `README.md` documents the user-facing
behaviour, read it first, especially **Objects**, **Fields in the name** and **Config**.

## Project in one paragraph

PHP 8 app (no framework, Symfony Yaml via composer, PSR-4 `Sys\` → `lib/`), plain JS client
(`controller.js`). A database is a folder of markdown files with yml front matter
(`config.yml` → `dbPath`, demo: `data/demo`). Every file/folder is an object (`lib/Obj.php`).
Ajax: `ajax.php?a=NAME` → `ajax/NAME.php`, handlers get `$app` and `$ajax`.
Types: `types/<Type>.yml`; viewers: `type_viewer/<Type>/<Viewer>/`.
Code style: the user's global CLAUDE.md applies (2 spaces, `{` on next line, `if( x )` spacing,
PHP alternative syntax in views, no echo for html, comments kept).

## Where the naming rules live

- `lib/ObjPath.php` — all naming rules: content file of a folder, link marker, sanitizing,
  free names, `parseName()` (name → title + fields), `nameFor()` (title + fields → name,
  with read-back validation), `label()` (path as readable titles)
- `lib/NameFields.php` — the rules of `nameFields`: parse, normalize, format, roles
- `lib/ObjStore.php` — reads/writes objects; `readName()` fills title, `nameData`, `altId`,
  `altTarget`; `rename($obj, $title, $nameValues)` renames for title or name field edits
- `lib/Index.php` — `<db>/.sys/index.json`: ids, alternative ids, backlinks, duplicates;
  `target($link)` resolves a link by `target` (id) or `altTarget` (id2)

## What we changed

### 1. README: objects documented
New **Objects** section: every file/folder is an object, leaf vs folder, Group/File/Link types,
what is not an object (hidden, content file, sidecar, shadowed `X.md`).

### 2. Configurable folder content file (`thisFileMatch`)
- `thisFile` (default `-this`) is the name the app writes; `thisFileMatch` is an extra regex on
  the name without `.md`, e.g. `thisFile: '- DESC'`, `thisFileMatch: '-\s*DESC.*'`
- One convention per database (user's decision), always `.md`
- Several matches in one folder: first in byte order wins, **Check database** reports the rest
  (`extraThisFile`)
- Creating/renaming an entry to a matching name is refused (`ObjPath::freeName`)

### 3. Fields parsed from names (`nameFields`)
User's requirements (from the Q&A):
- token anywhere in the name, brackets optional: `id-K12`, `(id-K12)`, `#K12` → field `id2`
- `My file (lnk-K12).md` is a link to the entry whose id2 is `K12`; such a link has its own
  free title, front matter and body; opening it opens the target (as before)
- tags are **fixed strings**: `(best)` is a tag, `(some string)` is just title text
- the system must be generic: rules defined in `<db>/.sys/config.yml`, all fields are removed
  to get the title
- id2 is unique across the database; `id` in front matter stays primary (Pinned/Recent use it)
- name fields are editable, editing renames the file (via `format`)
- links created by the app keep the old form (`Title (lnk).md` + front matter `target`)

Implementation:
- Rules run in order, each removes its tokens before the next; a token must stand alone
  (start/blank before, blank/end after; enforced by the engine, not the user regex)
- Rule keys: `field`, `match` (group 1 = value), `format` (`{value}`; none = read-only),
  `role` (`id` | `link`), `list`, `label`; validated in the `NameFields` constructor
- `Obj::$nameData` holds name fields separately from `$data` (front matter);
  `Obj::field()` checks `nameData` first. Name fields are never written to front matter
- Resources: fields come from the part before the extension, the title keeps the extension
- Rename keeps tokens as written but re-orders them behind the title in **rule order**
- `ObjPath::nameFor()` re-parses the new name; mismatch → 422: an edited field that does
  not read back blames that field, otherwise the title
- `ajax/save.php`: splits name fields from front matter fields, only changed ones rename,
  refuses a duplicate id2
- `DetailRenderer::fields()`: name fields go after the title unless the type declares them;
  inputs carry `data-rename` (save on blur/Enter like the title, see `controller.js`),
  read-only ones are `disabled`
- `Db::check`: `duplicateAltId`, broken links by id2

### 4. Bug fix: intact links were drawn as broken
`App::renderer()` used the link's own type (Link → "broken link" badge). Now: resolve the link
if needed, draw with the target's viewer, the Link viewer only for broken links.

### 5. Smaller
- `Db::pathLabel()` moved to `ObjPath::label()` (strips name fields from segments)
- Index is stale when `<db>/.sys/config.yml` is newer than the build
- Demo config documents `thisFileMatch` and a commented `nameFields` example
  (`nameFields: []` active, demo data unchanged)

## Known limitations / ideas not done

- Objects with only an id2 (no front matter id) are not in search, Recent or Pinned
- A link file's own front matter/body is not reachable in the UI (opening shows the target).
  On the user's task list: "Link files currently show the target in the UI (own front matter
  values hidden) — ask AI how to solve this"
- A name consisting only of tokens gets an empty title; saving the editor then fails the
  "title cannot be empty" check
- List name fields are edited as a comma separated text input, no multi-select for fixed tags
- `ObjPath::isLinkName()`/`stripMarker()` treat the ` (lnk)` marker anywhere in the name

## Open items from the user's task list (at the time of writing)

- Add AGENTS.md or CLAUDE.md with project information (which one is better?)
- Do we have tags? (yes now, via `nameFields`, but no type-level tag concept)
- Copy the demo folder to `data/gd_demo` using the "gd" syntax (`- DESC` content files,
  `id-`/`#` ids, `lnk-` links, fixed tags) — config example is in `data/demo/.sys/config.yml`
- "Make gd compatible — ensure that - DESC files are always md" (done: content files must be `.md`)
- Prio field for the Activity type
- Are non-activity folders just objects or groups? (folders without content file are Group)
- What is the current Info type? (`types/Info.yml`: plain text entry, the `defaultType`)
- Anything missing from gd?

## How to verify

No test suite. What was used:

```bash
# lint
php -l lib/NameFields.php

# demo database: object count, health issues, link rendering
php -r "require 'vendor/autoload.php'; \$app = Sys\App::boot(getcwd()); \$app->index->build();
  echo count(\$app->index->all()), ' ', json_encode(\$app->db->check(\$app->store, \$app->index));"
```

For name fields: a throwaway database in the temp dir with a `.sys/config.yml`, built with
`new Config('none.yml', ...)`, `Db`, `ObjPath`, `ObjStore`, `Index` directly. End to end: copy the
app (without `data`) to a temp dir, set its `config.yml` `dbPath`, run `php -S 127.0.0.1:8765`
in the background and call `ajax.php?a=health|detail|save` with curl (save takes JSON POST:
`{"path": ..., "fields": {...}}`). Don't change the user's `config.yml` for tests.
