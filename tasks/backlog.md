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
10. [Revisit the selection and priority encoding](backlog/revisit-selection-encoding.md): decide once
    the "what next" view and the agent exist

## Activity model

From the activity model, independent of the main line after step 3.

- [Optional fields shown only when used](backlog/optional-fields.md): first, the others build on it
- [Goal, acceptance and progress](backlog/activity-goal.md)
- [Activity log and lessons learned](backlog/activity-log.md)
- [Collapsible tree in the navigation column](backlog/nav-tree.md)

## Small fixes

Independent, any time.

- [Colors for tags a type adds](backlog/type-tag-colors.md)
- [Path lines keep tags only a type adds](backlog/path-line-type-tags.md)
- [Renaming a resource drops its extension](backlog/resource-rename-extension.md)
