# Remaining gd syntax (states and more)

Discussed 2026-10-29: the app should read and write the old gd database completely, then grow from
there as one app instead of two.

## Why

Every upgrade (status, dependencies, goal, the agent) is only useful when it works on the real
collection. Anything the app cannot read in a gd name is lost or shown as part of the title.

## What

- Collect what gd still writes that the app does not understand yet, e.g. the syntax for states
- Express each one as `nameFields` rules in `<db>/.sys/config.yml` where possible, so the app
  itself stays free of gd special cases
- Map gd states onto the Activity `status` values (see `activity-status.md`)
- **Check database** on the real collection: names the rules cannot read

## Open questions

- What exactly was the old state syntax, and which other markers exist?
