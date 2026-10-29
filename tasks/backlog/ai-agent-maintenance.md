# AI agent that maintains the collection

Discussed 2026-10-29: the long-term direction, developed step by step.

## Why

Large trees were the problem of the old system: sorting and keeping nested prios and selections
current was too much manual work. An agent that understands what the activities mean can do this.

## What

- Fix problems in the old gd collection, e.g. missing front matter, missing priorities of selected
  Activities, names the rules cannot read
- Sort the tree, update priorities and selections from rules plus its understanding of the content
- Works through the app's own code (`ObjStore`, ajax handlers), never raw file edits, so naming
  rules, ids and links stay consistent
- Proposes changes the user reviews and accepts, at least until it has earned trust
- Needs `bulk-change-safety.md` first

## Steps (first idea)

1. Read-only: report what it would change
2. Apply accepted proposals one by one
3. Scheduled runs on the stale review list
