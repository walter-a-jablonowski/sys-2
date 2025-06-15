
PC layout or tablets in widescreen: show the main list left and the details right. When the user clicks on a sub entry, show the sub entry details right.

 --

I am making an app that can be used to manage all kind of activities in one app.

Different types of activities can be defined in a file structure like

- /activities
  - /shared: shared activity types
    - /MY_ACTIVITY_TYPE
      - def.yml contains: definitions of default data fields with basic validation
      - list.php: cell renderer used to render the list cell
      - edit.php: renderer for the edit page
      - /ajax: possible ajax functions one file per function (ajax call forwarded by ajax.php)
  - /MY_ACTIVITY_TYPE
    - def.yml contains: data field definitions
    - /types: special sub types
      - /MY_ACTIVITY_TYPE
        - def.yml contains: definitions of special data fields with basic validation
        - same as above ...

Deriving ids from name or title:

- prefer a human readable id like default_TYPE_IDENT where:
  - "default" is currently our only user
  - and TYPE_IDENT is derived from the type name (convert each word to first character uppercase, then remove all non alpha numeric chars)

def.yml:

```yml
id:               # unique id (derived from name)
date:             # created date

name:             # type name
description: |
  type description

allowedSubTypes:  # list of id of allowed sub types

fields:
  myField:        # name of the field
    type:         # string, int, float or bool
    required:     # true | false
    format:       # regex (for strings)
    min:          # min, max, step (for numbers)
    max: 
    step: 
    values:       # for dropdown
      "My label": someValue 
  ...
```

Default fields for all activity types:

- unique id (string)
  - prefer a human readable id like default_TYPE_IDENT where:
    - "default" is currently our only user
    - and TYPE_IDENT is derived from the type name (convert each word to first character uppercase, then remove all non alpha numeric chars)
- date (string, YYYY-MM-DD)
- title (string)
- description (string)

## Initial types

Currently we have only one activity type with 2 entry types for my searching for my search for a new apartment:

- Event type "apartments" currently with no special data fields
- Default fields for entry types:
  - date (YYYY-MM-DD)
  - title (input)
  - fields currently hidden in UI:
    - id (hidden in UI)
    - description (textarea)
- Entry type "common activity", special fields:
  - text (input)
- Entry type "apartment", special fields:
  - details (textarea)
  - a status (dropdown)
    - new
    - current
    - maybe
    - done
  - result (input)
  - url (input)
  - files_id (readonly in UI, 4 digits with leading zeros, incrementing)
    - use a json file to remember the last id

## User Interface

Therefore in the header bar of the app we have a dropdown on the left and gears icon on the right. The dropdown can be used to switch events so that the user can quickly go to a seperate event if he has multiple things to manage. The gear icon currently has no function. 

Below the header the app shows a list of entries (bootstrap list group), sorted by last one first. Each list entry can be of a different type

We can navigate from the entries list to a details page. Each entry by default can also have a list of sub entries which can be events or entries (hierarchical) if that feature is enabled in def.yml (bootstrap list group). In the UI this list is shown below the entry fields. For the entry type apartment we use this for status texts only:

- Entry type "apartment_status" (no special fields)

Add typical CRUD functions everywhere in a user friendly way.

File structure for data:

- /data
  - /MY_ACTIVITY_TYPE
    - ...
  - /apartments_SOME_UNIQUE_ID
    - data.yml contains: data for this event
    - /files
      - /FILES_ID
        - image.jpg
  - /apartments_SOME_UNIQUE_ID_2  <-- we can use events of type apartment multiple times
    - ,,,

For apartments, also let me use the smartphones cam to add images that are saved in /data/apartments/files/FILES_ID/.

Use bootstrap 5.3 and be sure that it looks good on smartphones.

Indent all codes with 2 spaces, put the { on the next line and use Symfony yml.
