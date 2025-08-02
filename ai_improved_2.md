# Hierarchical Data Management System - Enhanced Specification

## System Overview

Create a mobile-first web application for managing hierarchical data entries of different types. The system allows users to navigate through nested levels of entries, with each entry capable of containing sub-entries, resource files, and group folders.

**Core Architecture:**
- **Backend**: PHP with file-based storage using YAML front matter in Markdown files
- **Frontend**: HTML with vanilla JavaScript and Bootstrap 5.3
- **Storage**: YAML/Markdown files with hierarchical folder structure
- **Dependencies**: Symfony YAML 5.4 parser via Composer

## File Structure Requirements

The application must implement the following exact directory structure:

```
/
├── ajax/                    # Global AJAX handlers (one file per function)
├── data/                    # All data instances (see Data Storage section)
├── lib/                     # Common classes and utility functions
├── types/                   # Type definitions and renderers (see Type System section)
├── index.php               # Main entry point
├── ajax.php                # AJAX request router
├── styles.css              # Global styles (prefer Bootstrap classes)
├── controller.js           # Global JavaScript functionality
├── config.yml              # Application configuration
└── composer.json           # Symfony YAML 5.4 dependency
```

### Configuration File (config.yml)
```yml
dataFileName: "-this"  # Name of data files in instance folders (extension: .md)
```

### Composer Dependencies (composer.json)
Must include Symfony YAML 5.4 for parsing YAML front matter.

## Type System Architecture

### Global Type Definition (/types/def.yml)

All types inherit these mandatory default fields:

```yml
# Default fields present in ALL instances
time:        # Creation timestamp (format: YYYY-MM-DD HH:MM:SS)
name:        # Human-readable name (string)
description: # Detailed description (string)

# Auto-generated fields (no explicit definition in def.yml)
id:          # Unique identifier (auto-generated from name, see ID Generation Rules)
type:        # Type identifier (optional, used for type disambiguation)
```

### Type Definition Structure (/types/TypeName/)

Each type must have the following structure:

```
/types/TypeName/
├── def.yml              # Type definition (required)
├── ajax/                # Type-specific AJAX handlers (optional)
├── type.php             # Type-specific PHP class (optional)
├── controller.js        # Type-specific JavaScript (optional)
├── read_only.php        # Read-only renderer (required)
├── list.php             # List cell renderer (required)
├── edit.php             # Edit form renderer (required)
└── styles.css           # Type-specific styles (optional)
```

### Type Definition Schema (def.yml)

```yml
id: TypeName                    # Unique type identifier (required)
time: YYYY-MM-DD HH:MM:SS      # Type creation timestamp (required)
name: "Display Name"           # Human-readable type name (required)
derivedFrom: ParentTypeId      # Parent type ID (optional)
description: |                 # Type description (required)
  Detailed description of the type's purpose and usage

# Type identification rules (required)
typeIdentification:            # String or array of regex patterns
  - "^\\s*[1-5]\\s*-\\s*"     # Match against file/folder names in /data
  # OR single string: "^\\s*[1-5]\\s*-\\s*"
  # If "type" field exists in front matter, it overrides pattern matching

allowedSubTypes:               # Array of allowed sub-type IDs (required)
  - "Info"                     # Specific type IDs
  - "*"                        # Use "*" for all types
  # Empty array [] for no sub-types allowed

# Type-specific fields (optional)
fields:
  fieldName:                   # Field identifier
    type: string               # Data type: string|int|float|bool|hyperlink
    required: true             # Validation: true|false
    format: "regex_pattern"    # String validation regex (optional)
    min: 1                     # Numeric constraints (optional)
    max: 100                   # Numeric constraints (optional)
    step: 1                    # Numeric step value (optional)
    values:                    # Dropdown options (optional)
      "Display Label": value   # Key-value pairs for dropdowns
```

### ID Generation Algorithm

When creating new instances, generate unique IDs using this exact algorithm:

1. **Start with the name field value**
2. **Convert to TitleCase**: First character of each word uppercase
3. **Remove non-alphanumeric characters**: Keep only letters and numbers
4. **Append unique suffix**: `-{User}-{Timestamp}`
   - User: Currently hardcoded as "Default"
   - Timestamp: Format YYMMDDHHMMSS (2-digit year, month, day, hour, minute, second)

**Example**: Name "My Test Entry" → ID "MyTestEntry-Default-240802195607"

## Required Type Implementations

### Type: Activity

```yml
id: Activity
name: Activity
description: |
  Represents tasks, projects, or activities with priority levels and states.
  Can contain any type of sub-entries.

typeIdentification:
  - "^\\s*[1-5]\\s*-\\s*"     # Active activities (number = priority)
  - "^\\d{6}\\s*-\\s*"        # Closed activities (YYMMDD closure date)

allowedSubTypes: ["*"]         # All types allowed as sub-entries

fields:
  priority:
    type: int
    required: true
    min: 1
    max: 5
    values:
      "1 - Highest": 1
      "2 - High": 2
      "3 - Medium": 3
      "4 - Low": 4
      "5 - Lowest": 5
  
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
    format: "^\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}$"
```

**Special Behavior:**
- When priority changes, the folder/file name prefix must be updated to match
- When state becomes "done", rename with closure date prefix (YYMMDD format)

**List Renderer Requirements:**
- Left side: Priority badge + name
- Right side: State badge
- Priority badge colors: 1=red, 2=orange, 3=yellow, 4=blue, 5=gray

### Type: Info

```yml
id: Info
name: Info
description: |
  Simple informational entries with basic fields only.
  Can't contain sub-entries.

typeIdentification: [".*\\(\\s*i\\s*\\).*"]  # "(i)" anywhere in name

allowedSubTypes: []            # No sub-types allowed

# No additional fields beyond defaults
```

**List Renderer Requirements:**
- Left side: Date (MM-DD format)
- Right side: Name
- Use muted text styling

### Type: Apartment

```yml
id: Apartment
name: Apartment
derivedFrom: Activity
description: |
  Apartment search entries with specialized fields for tracking
  apartment hunting progress and file management.

typeIdentification:
  - "^\\s*[1-5]\\s*-\\s*"     # Same as Activity
  - "^\\d{6}\\s*-\\s*"        # Same as Activity
# Must have "type: Apartment" in front matter to distinguish from Activity

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
    # Auto-generated using global counter (see File Counter System)
  
  url:
    type: hyperlink
    required: false
```

**List Renderer Requirements:**
- First line: Name (clickable if URL present) + State badge (right-aligned)
- Second line: Date (YYYY-MM-DD) + files_nr (right-aligned)
- Use smaller, muted text for second line

**Special Features:**
- **File Counter System**: Global auto-incrementing counter for files_nr
  - Storage: `/user/Default/types/Apartment/files_nr.json`
  - Format: `{"lastId": 1234}`
  - Increment on each new apartment creation
- **Camera Integration**: Image upload functionality for smartphone cameras
  - Supported formats: JPEG, PNG, WebP (hardcoded list)
  - Storage location: `/data/{parentPath}/{apartmentPath}/images/`
  - AJAX handler: `handleFileUpload` in apartment-specific ajax folder

## Data Storage System

### Storage Patterns

**Single File Instances** (for simple entries):
```
/data/filename.md
```

**Folder Instances** (for entries with sub-content):
```
/data/foldername/
├── -this.md              # Main data file
├── sub-entry.md          # Sub-instances
├── resource-file.txt     # Resource files
└── group-folder/         # Group folders
```

### Data File Format (-this.md)

All data files use YAML front matter with Markdown content:

```markdown
---
id: GeneratedId-Default-YYMMDDHHMMSS
type: TypeName
time: YYYY-MM-DD HH:MM:SS
name: Entry Name
priority: 3
state: new
dueDate: 2024-12-31 23:59:59
---

This is the description content in Markdown format.
It can contain multiple paragraphs and formatting.
```

**Critical Rules:**
- All fields except `description` go in YAML front matter
- `description` field content goes in Markdown body
- File extension must be `.md`
- YAML front matter is mandatory even if only containing default fields

### Content Classification

**Instance Files/Folders**: Identified by:
1. Matching any `typeIdentification` regex pattern from any type, OR
2. Having a `type` field in YAML front matter (takes precedence)

**Resource Files**: Any files that don't match instance identification rules
- Displayed in "Resources" tab
- Can be any file type
- Show file size, modification date, and appropriate icons

**Group Folders**: Any folders that don't match instance identification rules
- Displayed in "Resources" tab
- Can contain sub-instances and resources
- Show folder icon and item count

### Example Data Structure

```
/data/
├── (i) Welcome.md                    # Info instance (single file)
├── 2 - Apartment Search/             # Activity instance (folder)
│   ├── -this.md                      # Main data file
│   ├── 3 - Downtown Apartment/       # Apartment sub-instance
│   │   ├── -this.md                  # Apartment data
│   │   ├── (i) Viewing Info.md       # Info sub-instance
│   │   ├── contract.pdf              # Resource file
│   │   └── images/                   # Group folder
│   │       ├── exterior.jpg          # Resource files
│   │       └── interior.jpg
│   └── 4 - Suburb Apartment/
│       └── -this.md
└── 1 - Work Projects/                # Activity instance
    ├── -this.md
    ├── 2 - Website Redesign.md       # Activity sub-instance (single file)
    └── 260801 - Old Project/         # Closed activity
        └── -this.md
```

## User Interface Specification

### Technology Requirements
- **Framework**: Bootstrap 5.3 (mobile-first approach)
- **Responsive Design**: Optimized for smartphones, functional on all devices
- **Layout**: Fixed header + scrollable content area

### Header Bar (Fixed Position)

**Left Side**: Current level name
- Base level: "Start"
- Sub-levels: Name of current instance
- No breadcrumb navigation (use browser back button)

**Right Side**: Actions dropdown
- **Edit**: Edit current instance (disabled on start page)
- **Delete**: Delete current instance (disabled on start page)
  - Show confirmation dialog
  - Navigate back after deletion
- **Settings**: Gear icon (placeholder, no functionality)

### Content Area (Scrollable)

#### Read-Only Instance Display
- Rendered using type-specific `read_only.php`
- Hidden on start page
- Shows current instance data in formatted view

#### Tab Interface

**Tab 1: List**

*Toolbar:*
- **Sort Dropdown**: Options for "By Time", "By Name", etc.
- **Add Button** (right-aligned): Opens add modal

*Add Modal Workflow:*
1. Type selection dropdown (populated from `/types/` folder)
2. Dynamic form loading based on selected type
3. Form validation according to type definition
4. Success/error feedback

*List Display:*
- **Layout**: Bootstrap cards
- **Sorting**: Default by time (newest first)
- **Rendering**: Type-specific `list.php` renderers

*List Item Actions:*
- **Click/Touch**: Navigate to sub-entries
- **Edit Button**: Open edit modal
- **Delete Dropdown**: Confirmation + deletion

**Tab 2: Resources**

*Display:*
- Resource files with icons, size, modification date
- Group folders with icons and item counts
- Click to download files or navigate into folders

### Modal Dialogs

**Add/Edit Modals:**
- Dynamic form generation from type `edit.php`
- Client-side validation based on type definition
- AJAX submission with error handling
- Success feedback and list refresh

**Delete Confirmation:**
- Clear warning message
- Confirm/Cancel buttons
- Progress indication during deletion

## AJAX Architecture

### Global AJAX Router (ajax.php)

Central dispatcher that routes requests to appropriate handlers:

```php
// Route format: ?action=handlerName&type=TypeName
// Global handlers: /ajax/handlerName.php
// Type-specific: /types/TypeName/ajax/handlerName.php
```

### Required AJAX Handlers

**Global Handlers** (`/ajax/`):
- `saveEntry.php`: Create/update instances
- `deleteEntry.php`: Delete instances and cleanup
- `loadEditForm.php`: Load type-specific edit forms
- `getResourcesAtPath.php`: List resources and group folders

**Type-Specific Handlers** (`/types/TypeName/ajax/`):
- `handleFileUpload.php`: File upload (Apartment type)
- `getNextFileNumber.php`: File counter management (Apartment type)

### AJAX Response Format

**Success Response:**
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { /* optional response data */ }
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "Detailed error message for user display"
}
```

### Error Handling Requirements

**PHP Error Handling:**
- Catch all exceptions and return JSON error responses
- Log errors for debugging while showing user-friendly messages
- Validate all input data before processing

**JavaScript Error Handling:**
- Global error handler for AJAX failures
- User-friendly error messages in modals/alerts
- Graceful degradation for network issues

## Implementation Requirements

### Code Style Standards

**PHP Conventions:**
- **Indentation**: 2 spaces (no tabs)
- **Braces**: Opening brace on next line
- **Exception**: try-catch blocks use same-line braces
```php
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
}
```

**Spacing Rules:**
- Conditions/loops: `while( $condition )`
- Function definitions: `function name( $args ) : returnType`
- Function calls: `func( $arg1, $arg2 )` (multiple args) or `func($arg)` (single arg)
- Negation: `if( ! condition )`
- String preference: Single quoes unless dynamic content needed

**JavaScript/HTML:**
- Same indentation and spacing rules
- Use PHP alternative syntax for HTML rendering
- Prefer `fetch()` API for AJAX calls

### Path Management
- **No `__DIR__` usage**: Use relative paths exclusively
- **Routing**: All requests through `index.php` or `ajax.php`
- **File access**: Relative to base

### Validation Strategy
- **Simple validation**: Avoid third-party validation libraries
- **Type-based validation**: Use type definition constraints
- **Client + Server**: Validate on client and server sides for security and UX

### Class Design
- **Prefer classes over functions** for application-specific code
- **Utility functions allowed** for common tasks
- **Clear separation of concerns**: DataManager, TypeManager, etc.

## Demo Data Requirements

Create realistic demo data demonstrating all features:

**Required Demo Instances:**
1. **Welcome Info**: Basic info entry explaining the system
2. **Apartment Search Activity**: 
   - Multiple apartment sub-instances with different states
   - Resource files (contracts, images)
   - Info sub-entries (viewing info, contact info)
3. **Work Projects Activity**:
   - Active and closed project activities
   - Nested sub-tasks with various priorities
   - Mixed file and folder instances

**File Structure Example:**
```
/data/
├── (i) Welcome to the System.md
├── 2 - Apartment Search/
│   ├── -this.md
│   ├── 3 - Downtown Loft/
│   │   ├── -this.md (state: current)
│   │   ├── (i) Viewing info.md
│   │   ├── contract_draft.pdf
│   │   └── images/
│   │       ├── living_room.jpg
│   │       └── kitchen.jpg
│   ├── 4 - Suburb House/
│   │   └── -this.md (state: maybe)
│   └── 260725 - Rejected Apartment/
│       └── -this.md (state: done)
└── 1 - Work Projects/
    ├── -this.md
    ├── 2 - Website Redesign/
    │   ├── -this.md (state: progress)
    │   ├── 1 - Design Mockups.md
    │   └── 3 - Content Migration.md
    └── 260720 - Old Website/
        └── -this.md
```

## Security and Authentication

**Current State**: No authentication system implemented
**File Access**: Direct file system access (ensure proper path validation)
**Input Sanitization**: Required for all user inputs
**File Upload Security**: Validate file types and sizes for apartment images

## Testing and Validation

### Functional Testing Checklist

**Type System:**
- [ ] All three types (Activity, Info, Apartment) create correctly
- [ ] Type identification works for all patterns
- [ ] Field validation enforces type definitions
- [ ] Inheritance works properly (Apartment from Activity)

**Data Management:**
- [ ] YAML front matter parsing works correctly
- [ ] File and folder instances create properly
- [ ] Resource files and group folders display correctly
- [ ] ID generation follows algorithm exactly

**User Interface:**
- [ ] Mobile-responsive design works on all screen sizes
- [ ] Navigation between levels functions properly
- [ ] Add/Edit/Delete operations work correctly
- [ ] AJAX calls handle errors gracefully

**Special Features:**
- [ ] Apartment file counter increments correctly
- [ ] Camera image upload works on mobile devices
- [ ] Priority changes update folder names
- [ ] State changes to "done" trigger proper renaming

### Error Scenarios to Test

1. **Invalid YAML front matter**: Graceful error handling
2. **Missing type definitions**: Clear error messages
3. **File permission issues**: User-friendly feedback
4. **Network failures**: AJAX timeout handling
5. **Invalid file uploads**: Type and size validation
6. **Concurrent access**: File locking considerations

## Success Criteria

The implementation is complete when:

1. **All three required types** (Activity, Info, Apartment) are fully functional
2. **Hierarchical navigation** with proper back-button support
3. **CRUD operations** (Create, Read, Update, Delete) work for all types
4. **Mobile interface** is fully responsive and touch-friendly
5. **File management** handles resources and group folders correctly
6. **Special features** (file counter, image upload) function as specified
7. **Demo data** demonstrates all system capabilities
8. **Error handling** provides clear feedback for all failure scenarios
9. **Code quality** meets all specified style and architectural requirements
10. **Performance** is acceptable on mobile devices with reasonable data sets

The system should feel intuitive for smartphone users while maintaining full functionality across all device types.
