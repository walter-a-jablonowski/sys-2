# Observing from outside (the "observe" step)

Discussed 2026-10-29. From the activity model (§3 step 1, §2 feedback): today every observation is the
user's input. Maybe obsolete, kept until we find something worth observing.

## Ideas

- Interaction with external agents: another agent (or service) reports progress or a result for an
  activity, e.g. a coding agent finishes a task, a booking is confirmed
- Inputs that update `now`, `progress` or `status` (see `activity-goal.md`), e.g. a git repository,
  a calendar, mail replies
- Each observation lands in the activity log as an "observe" entry (see `activity-log.md`)

## Open questions

- What is worth observing at all? Which external agents would talk to the app?
- An inbound ajax endpoint with a key per agent?
