# Renaming a resource drops its extension

Known limitation, listed in `CLAUDE.md`.

## Problem

Renaming `slides.pdf` to the title `Slides` (without `.pdf`) renames the file to `Slides`, so it
loses its extension and is no longer recognized as a PDF.

## What

- Keep the old extension when the new title has none, or show the extension as a fixed suffix
  next to the title input
