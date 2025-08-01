
I am making an app that can be used to manage a list of entries of different types. From each entry the user can navigate to a list of sub entries (hierarchical).

## File struct

- /ajax:  commonly used ajax functions one file per function (ajax call forwarded by ajax.php), see also type specific ajax calls below
- /data:  see below
- /lib:   commonly used classes and functions
- /types: see below
- index.php
- styles.css: commonly used styles (see also type specific ajax calls below), prefer bootstrap classes
- config.yml:
  ```yml
  dataFileName: "-this"  # name of the data file used in data (in principle can have any extension, but practically we use "md")
  ```
- composer.json: for Symfony Yaml 5.4
- controller.js: commonly used s code (see also type specific js code below)

## Types

Different types of entries can be defined in a file structure like

- /types
  - def.yml global definition, contains: definitions of default data fields of instances of all types
    - time (string, YYYY-MM-DD HH:MM:SS)
    - name (string)
    - description (string)
    - each instance also has a field "id" which isn't explicitly defined here, derive it from the field "name"
      when an instance is created:
        - convert each word to first character uppercase
        - then remove all non alpha numeric chars
        - add a unique user and date behind, seperated by hyphen: "-Default-YYMMDDHHMMSS"
          (the user currently is "Default" only)
    - each instance may also have a field "type" which isn't explicitly defined here
  - /MyType
    - def.yml type definition, contains: definitions of special data fields for this type with basic validation
    - /ajax:         type specific ajax functions one file per function (ajax call forwarded by ajax.php)
    - type.php:      type specific PHP code as class (if any)
    - controller.js: type specific js code
    - ready_only.php: renders a read only version of the instance data
      - this is a php file that uses PHP's alternative syntax for rendering HTML
    - list.php:  cell renderer used to render the list cell
    - edit.php:  renderer for the edit form
    - styles.css: type specific styles (if any), prefer bootstrap classes
  - ...

Type definition:

```yml
id:   MyType        # type unique id
time:               # created time YYYY-MM-DD HH:MM:SS

name: My Type       # type name
description: |
  type description

typeIdentification: "^\\s*[1-5]\\s*-\\s*"  # identify the type of a file or folder in /data (match this against name)
                                           #   this may also be an array of multiple type identification strings
                                           #   if no string or if a field "type" is in the front matter use this as the type
allowedSubTypes:    ["Info"]               # list of type ids of allowed sub types for the list, "*" for "all", empty array for none

fields:             # special fields for this type
  myField:          # name of the field
    type:           # string, int, float, bool, hyperlink
    required:       # true | false
    format:         # regex (for strings)
    min:            # min, max, step (for numbers)
    max: 
    step: 
    values:         # for dropdown
      "My label": someValue
  ...
```

## Initial types

- Type "Activity"
  - default fields: see above
  - special fields:
    - priority (int), values: 1-5
    - state (dropdown), values: new (default), progress, done
    - dueDate (optional)
  - typeIdentification
    - either: "^\\s*[1-5]\\s*-\\s*" for an active activity (the number is the same as the priority and must be updated in the file/folder name when the priority changes)
    - or: "^\\d{6}\\s*-\\s*" for an activity that was closed ("d{6}" is the date when the activity gets state closed)
  - allowedSubTypes: all ("*")
  - list renderer:
    - left aligned:  priority as badge and name
    - right aligned: state
  - edit renderer: form in modal for editing all fields

- Type "Info"
  - default fields: see above
  - no special fields
  - typeIdentification: ".*\\(\\s*i\\s*\\).*" somewhere in the file or folder name
    - make a regex that matches this
  - allowedSubTypes: none
  - list renderer:
    - left aligned:  date (format MM-DD)
    - right aligned: name
  - edit renderer: form in modal for editing all fields

We also define these types for my search for a new apartment:

- Type "Apartment"
  - default fields: see above
  - special fields:
    - state (dropdown)
      - new
      - current
      - maybe
      - done
    - result (string)
    - files_nr (string, 4 digits with leading zeros)
      - files_nr: a global counter for all apartments, auto-incrementing
      - use a json file to remember the last id in user/Default/types/Apartment/files_nr.json
      - increment each time an apartment is created
      - save the generated id in the new record in the "files_nr" field
    - url (string)
  - typeIdentification: same as Activity, also uses a field "type" in the front matter of instances because we must differentiate this type from Activity
  - allowedSubTypes: Activity, Info
  - list renderer:
    - first line:
      - left aligned:  name (with clickable url if present) 
      - right aligned: status as badge 
    - second line (small, grey):
      - left aligned:  date (YYYY-MM-DD)
      - right aligned: files_nr
  - edit renderer: form in modal for editing all fields
  - special features:
    - making pictures
      - let me use the smartphones cam to add images that are saved in e.g. /data/myApartmentSearch/myApartment/images as "resource files"
      - for saving the file we add a special ajax function
      - file types: typically used image types provided by the smartphones cam (implement hardcoded)

# Instances of types (data)

Instances of a type can be saved either as a single files or a folder with the file
"-this.md" that may also have resource files and sub instances.

Sample for the data:

- /data
  - /(i) MyInfo.md: this is some Info instance saved as a single file
  - /2 - myApartmentSearch: this is an instance of type "Activity" saved as folder
    - "-this.md" is the data file, contains data in front matter, except the
                 field "description" which is the text content of the md file
    - /2 - myApartment: is a instance of type "Apartment" inside the apartment search
      - "-this.md"
      - 2 - Some activity.md
      - 260728 - Some closed activity.md
      - some_image.jpg: all files that can't be identified as an instance of a type (by
                        trying any typeIdentification of all types or if text file: by
                        looking for a front matter field "type") are "resource files" for
                        the current instance
      - /myFolder:      all folders that can't be identified as an instance of a type are
                        "group folders" that may contain sub instances or resources
      - /images
    - /260720 - myApartment 2: a closed apartment
      - ...
  - ...

## User Interface

Use bootstrap 5.3 and optimize it for smartphones. The app UI consists of a header bar and a scrollable content area.

- Header bar (fixed to the page)
  - Name of the current level in the hierarchcal list (initially "Start")
    - we use no breadcrumbs for navigation, just the smartphone's back button
  - Actions dropdown (right aligned):
    - Edit: edit the currently shown instance (inactive on the start page)
    - Delete: delete the currently shown instance
    - gears icon (for settings, currently has no function)
- Content area (scrollable)
  - Read only rendering of the current entry (via ready_only.php, invisible on the start page)
  - Tabs
    - List
      - Tool bar with
        - sorting dropdown (e.g. by time, by name)
        - Add button (right aligned): brings up a modal that lets you select the type and set the name and description (currently no special fields)
      - List (use cards)
        - initially show the data from the first level of /data
        - the list is sorted by time (last one first)
        - list cell
          - layout
            - the cell content is rendered by the list cell renderer of the current type
            - all cells get actions on the right of the cell (button group)
              - edit button: open a modal for editing the entry
              - dropdown: single action "delete"
          - actions
            - single click or touch: loads the the list of sub entries associated with the entry
    - Resources: list of "resource files" and "group folders" in the current directory

## Misc functions

- We currently use no login system
- Add error handling for PHP and JavaScript errors with display to the user

## Code

- Use simple code for input validation, ideally with no third party library
- Indent all codes with 2 spaces, put the { on the next line and use Symfony yml.
- Avoid using the __DIR__ constant, we can use relative paths cause we route everything over index.php oder ajax.php

### Design

- Make sure that the bootstrap layout looks good and prefer bootstrap classes over own styles
- Make the layout work on all devices

### Demo data

- Also add demo files under /data as descriped above
