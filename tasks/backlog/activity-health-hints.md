# Hints from the model's failure causes

From the activity model (§7: most failures are failures of one element).

## What

Checks that point at likely trouble, shown on the activity and collected in the stale review:

- running without goal or acceptance criteria (§7.6: the activity never ends)
- no progress or log entry for a long time while running (§7.5: slow feedback)
- irreversible or high-uncertainty activity without plan (no children, phase not past plan, §7.1)
- many agents without dependencies or schedule (§7.2: coordination)
- estimate missing on a large activity, or far exceeded (§7.4)
- assumptions not reviewed for long (§7.4: wrong model)

- Thresholds in `<db>/.sys/config.yml`, each check can be turned off
- Needs the fields of `activity-elements.md`, so the checks come one by one with them
