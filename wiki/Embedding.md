# Embedding

## Any web page

Copy `pcb.css`, `js/pcb-logic.js`, and `js/pcb-app.js` from the repository, then add:

```html
<link rel="stylesheet" href="pcb.css">
<div class="tmwb-pcb" id="tmwb-pcb" data-heading="h2"></div>
<script src="js/pcb-logic.js"></script>
<script src="js/pcb-app.js"></script>
```

`data-heading` sets the heading level of the tool's title (`h1` to `h6`, default `h1`). Everything is scoped under `.tmwb-pcb` and sized in pixels. Only one copy can be on a page.

## Drupal

The repository includes `drupal/tmwb_pcb_wiring`, a Drupal 10/11 module.

1. Copy the folder to `web/modules/custom/`.
2. Enable **TMWB PCB and Wiring Tools** (`drush en tmwb_pcb_wiring`).
3. Edit your text format (Configuration, Text formats and editors) and turn on **TMWB PCB and Wiring Tools Embed**. The format must allow `div` elements with a `class` attribute.
4. In the body, switch to source mode and add:

```html
<div class="tmwb-pcb-wiring" data-heading="h2"></div>
```

The filter swaps the placeholder for the tool, loads its CSS and JavaScript, adds a server-rendered description for search engines, and optionally adds Schema.org `WebApplication` structured data.
