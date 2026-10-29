# Activity status

Discussed 2026-10-29: status was a gap in the old gd system, to be added later.

## Why

Priority alone does not say what to do next: a blocked important task should not come before an
available secondary one. The "what next" view needs to know what can be done now.

## What

- Activities already have `status` in front matter (`open`, `running`, `done`, `types/Activity.yml`)
- Add at least `waiting`, maybe with a note on what it waits for. `blocked` can be derived from
  dependencies (see `activity-dependencies.md`)
- Add the end states of the activity model (`tasks/resources/claude-recommendation.md`, §3 stop
  conditions): `failed`, `abandoned` besides `done`
- Read and write the old gd state syntax in the name (see `gd-remaining-syntax.md`)

## Open questions

- Which states does the user actually need?
- Done activities: keep in place, hide in lists, or move to an archive?
- The model's lifecycle phases are a second axis besides the status, see `lifecycle-phases.md`
