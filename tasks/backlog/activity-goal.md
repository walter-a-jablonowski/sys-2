# Goal, acceptance and progress

From the activity model (`tasks/resources/claude-recommendation.md`, §1–3): an activity changes a
current state into a goal state; unclear acceptance criteria make it never end.

## What

- Optional Activity fields: `goal` (what is true when done, how to check it) and `progress`
  (0–100 or a short "where it stands")
- Detail header: goal and progress bar when set, nothing when not (a small task stays a title)
- Progress of a parent can be derived from its children (the workflow status bar already counts
  done steps)

## Open questions

- Is a free text goal enough, or a checklist of acceptance criteria?
- Manual progress, derived only, or both?
