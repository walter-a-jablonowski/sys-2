# Safety net for bulk changes

Discussed 2026-10-29, prerequisite for the agent.

## Why

Delete is permanent and there is no trash. A re-prioritization run renames many files at once
(the prio is part of the name), so a mistake touches a large part of the tree.

## What

- One of:
  - the database folder under git, the app commits before and after an agent run
  - a change log in `<db>/.sys/` with undo (rename back, restore content)
- A trash for deletes, at least for changes made by the agent
- A dry run that lists all renames and edits before anything is written
