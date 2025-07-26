
I am making an app that can be used to manage a list of entries of different types. From each entry the user can navigate to a list of sub entries (hierarchical).

## Common

```yml
dataFileName: "-this"  # name of the data file used in data
```

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
  - /MyType
    - def.yml type definition, contains: definitions of special data fields for this type with basic validation
    - ready_only.php: renders a read only version of the instance data
      - this is a php file that uses PHP's alternative syntax for rendering HTML
    - list.php: cell renderer used to render the list cell
    - edit.php: renderer for the edit page
    - /ajax:    default and special ajax functions one file per function (ajax call forwarded by ajax.php)
  - ...

Type definition:

```yml
id:   MyType        # type unique id
time:               # created time YYYY-MM-DD HH:MM:SS

name: My Type       # type name
description: |
  type description

typeIdentification: "^\\s*[1-5]\\s*-\\s*"  # identify the type of a file or folder in /data (match this against name)
allowedSubTypes:    ["Info"]               # list of type ids of allowed sub types for the list

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
    - priority (int)
    - state (dropdown), values: new (default), progress, done
    - due to date (optional)
  - allowedSubTypes: all
  - list renderer:
    - left aligned:  priority as badge and name
    - right aligned: state
  - edit renderer: form in modal for editing all fields

- Type "Info"
  - default fields: see above
  - no special fields
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
  - special features:
    - making pictures
      - let me use the smartphones cam to add images that are saved in e.g. /data/myApartmentSearch/myApartment/images as "resource files"
      - for saving the file we add a special ajax function
    - provide a single additional numeric identifier "files_nr" for all Apartment instances (shared use)
      - use a json file to remember the last id in types/Apartment/files_nr.json
      - increment each time an apartment is created
      - save the generated id in the new record in the "files_nr" field

# Instances of types (data)

Sample for the data:

- /data
  - /MyInfo.md: this is some Info instance made as file
  - /myApartmentSearch: this is an instance of type "Activity"
    - "-this.md" is the data file, contains data in front matter, except the
      field "description" which is the text content of the md file
    - /myApartment: this contains instances of type "Apartment"
      - "-this.md"
      - some_image.jpg: all files that can't be identified as an instance of a type are "resource files" for the current instance
      - /myFolder:      all folders that can't be identified as an instance of a type are "group folders" that may contain instances or resources
      - /images
  - ,,,

## User Interface

We keep the user interface pretty simple. Use bootstrap 5.3 and optimize it for smartphones. The app UI consists of a header bar and a list of entries (list group).

- Header bar
  - Name of the current level in the hierarchcal list (initially "Start")
  - Actions dropdown (right aligned): 
    - Edit: edit the currently shown instance
    - Delete: delete the currently shown instance
    - gears icon (for settings, currently has no function)
- Read only rendering of the current entry (via ready_only.php, invisible on the start page)
- Tabs
  - List
    - Tool bar with
      - sorting dropdown (e.g. by time, by name)
      - Add button (right aligned): brings up a modal that lets you select the type and set the name and description (currently no special fields)
    - List (list group)
      - initially show the data from the first level of /data
      - the list is sorted by time (last one first)
      - list cell
        - layout
          - the cell content is rendered by the list cell renderer of the current type
          - all cells get an action menu on the right (dropdown, single action: delete)
        - actions
          - single click or touch: open a modal for editing the entry
          - double click or double touch: loads the the list of sub entries associated with the entry
  - Resources: list of "resource files" and "group folders" in the current directory

## Misc

- Use simple code for input validation, ideally with no third party library
- Indent all codes with 2 spaces, put the { on the next line and use Symfony yml.
