
PC layout or tablets in widescreen: show the main list left and the details right. When the user clicks on a sub entry, show the sub entry details right.

- Header bar
  - dropdown on the left: can be used to switch main entries so that the user can quickly switch them

 --

I am making an app that can be used to manage a list of entries of different types. from each entry the user can navigate to a list of sub entries (hierarchical).

## Common

Deriving ids from field name:

- convert each word to first character uppercase, then remove all non alpha numeric chars
- usually we also add a additional string to the id to make it unique (see below)

## Types

Different types of entries can be defined in a file structure like

- /entries
  - def.yml contains: definitions of default data fields with basic validation
  - /shared: shared entry types
    - /MY_ENTRY_TYPE
      - def.yml contains: definitions of default data fields with basic validation
      - list.php: cell renderer used to render the list cell
      - edit.php: renderer for the edit page
      - /ajax: possible ajax functions one file per function (ajax call forwarded by ajax.php)
  - /MY_ENTRY_TYPE
    - def.yml contains: data field definitions
    - /types: special sub types
      - /MY_ENTRY_TYPE
        - def.yml contains: definitions of special data fields with basic validation
        - same as above ...

def.yml:

```yml
id:   default_MyType  # unique id (derived from name, add "default" in front which is currently the only user)
date:                 # created date

name:                 # type name
description: |
  type description

allowedSubTypes: ["Info"]  # list of type ids of allowed sub types for the list

fields:               # special fields for this type
  myField:            # name of the field
    type:             # string, int, float or bool
    required:         # true | false
    format:           # regex (for strings)
    min:              # min, max, step (for numbers)
    max: 
    step: 
    values:           # for dropdown
      "My label": someValue 
  ...
```

Default fields for all entry types (defined in entries/def.yml):

- date (string, YYYY-MM-DD)
- name (string)

The id isn't explicitly defined in def.yml but hardcoded

## Initial types

- Type "Info" (default_Info), special fields:
  - description (string)
  - list renderer:
    - left aligned:  date (format MM-DD)
    - right aligned: name
  - edit renderer: form in modal for editing all fields

We also define these types for my search for a new apartment:

- Type "Apartment" (default_Apartment)
  - special fields:
    - description (string)
    - a status (dropdown)
      - new
      - current
      - maybe
      - done
    - result (string)
    - url (string)
    - files_nr (string, 4 digits with leading zeros, incrementing)
      - use a json file to remember the last id in data/myApartmentSearch_YYYY-MM-DD-HHMMSS/files_nr.json
  - allowedSubTypes: Info
  - list renderer:
    - first line:
      - left aligned:  name (with clickable url if present) 
      - right aligned: status as badge 
    - second line (small, grey): 
      - left aligned:  date
      - right aligned: files_nr 
  - edit renderer: form in modal for editing all fields

# Instances of entry types (data)

The ids for instances look like `myApartmentSearch_YYYY-MM-DD-HHMMSS`

- /data
  - /myApartmentSearch_YYYY-MM-DD-HHMMSS
    - data.yml contains: data for this entry
    - files_nr.json
    - /files
      - /FILES_NR
        - image.jpg
  - ,,,

## User Interface

We keep the user interface pretty simple. Use bootstrap 5.3 and optimize it for smartphones. The app UI consists of a header bar and a list of entries (list group).

- Header bar
  - Name of the current level in the hierarchcal list (initially "Start")
  - Button with gears icon on the right (currently has no function)
- List (list group)
  - initially show the main entries (the data is from the first level of folders in /data)
  - the list is sorted by last one first (field date)
  - when we double clicks or touches an entry this loads the the list of sub entries associated with the entry
  - list cells
    - all cells get an action menu on the right (dropdown, single entry: delete)
    - the cell content on the left side is rendered by the list cell renderer of the current type
    - default behaviour for navigating:
      - single click or touch: open a modal for editing the entry

TASK: For apartments, also let me use the smartphones cam to add images that are saved in /data/myApartmentSearch_YYYY-MM-DD-HHMMSS/files/FILES_NR/.

## Misc

- Use simple code for input validation, ideally with no third party library
- Indent all codes with 2 spaces, put the { on the next line and use Symfony yml.
