# Agent finishes tasks itself

Discussed 2026-10-29. From the activity model (§3 control loop, §8 `run()`): today the app records
the loop, the user runs it. Later the agent can run it for some activities.

## What

- The agent takes over (sub-)tasks it can do with its tools instead of only organizing or querying
  the database, e.g. research, drafting a text, collecting files, summarizing the children
- It works through the loop like a person: observe, decide, act, and writes each step to the
  activity log (see `activity-log.md`), with the effort spent
- Results go into the activity (body, children, resources); the user accepts them and closes it
- Marked as agent work: `agents` contains the agent (see `activity-agents.md`)
- Stop conditions apply to it as well: a budget per task (time, cost of model calls)

## Open questions

- Which kinds of tasks first?
- Which tools may it use (web, mail, files outside the database)? Each needs its own permission
- Needs `agent-memory.md` and `bulk-change-safety.md`
