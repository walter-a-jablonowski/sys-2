# Review of stale entries

Discussed 2026-10-29: automating the detection of stale items makes sense.

## Why

The old process was a full manual pass over the whole tree every few months, which stopped working
when the tree grew too large. Pointing at what is likely outdated turns that into a short routine.

## What

- A list of entries that probably need a decision, e.g.:
  - selected (1 or 2) for a long time
  - untouched for months while important
  - a priority left in front matter while the name shows another one (renamed outside the app)
  - Activities without priority (already in **Check database** as `missingField`)
- Thresholds in `<db>/.sys/config.yml`
- Later the agent can work through this list and propose changes (see `ai-agent-maintenance.md`)
