# Activity status

Discussed 2026-10-29: status was a gap in the old gd system, to be added later.

## Why

Priority alone does not say what to do next: a blocked important task should not come before an
available secondary one. The "what next" view needs to know what can be done now.

## What

- Activities already have `status` in front matter (`open`, `running`, `done`, `types/Activity.yml`)
- Add at least `waiting` and `blocked`, maybe with a note on what it waits for
- Decide whether gd shows the status in the name as well (like the prio) or in front matter only

## Open questions

- Which states does the user actually need?
- Done activities: keep in place, hide in lists, or move to an archive?
