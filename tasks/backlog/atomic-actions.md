# Atomic actions (ready to do)

From the activity model (§4): decomposition stops at an atomic action, one the agent can do directly
without further planning.

## Why

A leaf of the tree is not always atomic: "Plan the trip" without children still needs planning.
"What next" should offer things you can just do.

## What

- Optional flag, e.g. `needsPlanning: true`, or derived: a leaf without the flag counts as atomic
- "What next" lists atomic actions; a leaf that needs planning shows up as "plan this" instead
- The agent can propose a breakdown of activities that need planning

## Open questions

- Flag the exception (needs planning) or the rule (ready)?
