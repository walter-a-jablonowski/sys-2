# Hierarchical Data Management System

A flexible, mobile-first web application for managing hierarchical data with configurable types.

## Features

- **Type-based System**: Define custom data types with specific fields and validation
- **Hierarchical Structure**: Navigate through nested data levels
- **Mobile-first Design**: Optimized for smartphones with BS 5.3
- **File-based Storage**: Uses YAML/Markdown files for data persistence
- **Custom Renderers**: Type-specific list, read-only, and edit views
- **Image Upload**: Camera support for apartment oic
- **Error Handling**: User-friendly error display for PHP and JavaScript

## Built-in Types

### Activity
- Priority (1-5)
- State (New, In Progress, Done)
- Due Date (optional)
- Allows all sub-types

### Info
- Basic type
- No special fields
- No sub-types allowed

### Apartment
- State (New, Current, Maybe, Done)
- Files Number (auto-incrementing 4-digit)
- URL (optional link)
- Result
- Camera pic upload
- Allows Info sub-types

## Installation

1. Place files in web server directory
2. Ensure PHP 7.4+ is available
3. Set write permissions for `data/` and `types/` directories
4. Access via web browser

## Usage

- **Add Entry**: Click the "+Add" button to create new entries
- **Edit Entry**: Single-click any entry to edit it
- **Navigate**: Double-click entries to view their sub-entries
- **Back Navigation**: Use the back button to go up levels
- **Resources**: Switch to Resources tab to see files and folders
- **Sort**: Use the sort dropdown to order entries by time or name

## File Structure

```
/types/
  def.yml                 # Global field definitions
  /Activity/
    def.yml              # Type definition
    list.php             # List cell renderer
    read_only.php        # Read-only view
    edit.php             # Edit form
  /Info/
    ...
  /Apartment/
    files_nr.json        # Auto-increment counter
    ...

/data/
  /2 - MyActivity/
    -this.md             # Instance data file
    /3 - SubEntry/
      -this.md
      some_image.jpg     # Resource files
      /images/           # Group folders
```

## Technical Details

- **Backend**: PHP with custom type and data managers
- **Frontend**: HTML, JavaScript, BS 5.3
- **Storage**: File-based with YAML front matter
- **Routing**: All requests through index.php and ajax.php
- **Date Format**: YYYY-MM-DD HH:MM:SS for timestamps
- **ID Format**: TitleCase-Default-YYMMDDHHMMSS

## Customization

To add new types:
1. Create `/types/YourType/` directory
2. Add `def.yml` with type definition
3. Create renderer files: `list.php`, `read_only.php`, `edit.php`
4. Define `typeIdentification` regex for file naming

## Error Handling

The system includes comprehensive error handling:
- PHP errors are logged and displayed to users
- JavaScript errors show toast
- Form validation with field-specific messages
- File upload validation for image types
