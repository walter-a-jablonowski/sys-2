# Resources and value

From the activity model (§2 resources, §3 stop conditions, §6 resource scarcity).

## Why

The model stops an activity when resources run out or the remaining cost exceeds the value. Without
numbers for both the app cannot warn, and estimates cannot improve.

## What

- Optional fields: `estimate` (effort, e.g. `3h`, `2d`), `spent` (effort so far), `budget` (money),
  `cost` (money so far), `value` (what it is worth, e.g. 1–5 or money)
- Parents sum the estimates and spent effort of their children, shown next to their own
- Spent effort can be added from log entries (see `activity-log.md`)
- Remaining = estimate − spent, or re-estimated by the user

## Open questions

- Units: hours/days only, or also free resources (material, attention)?
- Value: a scale or money? Relative to the parent like the priority?
