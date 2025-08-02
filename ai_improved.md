# Hierarchical Data Management System

## Overview

Build a mobile-first web application for managing hierarchical data entries of different types. The system allows users to create, edit, delete, and navigate through nested data structures with type-specific behaviors and validations.

## Core Architecture

### Technology Stack
- **Backend**: PHP with Symfony YAML 5.4 parser
- **Frontend**: HTML5, vanilla JavaScript, Bootstrap 5.3
- **Data Storage**: File-based using YAML front matter in Markdown files
- **Mobile-First**: Optimized for smartphone usage

### File Structure
```
/
├── index.php                 # Main entry point
├── ajax.php                  # AJAX request router
├── styles.css                # Global styles (prefer Bootstrap classes)
├── controller.js             # Global JavaScript utilities
├── config.yml                # Application configuration
├── composer.json             # Dependencies (Symfony YAML 5.4)
├── /ajax/                    # Global AJAX handlers (one file per function)
├── /lib/                     # Common classes and utility functions
├── /data/                    # Data instances (see Data Structure below)
└── /types/                   # Type definitions and renderers
    ├── def.yml               # Global field definitions
    └── /[TypeName]/          # Individual type folders
        ├── def.yml           # Type-specific definitions
        ├── type.php          # Type-specific PHP class (optional)
        ├── controller.js     # Type-specific JavaScript
        ├── read_only.php     # Read-only renderer
        ├── list.php          # List cell renderer
        ├── edit.php          # Edit form renderer
        ├── styles.css        # Type-specific styles (optional)
        └── /ajax/            # Type-specific AJAX handlers
```

## Type System

### Global Type Definition (`/types/def.yml`)
```yml
# Default fields available to all types
fields:
  time:         # Creation timestamp (YYYY-MM-DD HH:MM:SS)
    type: string
    required: true
  name:         # Human-readable name
    type: string
    required: true
  description:  # Detailed description (stored as markdown content)
    type: string
    required: false
  # Auto-generated fields (no explicit definition):
  # - id: Generated from name + user + timestamp
  # - type: Type identifier (optional override)
```

### Type Definition Structure (`/types/[TypeName]/def.yml`)
```yml
id: TypeName                    # Unique type identifier
time: YYYY-MM-DD HH:MM:SS      # Type creation timestamp
name: "Human Readable Name"     # Display name
derivedFrom: ParentTypeId      # Inheritance (optional)
description: |
  Detailed description of the type's purpose and usage

# Type identification patterns (regex)
typeIdentification: 
  - "^\\s*[1-5]\\s*-\\s*"     # Can be string or array of patterns
  # If front matter contains "type" field, it overrides pattern matching

allowedSubTypes: ["TypeId1", "TypeId2"]  # "*" for all, [] for none

fields:
  fieldName:
    type: string|int|float|bool|hyperlink
    required: true|false
    format: "regex_pattern"     # For string validation
    min: number                 # For numeric fields
    max: number
    step: number
    values:                     # For dropdown fields
      "Display Label": actualValue
```

## Required Types

### Activity Type
```yml
id: Activity
name: Activity
description: |
  Represents tasks, projects, or activities with priority and state tracking

typeIdentification:
  - "^\\s*[1-5]\\s*-\\s*"     # Active: priority number prefix
  - "^\\d{6}\\s*-\\s*"        # Closed: YYMMDD prefix

allowedSubTypes: ["*"]         # All types allowed as subtypes

fields:
  priority:
    type: int
    required: true
    min: 1
    max: 5
  state:
    type: string
    required: true
    values:
      "New": new
      "In Progress": progress
      "Done": done
  dueDate:
    type: string
    required: false
    format: "^\\d{4}-\\d{2}-\\d{2}$"
```

**Special Behaviors:**
- Priority number in filename must match priority field
- When priority changes, filename must be updated
- When state becomes "done", filename prefix changes to YYMMDD format

### Info Type
```yml
id: Info
name: Info
description: |
  Simple informational entries without complex state management

typeIdentification: 
  - ".*\\(\\s*i\\s*\\).*"     # "(i)" anywhere in filename

allowedSubTypes: []            # No subtypes allowed

# No additional fields beyond global defaults
```

### Apartment Type
```yml
id: Apartment
name: Apartment
derivedFrom: Activity
description: |
  Specialized activity type for apartment hunting with file management

typeIdentification:
  - "^\\s*[1-5]\\s*-\\s*"     # Same as Activity
  - "^\\d{6}\\s*-\\s*"
  # Must have "type: Apartment" in front matter to differentiate from Activity

allowedSubTypes: ["Activity", "Info"]

fields:
  state:
    type: string
    required: true
    values:
      "New": new
      "Current": current
      "Maybe": maybe
      "Done": done
  result:
    type: string
    required: false
  files_nr:
    type: string
    required: true
    format: "^\\d{4}$"         # 4 digits with leading zeros
    # Auto-generated from global counter in user/Default/types/Apartment/files_nr.json
  url:
    type: hyperlink
    required: false
```

**Special Features:**
- Camera integration for image capture
- Images saved as resource files in `/images` subfolder
- Supported image types: jpg, jpeg, png, gif, webp (hardcoded)
- Global files_nr counter with JSON persistence

## Data Structure

### Instance Storage Patterns

**Single File Instance:**
```
/data/(i) MyInfo.md           # Info type as single file
```

**Folder Instance:**
```
/data/2 - MyActivity/         # Activity type as folder
├── -this.md                  # Instance data file
├── resource_file.pdf         # Resource files
├── /images/                  # Group folder
│   └── image.jpg
├── /3 - SubActivity/         # Sub-instance
│   └── -this.md
└── (i) SubInfo.md           # Sub-instance as file
```

### Data File Format (`-this.md`)
```markdown
---
id: MyActivity-Default-250802142133
type: Activity
time: 2025-08-02 14:21:33
name: My Activity
priority: 2
state: progress
dueDate: 2025-08-10
---

This is the description content in markdown format.
It can contain multiple paragraphs and formatting.
```

### ID Generation Algorithm
1. Take the `name` field
2. Convert each word to title case (first letter uppercase)
3. Remove all non-alphanumeric characters
4. Append: `-{user}-{YYMMDDHHMMSS}`
5. Example: "My Test Activity" → "MyTestActivity-Default-250802142133"

### Configuration (`config.yml`)
```yml
dataFileName: "-this"         # Base name for instance data files
```

## User Interface

### Layout Structure
- **Fixed Header Bar**
  - Current level name (no breadcrumbs)
  - Actions dropdown (Edit, Delete, Settings)
- **Scrollable Content Area**
  - Read-only instance display (when viewing sub-level)
  - Tabbed interface: List, Resources

### Header Bar
```
[Current Level Name]                    [⋮ Actions]
                                         ├─ Edit
                                         ├─ Delete
                                         └─ ⚙️ Settings
```

### Content Tabs

#### List Tab
**Toolbar:**
- Sorting dropdown (by time, by name)
- Add button (right-aligned)

**Add Flow:**
1. Click Add → Type selection modal
2. Select type → Type-specific form appears
3. Fill form → Submit creates instance

**List Display:**
- Card-based layout
- Sorted by time (newest first)
- Each card shows type-specific renderer output
- Action buttons: Edit, Delete dropdown

**Type-Specific Renderers:**

*Activity List Item:*
```
[🏷️2] Activity Name                    [New]
```

*Info List Item:*
```
08-02                          Info Name
```

*Apartment List Item:*
```
Apartment Name [🔗]                   [Current]
2025-08-02                           0001
```

#### Resources Tab
- Lists resource files with icons, size, modified date
- Lists group folders
- Files with no matching a type pattern are resources

### Navigation
- Single click/touch: Navigate into sub-level
- Back button: Use browser/device back button
- No breadcrumb navigation

### Modals
- **Add Entry**: Type selection + type-specific form
- **Edit Entry**: Type-specific edit form
- **Delete Confirmation**: Simple yes/no dialog

## Technical Requirements

### Error Handling
- **PHP**: Catch exceptions, return JSON error responses
- **JavaScript**: Global error handler, user-friendly messages
- **AJAX**: Consistent error response format

### Validation
- **Client-side**: Real-time form validation
- **Server-side**: Complete validation before persistence
- **Type-specific**: Use field definitions for validation rules

### File Operations
- **Create**: Generate ID, create folder/file structure
- **Update**: Preserve ID, update content and filename if needed
- **Delete**: Remove files/folders, handle cascading deletes
- **Upload**: Handle image uploads for Apartment type

### AJAX Architecture
- **Router**: `ajax.php` forwards to specific handlers
- **Global handlers**: `/ajax/functionName.php`
- **Type handlers**: `/types/TypeName/ajax/functionName.php`
- **Response format**: JSON with success/error status

## Code Standards

### PHP Conventions
```php
// Indentation: 2 spaces
// Braces: Next line (except try-catch)
function myFunction( $arg1, $arg2 ) : bool
{
  if( $condition )
    return true;
  
  try {
    // code
  }
  catch( Exception $e ) {
    // error handling
  }
  
  return false;
}

// String building: prefer interpolation
$message = "Hello $name";
$message = "Value: {$this->value}";

// Spaces around conditions and operators
while( $condition )
if( ! in_array($item, $array) )
```

### JavaScript Conventions
```javascript
// 2-space indentation
// Prefer fetch API for AJAX
// Global error handling
// Event delegation for dynamic content
```

### CSS Conventions
- Prefer Bootstrap 5.3 classes
- Custom styles only when necessary
- Mobile-first responsive design
- Type-specific styles in type folders

### File Path Conventions
- Use relative paths (no `__DIR__`)
- Route everything through `index.php` or `ajax.php`
- Consistent path separators

## Security Considerations
- Input validation on all user data
- File type restrictions for uploads
- Path traversal proection
- No authentication system (current requirement)

## Demo Data Structure
```
/data/
├── (i) Welcome Info.md
├── /1 - Apartment Search/
│   ├── -this.md
│   ├── /2 - Downtown Apartment/
│   │   ├── -this.md
│   │   ├── /images/
│   │   │   ├── exterior.jpg
│   │   │   └── interior.jpg
│   │   └── (i) Viewing info.md
│   └── /3 - Suburb Apartment/
│       └── -this.md
└── /2 - Work Projects/
    ├── -this.md
    ├── /1 - Website Redesign/
    │   └── -this.md
    └── (i) Project Guidelines.md
```

## Implementation

### Apartment Special Features
1. **Files Counter**: 
   - JSON file: `user/Default/types/Apartment/files_nr.json`
   - Format: `{"lastNumber": 1}`
   - Auto-increment on creation

2. **Image Upload**:
   - Camera API integration
   - Save to `/images/` subfolder
   - Supported: jpg, jpeg, png, gif, webp

3. **URL Handling**:
   - Clickable links in list view
   - Validation for proper URL format

### Type Identification Priority
1. Check front matter `type` field
2. Match filename against `typeIdentification` patterns
3. Default to resource file if no match

### Responsive Design
- Bootstrap 5.3 grid system
- Mobile-first breakpoints
- Touch-friendly interface elements
- Optimized for smartphone screens

This improved specification maintains all original requirements while providing clearer structure, comprehensive examples, and detailed implementation guidance.
