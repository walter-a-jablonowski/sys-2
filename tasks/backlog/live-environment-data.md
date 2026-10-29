# Live environment data

Discussed 2026-10-29. From the activity model (§2 environment): what acts on an activity from
outside. Today `environment` is only a list the user maintains (see `activity-elements.md`).

## Ideas

- Location (GPS) when the app runs on a phone with access to it: "What next" prefers activities
  tied to the place you are at (shopping list at the store, tasks at home)
- Activities get an optional place (`location`)
- Later other sources: weather, time of day, calendar

## Open questions

- Which activities need a place, and how is it entered (map, address, a Place entry to link)?
