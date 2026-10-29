# Backlog

One file per task in `backlog/`. Background: the activity model in
[resources/claude-recommendation.md](resources/claude-recommendation.md) and the UI sample in
[resources/claude-recomm-ui-sample.md](resources/claude-recomm-ui-sample.md).

## Main line

In order, each task builds on the ones before it.

1. [Remaining gd syntax (states and more)](backlog/gd-remaining-syntax.md): the app reads and writes
   the whole old collection, one app instead of two
2. [Priority in databases without a prio name field](backlog/priority-without-name-field.md):
   chip and sorting for the priority wherever it is stored, the rank needs it in every database
3. [Activity status](backlog/activity-status.md): waiting, failed, abandoned, the gd state syntax
4. [Dependencies between activities](backlog/activity-dependencies.md): what is blocked by what
5. ["What next" view](backlog/what-next-view.md): overall rank computed across the tree, without
   what is blocked or waiting
6. [Review of stale entries](backlog/stale-review.md): list of entries that probably need a decision
7. [Time-limited selection](backlog/time-limited-selection.md): rare, an ended selection shows up in
   the stale review
8. [Safety net for bulk changes](backlog/bulk-change-safety.md): undo, trash, dry run, needed before
   the agent writes anything
9. [AI agent that maintains the collection](backlog/ai-agent-maintenance.md): fixes, sorts and
   prioritizes, step by step
10. [Agent memory (how the agent learns)](backlog/agent-memory.md): rules, proposal feedback,
    retrieval, self-evaluation
11. [Agent finishes tasks itself](backlog/agent-does-tasks.md): runs the loop for (sub-)tasks
    instead of only organizing them
12. [Revisit the selection and priority encoding](backlog/revisit-selection-encoding.md): decide once
    the "what next" view and the agent exist

## Activity model

The rest of the activity model, after step 3 of the main line. In order where one needs another.

1. [Optional fields shown only when used](backlog/optional-fields.md): the mechanism for all fields below
2. [The ten elements as optional fields](backlog/activity-elements.md): overview, trigger,
   constraints, environment, assumptions
3. [Goal, acceptance and progress](backlog/activity-goal.md): goal, current state, gap
4. [Activity log and lessons learned](backlog/activity-log.md): the control loop made visible
5. [Who acts (agents)](backlog/activity-agents.md)
6. [Resources and value](backlog/activity-resources.md): estimate, spent, budget, value
7. [Stop-condition warnings](backlog/stop-condition-warnings.md): needs 3, 6 and the status
8. [Lifecycle phases](backlog/lifecycle-phases.md): optional phase and phase bar
9. [Atomic actions (ready to do)](backlog/atomic-actions.md): feeds "What next"
10. [Learning from closed activities](backlog/estimate-learning.md): needs 4 and 6
11. [Complexity of an activity](backlog/activity-complexity.md)
12. [Uncertainty and risks](backlog/activity-risks.md): estimate ranges, risk list
13. [Plan baseline and milestones](backlog/plan-baseline.md): drift from the plan (control phase)
14. [Closing: verify, deliver, release](backlog/activity-result.md): the close phase as one dialog
15. [Hints from the model's failure causes](backlog/activity-health-hints.md): grows with the fields above
16. [Collapsible tree in the navigation column](backlog/nav-tree.md): UI, independent
17. [Quick actions in lists](backlog/list-quick-actions.md): done checkbox, add a child inline
18. [Observing from outside](backlog/external-observation.md): external agents and inputs, maybe obsolete
19. [Live environment data](backlog/live-environment-data.md): location (GPS) and more

## Principles

From the activity model (§7). Most failures come from one element of an activity. The app turns them
into hints (`activity-health-hints.md`), the agent gets them in its prompt (`ai-agent-maintenance.md`).

1. Plan where actions are uncertain or can't be undone; where they are cheap and reversible, trying
   is cheaper than planning
2. Every further agent adds coordination effort
3. Managing an activity is an activity of its own and costs resources too
4. Typical causes of failure: unclear goal, wrong assumptions, missing feedback, resources
   underestimated, environment ignored
5. Slow feedback lets errors grow before they are noticed
6. Without acceptance criteria an activity never ends, or ends arbitrarily
7. Learning means updating the model: closing an activity makes the next similar one cheaper

## Not planned

- Coordination of many agents at the scale of organizations (§7.2): looks obsolete for this app
- One yml file per activity (UI sample): we keep markdown with front matter, readable as gd

## Small fixes

Independent, any time.

- [Colors for tags a type adds](backlog/type-tag-colors.md)
- [Path lines keep tags only a type adds](backlog/path-line-type-tags.md)
- [Renaming a resource drops its extension](backlog/resource-rename-extension.md)
