# Dependencies between activities

From the activity model (`tasks/resources/claude-recommendation.md`, §4: tree plus dependency graph).

## Why

The tree says what belongs together, not in which order. "What next" needs to know what is blocked:
an important task waiting for another one is not next.

## What

- Front matter `after: [<id>, …]`: this activity starts when those are done (ids, so renames and
  moves don't break it, like links)
- The index keeps the reverse direction, like backlinks ("blocks: …")
- An activity whose `after` entries are not all done counts as blocked (derived, no manual status)
- Lists grey out blocked entries; the children of an activity can be shown as a graph besides the list
- **Check database**: ids that don't exist, cycles

## Open questions

- Only between siblings, or across the whole tree?
- Field name: `after`, `dependsOn`, `blockedBy`?
