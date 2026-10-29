# Optional fields shown only when used

From the UI sample (`tasks/resources/claude-recomm-ui-sample.md`): progressive disclosure, a tea
task is a title and a checkbox, a house project fills in everything.

## Why

The activity model has ten elements (agent, trigger, resources, constraints, …). As always-visible
fields they would be empty on almost every entry of the gd collection.

## What

- A type marks fields as optional, e.g. `optional: true`: hidden in the editor while empty
- An "+ add" row lists the hidden ones (chips like "+ Agent + Constraints"), a click shows the input
- Which fields of the activity model exist: see `activity-elements.md`
