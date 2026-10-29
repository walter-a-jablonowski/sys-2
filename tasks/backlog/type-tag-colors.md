# Colors for tags a type adds

Found 2026-10-29.

## Problem

A type adds fixed values via `nameFields: { tags: ['urgent'] }` in `types/<Type>.yml`, but cannot
give them a chip color: `urgent` is always gold, the default.

## What

- Allow colors next to the values in the type, e.g. `nameFields: { tags: { urgent: 'red' } }`,
  merged in `ObjPath::fields($type)` like the values
