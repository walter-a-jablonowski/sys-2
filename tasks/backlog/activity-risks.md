# Uncertainty and risks

From the activity model (§1: every activity has uncertainty; §6 uncertainty of the model; §7.1).

## Why

Uncertainty is part of the core definition but so far only a parameter of `activity-complexity.md`.
Estimates as single numbers hide it, and nothing records what could go wrong.

## What

- Estimates as a range: `estimate: '2d-5d'` besides a single value (see `activity-resources.md`);
  a wide range marks an uncertain activity
- Optional list `risks`: what could go wrong, each with likelihood/impact (low, mid, high) and
  what to do about it
- A risk can turn into an activity (a child that reduces it), or be closed when it is gone
- Hints: high risks without measure, uncertain activity without plan (see `activity-health-hints.md`)

## Open questions

- Is `risks` different enough from `assumptions` (see `activity-elements.md`)? An assumption is a
  belief that may be wrong, a risk an event that may happen
