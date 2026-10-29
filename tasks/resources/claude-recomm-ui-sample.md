
( duplicate from grid-view )


Asked Claude shortly about how UI would look like

## UI concept

The model says small and huge activities have the same structure, so the UI uses **one screen for any activity at any level**. It only shows more detail when the activity needs it.

### Layout (single page app)

```
+------------------+------------------------------------------+
| TREE             | BREADCRUMB: House > Walls > Row 1        |
|                  +------------------------------------------+
| v Build house    | [Initiate|Define|Plan|Execute|Close]     |
|   > Design   ✓   |                                          |
|   v Walls    ●   | GOAL       Walls stand, inspected        |
|     • Row 1  ●   | NOW        Row 1 half done     GAP ███░░ |
|     • Row 2      | -----------------------------------------|
|   > Roof         | + Agent  + Resources  + Constraints ...  |
|                  | -----------------------------------------|
|                  | CHILDREN (list | graph)                  |
|                  | LOG (feedback / control loop)            |
+------------------+------------------------------------------+
```

### Main parts

1. **Tree (left):** the recursion. You can expand, collapse, drag to reorder and add children inline. Each node shows its status: open, running, done, failed or abandoned.
2. **Detail panel (right):** the selected activity. It looks the same on every level.
   - **Header:** a lifecycle bar showing the current phase.
   - **Core:** goal, acceptance criteria, current state, and the gap as a progress bar.
   - **Ten elements:** collapsed chips that you open only when needed. A tea task is just a title and a checkbox; a house project fills them all in.
3. **Children view:** you can switch between a list and a dependency graph. Blocked items are greyed out.
4. **Log:** the control loop made visible. Each entry is observe → decide → act, with the resources used. When you close an activity, it asks "what did we learn?", which updates the model.
5. **Stop conditions:** warnings appear when resources run out or the cost gets bigger than the value. They offer to redefine the goal or abandon.

### Principles

- **Progressive disclosure:** complexity in the UI grows only as the activity's complexity grows.
- **Same component at every level:** one recursive view, no special screens.
- **Technical:** PHP renders the static layout on page load. JavaScript renders the tree and detail panel and saves changes with fetch calls to `ajax.php`. Each activity is stored as a YAML file in `/data`.
