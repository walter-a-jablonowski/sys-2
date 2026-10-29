# Who acts (agents)

From the activity model (§2 agent, §6 number of agents, §7.2 coordination).

## What

- Optional field `agents`: links to Person entries (ids), the ones doing the work, separate from the
  Participants tab (everyone involved)
- Default: the user, so a single-user collection needs nothing here
- "What next" filtered by agent: what is next for me, what waits for someone else
- An activity waiting for another agent counts as `waiting` (see `activity-status.md`)
- Later, with more than one user: an agent is a user, assignments and notifications

## Open questions

- Reuse the Participants tab with a role (`agent`, `informed`, …) instead of a new field?
