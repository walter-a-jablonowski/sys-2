# Hierarchical Data Management System

This is a PHP-based hierarchical data management system that allows users to organize and manage entries of different types in a hierarchical structure.

## Features

- Hierarchical data organization
- Multiple entry types (Activity, Info, Apartment)
- Web-based interface with Bootstrap 5.3
- File-based storage using YAML and Markdown
- Type-based system with configurable fields and renderers
- Camera image upload for Apartment type
- No authentication system (single user)

## Project Structure

```
/
├── ajax/                  # Common AJAX functions
├── data/                  # Data storage
├── lib/                   # Common PHP libraries
├── types/                 # Type definitions and renderers
│   ├── Activity/          # Activity type
│   ├── Info/              # Info type
│   └── Apartment/         # Apartment type
├── user/                  # User-specific data
│   └── Default/           # Default user
│       └── types/
│           └── Apartment/ # Apartment-specific data
├── vendor/                # Composer dependencies
├── index.php              # Main entry point
├── ajax.php               # AJAX handler
├── config.yml             # Configuration
├── composer.json          # Composer configuration
├── controller.js          # Common JavaScript code
└── styles.css             # Common styles
```

## Installation

1. Install PHP and Composer
2. Run `composer install` to install dependencies
3. Configure your web server to serve the project directory
4. Access the application through your web browser

## Usage

- Navigate through the hierarchical data structure using the list view
- Add new entries using the "Add" button
- Edit entries using the edit button
- For Apartment entries, you can capture images using the device camera

## Entry Types

### Activity

- Priority (1-5)
- State (new, progress, done)
- Optional due date

### Info

- Basic information entry
- No special fields

### Apartment

- Derived from Activity
- Special fields: state (new, current, maybe, done), result, files_nr, url
- Camera image upload capability

## Data Format

Entries are stored as either single files or directories with a `-this.md` file containing the entry data in YAML front matter format.

Example:

```markdown
---
type: Activity
name: My Activity
id: MyActivity-Default-250801174200
time: 2025-08-01 17:42:00
priority: 2
state: progress
dueDate: 2025-08-15 10:00:00
---

This is a sample activity entry.
```

## Configuration

The `config.yml` file contains global configuration options:

```yaml
dataFileName: "-this"  # Name of the data file used in data directories
```

## Development

### Coding Conventions

- PHP backend with HTML/JS frontend
- 2-space indentation
- Specific bracket style (opening brace on next line)
- Bootstrap 5.3 mobile-first UI
- Error handling for PHP and JavaScript errors

### Date Formats

- Time fields: YYYY-MM-DD HH:MM:SS
- ID generation: YYMMDDHHMMSS

### Type Identification

Types are identified via regex patterns defined in each type's `def.yml` file.

## License

This project is licensed under the MIT License.
