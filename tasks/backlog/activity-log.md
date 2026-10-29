# Activity log and lessons learned

From the activity model (`tasks/resources/claude-recommendation.md`, §3 control loop, §5 close).

## What

- A log per activity: dated entries (what was observed, decided, done), newest first
- Entry kinds of the control loop (§3): observe, decide, act; plus spent effort (adds to `spent`,
  see `activity-resources.md`)
- Automatic entries: status changed, goal redefined (with the old goal), estimate changed
- On close (done, failed, abandoned): ask "what did we learn?" and store it in the log, used by
  `estimate-learning.md`
- Storage: a section in the body or a sidecar file, so the gd files stay readable

## Open questions

- Body section (`## Log`) or separate file?
- Should done/abandoned automatically add a log entry?
