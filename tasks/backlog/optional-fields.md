# Optional fields shown only when used

From the UI sample (`tasks/resources/claude-recomm-ui-sample.md`): progressive disclosure, a tea
task is a title and a checkbox, a house project fills in everything.

## Why

The activity model has ten elements (agent, trigger, resources, constraints, …). As always-visible
fields they would be empty on almost every entry of the gd collection.

## What

- A type marks fields as optional, e.g. `optional: true`: hidden in the editor while empty
- An "+ add" row lists the hidden ones (chips like "+ Agent + Constraints"), a click shows the input
- Elements without structure stay text in the body (markdown headings), only what the app or the
  agent evaluates becomes a field

## Open questions

- Which of the ten elements earn a field? Likely: goal, progress, agent (participants exist as
  links), resources (time/money budget)
