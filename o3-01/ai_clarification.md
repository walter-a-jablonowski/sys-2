
Initial scope

Should I start by scaffolding the core framework (routing, type loader, YAML I/O, basic Bootstrap layout) or focus on a specific feature first (e.g., Apartment image upload)?

Renderers

For each type, do you prefer one PHP class that both loads data and renders HTML, or a separate renderer file per view (list vs. detail)?

Navigation

The “back” button: should it rely on browser history or an explicit “parentId”/“parentPath” saved in each instance?

Error display

Where should PHP/JS errors surface—inline toast/alert, or a dedicated error panel?

Image upload

Accept only JPEG/PNG from the device camera, resize client-side, or store raw file?
Save images inside the same data/ subfolder as the Apartment instance?

Future extensibility

Should I design the type system so you can add new field types (e.g., select, textarea) via simple YAML config, or is hard-coding current fields sufficient for now?

 --

- Implement the ful application
- As descriped in the prompt we use simple php/html files for rendering
- We implemnt no back button, the user uses buttons in Chrome/Android instead
- Error display: you decide
- Image upload: Just make caputuring an image with the smartphone cam possible and save it in a standard format
- The control type for the fields is hard coded in the rendering files
