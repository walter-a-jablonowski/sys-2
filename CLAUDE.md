# Sys — project notes for agents

Markdown hierarchy viewer/editor. A **database is a folder** of markdown files with yml front
matter; every file and folder in it is an object. Single page app, single user (for now).

`README.md` is the source of truth for user-facing behaviour (objects, fields in the name,
config, keys, surprises). Read it before changing behaviour, and update it when you change some.

## Stack and running

- PHP 8, no framework. Composer: `symfony/yaml`, PSR-4 `Sys\` → `lib/`. Run `composer install` once
- Client: plain JS in `controller.js` (one IIFE, no build step), CSS in `styles/` + `themes/<theme>.css`
- Run: `php -S 127.0.0.1:8765` in the app root, open `index.php`
- No test suite, see **Verifying** below

## Code style

The user's global CLAUDE.md applies in full (2 spaces, `{` on the next line, `if( x )` spacing,
PHP alternative syntax in views, no `echo` for html, `'` strings, camelCase yml keys, keep comments).
Project habits on top of that:

- Every class and non-trivial method has a short docblock saying **why**, not what
- Comments that head a block get a blank line after them; short explanations sit at the line end
- Section comments inside classes: `// Internals`, `// Helpers for ...`, `// Parameters`
- Old comments refer to a design doc as `plan §2.3` etc. That doc is not in the repo; leave the references
- Errors: throw `Sys\Failure` — `notFound` 404, `conflict` 409 (stale mtime), `invalid` 422
  (pass `['field' => ...]` so the editor marks that input), `collision` 423, `new Failure(msg, 500)` for config errors

## Request flow

- `index.php` — first paint, server side: header, crumbs, nav column, first list page (`view/*.php`).
  Passes `window.SYS` (path, tab, dbName, create options, pageSize) to the client
- `ajax.php?a=NAME` → `lib/Ajax.php` → `ajax/NAME.php`. Handlers get `$app` (App) and `$ajax` (Ajax)
  and **return an array**, which becomes the JSON response. JSON POST bodies are merged into the params
  - `$ajax->obj()` loads the object by `path` (or `id`), `param()`, `intParam()`, `arrayParam()`
  - `?a=viewer&type=T&viewer=V&fn=F` → `type_viewer/T/V/ajax/F.php` (viewer actions)
- `file.php?path=...` — serves resource files out of the database (through `Db::clean()`)
- HTML always comes rendered from PHP (renderers or `View::render()` of `view/*.php`); the JS only moves
  it into place. Responses that change an object return the new `card` html and the `Frame` parts
  (`header`, `toolbar`, `viewers`, `footer`, `statusbar`)

## Services (`lib/App.php`, singleton via `App::boot($root)` / `App::get()`)

| Property   | Class            | Job |
|------------|------------------|-----|
| `config`   | `Config`         | `config.yml` (app) + `<db>/.sys/config.yml` (db, with `DB_DEFAULTS`) |
| `db`       | `Db`             | db root, `clean()` path safety (no `..`, no hidden segments), `check()` health report |
| `path`     | `ObjPath`        | **all naming rules** (see below) |
| `store`    | `ObjStore`       | the only code touching the db file system: get, children, walk, create, save, rename, move, delete, promote |
| `index`    | `Index`          | `<db>/.sys/index.json`: id → path, alt ids, backlinks, duplicates; `target($link)`; rebuilds itself when stale; hands out ids (`freeId()`, `ensureId()`) |
| `types`    | `TypeRegistry`   | `types/<Type>.yml` → `Type`; unknown type falls back to `defaultType` |
| `viewers`  | `ViewerRegistry` | `type_viewer/<Type>/<Viewer>/` → `Viewer`; active = user choice → type's `viewer` → `default` |
| `user`     | `UserSettings`   | `data/users/default/settings.yml` |
| `recent`, `pinned` | `ObjList` | id lists in `<db>/.sys/recent.json`, `pinned.json` |

Other: `ChildQuery` (children of a container for one tab: filter, links, sort, paging), `Search`
(fuzzy path search over the index), `Menu` (the ⋮ entries), `Frame`, `FrontMatter` (parse/build),
`NameFields`, `View`, `FileSize`.

## Objects and naming (the core rules)

- `Obj` (`lib/Obj.php`) is a plain data holder. **Title = file name**, never stored in front matter
  - `path` is relative to the db, without `.md`; `file` is the file holding it (`''` for a bare folder)
  - `data` = front matter minus reserved keys (`id`, `type`, `created`, `modified`, `target`)
  - `nameData` = fields parsed from the name, never written to front matter; `field()` checks it first
  - `isFolder`, `isLink`, `isResource`; `display()` gives a link's resolved target
  - `typeImplied`: the type came from the name or `defaultType`, not front matter. `ObjStore::save()`/`addId()`
    then write no `type`, so the name keeps deciding. `ajax/save.php` applies `__type` only when it differs
    from the type loaded (the dropdown sends it on every save) and answers `typeChanged`, which reloads the editor
- `X.md` and `X/<thisFile>.md` are the same object; adding a child promotes a leaf to a folder
- Every `.md` is an entry (front matter optional). Any other file → **resource** (type **File**), fields
  in sidecar `NAME.ext.md`. Folder without content file → type **Group**
- `lib/ObjPath.php` — content file of a folder (`thisFile`/`thisFileMatch`), link marker, `sanitize()`,
  `freeName()` (` 2`, ` 3`, …), `pathOfEntry()` (what is an object), `parseName()` (name → title + fields),
  `nameFor()` (title + fields → name, read-back validated, **a title is required besides the fields**),
  `label()` (path as readable titles)
- `lib/NameFields.php` — `nameFields` rules from `<db>/.sys/config.yml`: run in order, each takes its
  tokens out, the rest is the title. Keys: `field`, `match`, `format`, `values`, `role` (`id` | `link`), `list`, `label`
  - `values`: fixed strings (tags); found as `format` writes them, or where `match` puts `{value}`;
    `format()` refuses others, `fixedValues()` gives them for cards (`Renderer::tags()`) and the list filter
  - more keys: `at: 'start'` (leads the name, `flatten($tokens, $start)` writes it before the title),
    `type` (`typeOf()`: the name gives the type), `sort` (`rank()`, used by `ChildQuery::sort()`),
    `selected` (value → `global`|`local`, filled chip), `colors` (value → palette name, `.tag-<color>`,
    theme tokens `--tag-<color>`), `labels`
  - a type adds values via `nameFields: { tags: [...] }` in its yml, so parsing depends on the type:
    `ObjPath::fields($type)`, `parseName($name, $isResource, $type)`. Pass the type wherever an object is
    known; `ObjPath::label()` has none and reads the database's own
- Links: app-made links are `Title (lnk).md` with front matter `type: Link`, `target: <id>`. With a
  `role: link` name field, `Name (lnk-K12).md` links by alternative id. `App::renderer()` draws a link
  with its target's viewer, the Link viewer only when broken. `ajax/detail.php` opens the target
- Primary id is front matter `id`; Pinned/Recent/search/links use it. The alternative id (`role: id`) is
  unique across the db but only used for name links
  - new ids: `$app->index->freeId($type->newId($title))` — `newId()` alone can repeat within a minute
  - missing ids: `Index::build()` writes them into every `.md` entry (only the id line, file time kept,
    `ObjStore::addId()`); folders and resources get theirs via `Index::ensureId()` when pinned or linked
    to, or on first save — that creates their content file or sidecar

## Types and viewers (the extension points)

- `types/<Type>.yml` — **what** an object is: `fields` (nested via `fields:`, `tab:` puts a field on a detail tab),
  `tabs` of the nav column (`types`, `include: [inline, link]`), `create` per tab (`__link:Person` = add a link),
  `idPattern`, `icon`, `viewer`
- `type_viewer/<Type>/<Viewer>/` — **how** it is shown: `config.yml` (`label`, `detailTabs`, `header`, `footer`, `menu`),
  optional `<kind>_renderer.php` with class `<Type><Viewer><Kind>Renderer` extending the default
  (`ListRenderer`, `MobileRenderer`, `DetailRenderer`, `HeaderRenderer`, `ToolbarRenderer`, `FooterRenderer`,
  `StatusbarRenderer`, all extending `Renderer`), optional `styles.css`, `controller.js`, `ajax/<fn>.php`
- Action entries (menu, toolbar, status bar buttons) are arrays with `ajax` (+ `params`) or `js`
  (handler in `window.SYS_TYPES`); `Renderer::actionButton()` draws them
- A detail tab with id `raw` shows the front matter tab (`DetailRenderer::renderRaw()`)
- Reference implementation: `type_viewer/Activity/workflow` (toolbar + status bar + ajax actions)

## Client (`controller.js`)

- One `state` object and an `el` map of `[data-role]` elements; events delegated on `document` via `data-action`
- `api(action, params, method)` wraps fetch; errors carry `status` and `data` (e.g. `data.field`)
- Autosave 800 ms after typing; inputs with `data-rename` (title, name fields) save on blur/Enter only.
  `onSaveError`: 409 → reload dialog, 422 → marks `.field.has-error`
- State is mirrored in the hash (`#/A/B?tab=entries&sel=A/B/Entry`)

## Data and runtime files

- `config.yml` → `dbPath` (demo: `data/demo`), `theme`, `appName`. **Don't change it for tests**
- `<db>/.sys/` is hidden from requests: `config.yml` (db settings, documented example of `nameFields`),
  `index.json` (rebuildable, gitignored), `recent.json`, `pinned.json` (user state, gitignored)
- Index goes stale when any folder or `<db>/.sys/config.yml` is newer than the build; writes call `$app->index->invalidate()`
- Delete is permanent, there is no trash

## Verifying

```bash
# lint everything
for f in lib/*.php ajax/*.php; do php -l "$f" | grep -v "No syntax errors"; done

# demo database: object count and health issues (expect "27 []" for the untouched demo)
php -r "require 'vendor/autoload.php'; \$app = Sys\App::boot(getcwd()); \$app->index->build();
  echo count(\$app->index->all()), ' ', json_encode(\$app->db->check(\$app->store, \$app->index));"
```

End to end: copy the app without `data/` to a temp dir, create `data/<db>/.sys/config.yml` there (e.g. with
`nameFields`), point the copy's `config.yml` at it, run `php -S 127.0.0.1:8765` in the background and call
`ajax.php?a=health|detail|save|create|...` with curl. `save` takes a JSON POST:
`{"path": "...", "fields": {"title": "...", "body": "...", "<field>": "..."}}`.

## Known limitations

- Folders without content file and resources without sidecar have no id until pinned, linked to or
  saved: search finds them by path (`Index::withoutId()`), Recent does not list them until then
- A link file's own front matter/body is not reachable in the UI (opening shows the target)
- List name fields without `values` are edited as comma separated text
- `ObjPath::isLinkName()`/`stripMarker()` find the ` (lnk)` marker anywhere in the name
- Renaming a resource to a title without its extension drops the extension
