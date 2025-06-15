
PC layout or tablets in widescreen: show the main list left and the details right. When the user clicks on a sub entry, show the sub entry details right.

 --

I am making an app that can be used to manage all kind of events.

We can manage different types of events in one app. Therefore in the header bar of the app we have a dropdown on the left and gears icon on the right. The dropdown can be used to switch events so that the user can quickly go to a seperate event if he has multiple things to manage. The gear icon currently has no function. Possible data fields for the event can be defined in def.yml (see file structure below).

Below the header the app shows a list of entries (bootstrap list group), sorted by last one first. Each list entry can be of a different type, types are defined in def.yml (see file structure below). Currently we have only one event with 2 entry types for my searching for my search for a new apartment:

- Default fields for event types:
  - title (input)
  - fields currently hidden in UI:
    - id (hidden in UI)
      - use a json file to remember the last id (for all ids)
    - date (YYYY-MM-DD)
    - description (textarea)
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

We can navigate from the entries list to a details page. Each entry by default can also have a list of sub entries which can be events or entries (hierarchical) if that feature is enabled in def.yml (bootstrap list group). In the UI this list is shown below the entry fields. For the entry type apartment we use this for status texts only:

- Entry type "apartment_status" (no special fields)

Add typical CRUD functions everywhere in a user friendly way.

File structure for events and entries:

- /events
  - /shared_entry_types
    - /MY_ENTRY_TYPE
      - def.yml caontains: data field definitions with for basic validation: name, type (string, int, float, bool), required, format (regex for strings), min / max / step (for numbers), values (for dropdown)
      - list.php: cell renderer used to render the list cell
      - edit.php: renderer for the edit page
      - /ajax: possible ajax functions one file per function (ajax call forwarded by ajax.php)
  - /MY_EVENT_TYPE
    - def.yml contains: data field definitions
    - /entry_types
      - /MY_ENTRY_TYPE
        - ...

File structure for data:

- /data
  - /MY_EVENT_TYPE
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
