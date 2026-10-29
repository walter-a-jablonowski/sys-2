# Revisit the selection and priority encoding

Discussed 2026-10-29. Not now: decide once the "what next" view and the agent exist.

## Current state

- Name digit: 1 = selected everywhere, 2 = selected within the parent, 3–5 = priority within the parent
- The app already models selection and priority as two fields (`keepIn: 'priority'`), the digit is only
  how gd writes them, so another encoding is a config change plus a rename run

## Questions for later

- Does a manual global selection (1) still pay off once the app computes the overall rank,
  or does it become a rare override?
- Is a cap on the number of selections useful (soft warning)?
- Are three priority levels still enough when the agent sets them?
