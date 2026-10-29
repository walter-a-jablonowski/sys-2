# Agent memory (how the agent learns)

Discussed 2026-10-29. The backlog gives the agent data to read (estimates vs. spent effort, lessons,
wrong assumptions, hints), but the agent itself forgets everything between runs: a language model
does not change by use. Its "inner model" has to be written down.

## What

- **Agent memory** in `<db>/.sys/agent/`: separate text files (markdown) the agent builds quietly
  over time, without asking: the user's behaviour and preferences, and rules it derived, e.g.
  "the user keeps old project notes, don't propose deleting them", "renovation tasks take about
  twice the estimate", "prio 3 under 'Work' beats prio 3 under 'Home'"
- The user can read and correct the files any time; the agent never hides why it proposes something
  (it names the memory entry it relied on)
- **Proposal feedback**: every proposal with the user's answer (accepted, rejected, changed) and an
  optional reason. The strongest signal for what the user wants; rules in the memory come from here
- **Retrieval**: find similar activities (closed ones with lessons, estimates) for a new one; needs
  search beyond titles, e.g. over goal and body, later embeddings
- **Evaluation**: check its own predictions afterwards, e.g. did the entries it ranked high in
  "What next" get done, were its estimates closer than the user's
- Each run starts by reading the memory and ends by updating it

## Open questions

- One memory per database or per user?
- Files by topic (preferences, estimates, sorting) or one file per observation?
