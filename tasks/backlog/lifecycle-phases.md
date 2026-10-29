# Lifecycle phases

From the activity model (§5) and the UI sample (phase bar in the detail header).

## What

- Optional field `phase`: initiate, define, plan, execute, close
- A phase bar in the detail header when set; nothing for small activities
- Phase and status are separate: status says whether it can move (open, waiting, done, …), phase
  says what kind of work is due
- Suggested phase from the data, e.g. no goal → define, goal but no children → plan,
  children running → execute, all children done → close
- Initiate: decide whether it is worth starting; show trigger, value and estimate side by side
  (see `activity-elements.md`, `activity-resources.md`), a "not now" keeps it in the backlog
- Control (§5) is not a phase of its own but runs during execute: drift from the plan, see
  `plan-baseline.md`
- Close: verify, deliver, release, learn (see `activity-result.md`, `activity-log.md`)

## Open questions

- Only a suggestion computed from the data, or a field the user sets?
