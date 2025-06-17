
removed:

PC layout or tablets in widescreen: show the main list left and the details right. When the user clicks on a sub entry, show the sub entry details right.

- Header bar
  - dropdown on the left: can be used to switch main entries so that the user can quickly switch them

- files_nr (string, 4 digits with leading zeros, incrementing)
  - use a json file to remember the last id in data/myApartmentSearch/files_nr.json
- files_nr.json

 --

I am making an app that can be used to manage a list of entries of different types. From each entry the user can navigate to a list of sub entries (hierarchical).

## Types

Different types of entries can be defined in a file structure like

- /types
  - def.yml global definition, contains: definitions of default data fields with basic validation
    - time (string, YYYY-MM-DD HH:MM:SS)
    - name (string)
    - description (string)
    - The id isn't explicitly defined in def.yml but hardcoded
  - /MyType_1
    - def.yml type definition, contains: definitions of special data fields for this type with basic validation
    - list.php: cell renderer used to render the list cell
    - edit.php: renderer for the edit page
    - /ajax:    possible ajax functions one file per function (ajax call forwarded by ajax.php)
  - ...

Deriving ids from field name:

- convert each word to first character uppercase, then remove all non alpha numeric chars
- sometimes we also add a additional string to the id to make it unique (see below)

Type definition:

```yml
id:   MyType          # type unique id (derived from name)
time:                 # created time YYYY-MM-DD HH:MM:SS

name: My Type         # type name
description: |
  type description

typeIdentification: "^\\s*[1-5]\\s*-\\s*"  # name of the data file used in data
dataFileName:       "-this"   # name of the data file used in data
allowedSubTypes:    ["Info"]  # list of type ids of allowed sub types for the list

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

## Initial types

- Type "Activity"
  - default fields see above
  - special fields:
    - priority (int)
    - state (dropdown)
  - list renderer:
    - left aligned:  priority as badge and name
    - right aligned: state
  - edit renderer: form in modal for editing all fields

- Type "Info"
  - uses default fields see above
  - list renderer:
    - left aligned:  date (format MM-DD)
    - right aligned: name
  - edit renderer: form in modal for editing all fields

We also define these types for my search for a new apartment:

- Type "Apartment"
  - default fields see above
  - special fields:
    - state (dropdown)
      - new
      - current
      - maybe
      - done
    - result (string)
    - url (string)
  - allowedSubTypes: Info
  - list renderer:
    - first line:
      - left aligned:  name (with clickable url if present) 
      - right aligned: status as badge 
    - second line (small, grey):
      - left aligned:  date (YYYY-MM-DD)
      - right aligned: files_nr 
  - edit renderer: form in modal for editing all fields

# Instances of entry types (data)

- /data
  - /myApartmentSearch: this is an instance of type "Activity"
    - "-this.md" is the data file, contains data in front matter, except the
      field "description" which is the text content of the md file
    - /myApartment: this is an instance of type "Apartment"
      - "-this.md"
      - some_image.jpg: resource files
      - some-pdf.pdf
  - ,,,

## User Interface

We keep the user interface pretty simple. Use bootstrap 5.3 and optimize it for smartphones. The app UI consists of a header bar and a list of entries (list group).

- Header bar
  - Name of the current level in the hierarchcal list (initially "Start")
  - Button with gears icon on the right (currently has no function)
- List (list group)
  - initially show the main entries (the data is from the first level of folders in /data)
  - the list is sorted by last one first (field time)
  - when we double clicks or touches an entry this loads the the list of sub entries associated with the entry
  - list cells
    - all cells get an action menu on the right (dropdown, single entry: delete)
    - the cell content on the left side is rendered by the list cell renderer of the current type
    - default behaviour for navigating:
      - single click or touch: open a modal for editing the entry

TASK: For apartments, also let me use the smartphones cam to add images that are saved in e.g. /data/myApartmentSearch/myApartment/ as "resource files".

## Misc

- Use simple code for input validation, ideally with no third party library
- Indent all codes with 2 spaces, put the { on the next line and use Symfony yml.
