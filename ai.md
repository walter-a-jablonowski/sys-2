
PC layout or tablets in widescreen: show the main list left and the details right. When the user clicks on a sub entry, show the sub entry details right.

- Header bar
  - dropdown on the left: can be used to switch main entries so that the user can quickly switch them

 --

I am making an app that can be used to manage hierarchical entries of different types in one app.

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

Deriving ids from name or title:

- an id is derived from the name or title
  - convert each word to first character uppercase, then remove all non alpha numeric chars
  - usually we also add a additional string to the id to make it unique (see below)

def.yml:

```yml
id:   default_MyType  # unique id (derived from name, add "default" in front which is currently the only user)
date:                 # created date

name:                 # type name
description: |
  type description

allowedSubTypes:      # list of type ids of allowed sub types for the list

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
- title (string)

The id isn't explicitly defined in def.yml but made via code

## Initial types

- Baisc type "Info" (default_Info), special fields:
  - description (string)

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

# Data

These are the instances of our types. The ids for type instances are made via code like `myApartmentSearch_YYYY-MM-DD-HHMMSS`.

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
  - App title
  - button with gears icon on the right (currently has no function)
- List
  - initially show the list of the main entries
  - sorted by last one first (field date)
  - in principal each entry in the list can be of a different type

  - We can navigate from the entries list to a details page
  - Each entry by default can also have a list of sub entries (hierarchical)
  - In the UI this list is shown below the entry fields
  - For the entry type apartment we use this for status texts only

  - date (YYYY-MM-DD)
  - title (input)
  - fields currently hidden in UI:
    - id (hidden in UI)
    - description (textarea)

Add typical CRUD functions everywhere in a user friendly way.

For apartments, also let me use the smartphones cam to add images that are saved in /data/myApartmentSearch_YYYY-MM-DD-HHMMSS/files/FILES_NR/.

Indent all codes with 2 spaces, put the { on the next line and use Symfony yml.
