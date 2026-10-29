# Priority in databases without a prio name field

Found 2026-10-29 while making the priority required for every database.

## Problem

In `data/demo` the priority lives in front matter only. Cards show no chip for it and lists are not
sorted by it: both come from name fields (`fixedValues()`, `NameFields::rank()`).

## What

- Show the priority as a chip and sort by it, wherever it is stored
- Probably: chip colors, labels and sort order defined once for the field, not per name rule
  (today the gd config repeats the labels of `types/Activity.yml`)
