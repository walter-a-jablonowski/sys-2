# The ten elements as optional fields

From the activity model (`tasks/resources/claude-recommendation.md`, §2 and §8).

## Why

As text in the body the elements are readable but neither the app nor the agent can rely on them.
As optional fields they show up as chips only when used (see `optional-fields.md`).

## What

Optional Activity fields, each with its own task where it needs more than a field:

| Element       | Field                     | Task |
|---------------|---------------------------|------|
| Agent         | `agents` (links)          | `activity-agents.md` |
| Trigger       | `trigger` (text)          | here |
| Current state | `now`, `progress`         | `activity-goal.md` |
| Goal          | `goal`, `acceptance`      | `activity-goal.md` |
| Actions       | the children              | `atomic-actions.md` |
| Resources     | `estimate`, `spent`, …    | `activity-resources.md` |
| Constraints   | `constraints` (text/list) | here |
| Environment   | `environment` (text/list) | here |
| Model         | `assumptions` (text/list) | here, learning in `estimate-learning.md` |
| (Uncertainty) | estimate ranges, `risks`  | `activity-risks.md` |
| Feedback      | `progress`, the log       | `activity-goal.md`, `activity-log.md` |

- Constraints, environment and assumptions as lists, so single items can be checked off or flagged
  when they turn out wrong

## Open questions

- Lists or free text? Lists are easier for the agent, text is easier to write
