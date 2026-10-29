# Path lines keep tags only a type adds

Found 2026-10-29.

## Problem

The grey "where it lives" line under entries in Search, Recent and Pinned comes from
`ObjPath::label()`, which reads every segment with the database's own name fields. Values only a type
adds (e.g. `urgent` on Activities) stay in the text: "Kickoff process › Follow up (urgent)".

## What

- Read each segment with its type, e.g. from the index (it knows the type of every path)
