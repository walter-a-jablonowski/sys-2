
( duplicate from grid-view )

> Me: Think deeply from first principles: make a generalized (abstract) model that descripes any kind of activity of any complexity fully - from trivial things to huge projects.

Claude:

# A general model of activity

## 1. Core definition

An **activity** is an **agent** using **resources** and **actions** to change a **current state** into a **goal state**, under **constraints**, in an **environment** with **uncertainty**, while using **feedback** to correct itself.

Every activity, from "drink water" to "build an airport", has this same structure. Only the size of the parameters changes.

## 2. The ten elements

| # | Element | Question it answers | Tea | House |
|---|---|---|---|---|
| 1 | Agent | Who acts? | You | Owner, architect, 30 workers |
| 2 | Trigger | Why start now? | Thirst | Need for space, financing approved |
| 3 | Current state (S₀) | What is true now? | Cold water, no tea | Empty plot |
| 4 | Goal state (S₉) + acceptance criteria | What must be true at the end, and how do we check it? | Hot tea in cup | Approved, habitable building |
| 5 | Actions | What changes can the agent make? | Boil, pour, steep | Design, excavate, build, inspect |
| 6 | Resources | What is consumed? | 5 min, electricity | 2 years, money, material, attention |
| 7 | Constraints | What is not allowed or limited? | Kettle capacity | Law, budget, physics, order of work |
| 8 | Environment | What acts on the activity from outside? | Almost nothing | Weather, prices, authorities, suppliers |
| 9 | Model | What the agent believes about cause and effect | "Water boils in 3 min" | Plans, estimates, schedules |
| 10 | Feedback | How is progress measured? | Look at the kettle | Inspections, budget reports, milestones |

## 3. The core mechanism: the control loop

Every activity runs this loop until a stop condition is met:

1. **Observe** the current state.
2. **Compare** it with the goal state → this gives the **gap**.
3. **Decide** the next action, using the model.
4. **Act**, consuming resources.
5. Return to step 1.

**Stop conditions:**
- Gap = 0 → success
- Resources exhausted → failure
- Expected cost of remaining work > value of goal → abandon
- Goal changed → redefine and continue

(This is the same structure as the TOTE unit in psychology, PDCA in quality management, and a feedback controller in engineering.)

## 4. Recursion: why one model covers all sizes

Every action is itself an activity with the same ten elements.

- "Build house" contains "build wall", which contains "lay brick", which contains "lift brick".
- Decomposition stops at an **atomic action**: an action the agent can perform directly, with a predictable result, without further planning.

So any activity is a **tree of activities** (what) plus a **dependency graph** (in which order). A trivial activity is a tree with one node. A large project is a deep, wide tree with many dependencies.

## 5. Lifecycle phases

Each node in the tree passes through these phases. For trivial activities they take seconds and happen unconsciously; for large ones they are separate, explicit work.

1. **Initiate** – trigger occurs, decide whether the activity is worth starting.
2. **Define** – fix the goal state and acceptance criteria.
3. **Plan** – decompose, order, assign agents and resources.
4. **Execute** – run the control loop.
5. **Control** – compare progress with plan, adjust plan or goal.
6. **Close** – verify the result, deliver it, release resources, record what was learned (update the model).

## 6. What makes an activity complex

Complexity is not a different kind of activity. It is the value of these parameters:

| Parameter | Low | High |
|---|---|---|
| Depth/width of decomposition | 1 step | Thousands of tasks |
| Number of agents | 1 | Many organizations |
| Duration | Seconds | Years |
| Uncertainty of the model | Result is known | Result is unknown or unknowable |
| Goal clarity | Fixed and precise | Vague or changing |
| Interdependence | Steps are independent | Steps block each other |
| Reversibility | Easy to undo | Impossible to undo |
| Resource scarcity | Plenty | Very tight |
| Environment influence | Stable | Changing |

## 7. Consequences derived from the model

1. **Planning exists because of uncertainty and irreversibility.** If actions are cheap and reversible, trial-and-error is cheaper than planning.
2. **Coordination exists because of multiple agents.** Each added agent adds communication links (n agents → up to n(n−1)/2 links).
3. **Management is itself an activity.** Its goal state is "the other activity proceeds correctly". It adds its own resource cost.
4. **Most failures are failures of one element:** unclear goal, wrong model, missing feedback, underestimated resources, or ignored environment.
5. **Feedback speed limits correction speed.** Slow feedback → errors grow before they are detected.
6. **Unclear acceptance criteria make the stop condition undefined.** The activity then never ends or ends arbitrarily.
7. **Learning = updating the model.** The closing phase is what makes the next similar activity cheaper.

## 8. Compact formal form

```
Activity = (
  agent,          // who acts
  trigger,        // why it starts
  S0,             // current state
  goal,           // target state + acceptance test
  actions,        // available operations
  resources,      // consumed inputs
  constraints,    // limits and rules
  environment,    // external influences
  model,          // belief about cause and effect
  feedback,       // measurement of state
  children: [Activity],   // decomposition (empty = atomic)
  dependencies: Graph     // order between children
)

run(A):
  while not A.goal.accepted(observe()):
    if stop_condition(A): return failure/abandon
    a = decide(A.model, observe(), A.goal)
    execute(a)            // a is atomic, or run(a) recursively
  update(A.model)
  return success
```

**Summary:** one structure (ten elements), one mechanism (the feedback loop), one scaling rule (recursion). Trivial and huge activities differ only in the parameter values in section 6.


> Me: Make an ASCII diagram

Claude:

## 1. Structure: the ten elements

```
+=========================== ENVIRONMENT ===========================+
|  External influences: weather, prices, other agents, laws, chance |
|                                                                   |
|  TRIGGER ---> [ AGENT + MODEL ] <--------------------------+      |
|                       |                                    |      |
|                       | chooses                            |      |
|                       v                                    |      |
|  RESOURCES ----> [ ACTIONS ] <--- CONSTRAINTS               |      |
|  (consumed)            |          (limits)                  |      |
|                       | changes state                      |      |
|                       v                                    |      |
|               [ CURRENT STATE ] == gap ==> [ GOAL STATE ]  |      |
|                       |                           |        |      |
|                       v                           v        |      |
|               [ FEEDBACK: measure state, compare to goal ]-+      |
|                                                                   |
+===================================================================+
```

## 2. Mechanism: the control loop

```
              +-----------------+
     +------->|  1. OBSERVE     |
     |        |  current state  |
     |        +--------+--------+
     |                 |
     |                 v
     |        +-----------------+
     |        |  2. COMPARE     |--- gap = 0 ------->[ SUCCESS ]
     |        |  with goal      |--- goal changed -->[ REDEFINE ]
     |        +--------+--------+
     |                 | gap > 0
     |                 v
     |        +-----------------+
     |        |  3. DECIDE      |--- no resources -->[ FAILURE ]
     |        |  uses model     |--- cost > value -->[ ABANDON ]
     |        +--------+--------+
     |                 |
     |                 v
     |        +-----------------+
     |        |  4. ACT         |
     |        |  uses resources |
     |        +--------+--------+
     |                 |
     +-----------------+
```

## 3. Scaling: recursion (every action is an activity)

```
                             [ Build house ]                        <- level 0: the activity
                                    |
              +---------------------+---------------------+
              |                     |                     |
         [ Design ]          [ Build walls ]       [ Build roof ]   <- level 1
                                    |
              +---------------------+---------------------+
              |                     |                     |
        [ Lay row 1 ]         [ Lay row 2 ]            [ ... ]      <- level 2
              |
       +------+------+
       |             |
   [ Lift ]      [ Place ]                                          <- level 3: atomic (no children)
```

Order between nodes (dependency graph):

```
Design --> Foundation --> Walls --> Roof ------> Final inspection
                            |                           ^
                            +--> Electrics -------------+
```

## 4. Lifecycle of each node

```
INITIATE --> DEFINE --> PLAN --> EXECUTE --> CLOSE
                         ^          |          |
                         |          v          |
                         +-------CONTROL       |
                                               v
                                    update MODEL (learning)
```

A trivial activity uses all four diagrams with minimal values: one node, one loop pass, phases done without conscious effort.
