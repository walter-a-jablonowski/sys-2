# "What next" view

Discussed 2026-10-29. Replaces the removed "Selected" list pill.

## Why

In the old gd database a recursive tool went through the whole tree and filtered out what to do next
for the coming months (mostly larger dev projects). Priorities and the local selection are relative
to the parent, so a flat list of prios says nothing across the tree.

## What

- A larger view in the content area, not a pill under the list
- Overall rank computed by the app, the user only decides between siblings:
  - read the priorities along the path as a tuple, best branch first: `(3, 3, 4)` before `(3, 4, 3)`
  - selection 2 counts as the best value within its parent
  - selection 1 shows the entry wherever it lives, as an override
- Leave out what cannot be done now: done, waiting (`activity-status.md`), blocked (`activity-dependencies.md`)
- Filters: subtree, depth, type

## Open questions

- How did the old recursive tool weight levels? Pure path order, or something else?
- Should deadlines (schedule) move entries up?
- Is this view also where the agent shows its proposals (see `ai-agent-maintenance.md`)?
