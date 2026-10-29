# Plan baseline and milestones

From the activity model (§5 control: compare progress with plan, adjust plan or goal; §2 feedback:
milestones).

## Why

Schedule and estimate are overwritten when they change, so the app cannot show how far an activity
has drifted from its plan. Control needs the original plan next to the current one.

## What

- When an activity starts (status running, or phase execute): keep a baseline of `estimate`,
  `schedule.start` and `schedule.end` in front matter (`baseline: { … }`)
- Show the drift: "2 weeks late", "estimate +60 %", in the detail header and the stale review
- Re-baselining is an explicit action with a log entry (see `activity-log.md`)
- Milestones: children marked as milestone (`milestone: true`) with a date; the parent shows the
  next one and whether it is late

## Open questions

- Baseline set automatically at start or by the user?
