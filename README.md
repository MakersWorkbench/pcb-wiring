# PCB and Wiring Tools

Free tools for circuit board and wiring design, by [TheMakersWorkbench.com](https://www.themakersworkbench.com). One page, five tools, no build step and no dependencies. Type values with prefixes like `4.7k` or `470u`, and the answers update as you type.

![PCB and Wiring Tools](wiki/images/overview.png)

## The tools

| Tab | What it does |
| --- | --- |
| Trace Width | Copper trace width for a current, copper weight, and temperature rise (IPC-2221). |
| Trace Resistance | Resistance, voltage drop, and heat of a trace. |
| Via Current | An estimate of via current capacity and resistance. |
| Trace Spacing | Minimum conductor spacing for a voltage (IPC-2221 Table 6-1). |
| Wire Gauge | AWG to mm² conversion, and voltage drop in a wire run. |

## Run it

Open `index.html` in a browser. To serve it locally: `python -m http.server` in this folder.

Run the tests (Node 18 or newer, no packages needed):

```
node --test tests/pcb-logic.test.js
```

## Embed it

Load `pcb.css`, `js/pcb-logic.js`, and `js/pcb-app.js`, then add:

```html
<div class="tmwb-pcb" id="tmwb-pcb" data-heading="h2"></div>
```

All styles are scoped under `.tmwb-pcb` and sized in pixels.

### Drupal module

`drupal/tmwb_pcb_wiring` is a Drupal 10/11 module (TMWB PCB and Wiring Tools) that embeds the tool from a text-format filter. See its README.

## Files

- `index.html`, `pcb.css`: the page and styles
- `js/pcb-logic.js`: all the math, no DOM access, tested in Node
- `js/pcb-app.js`: the interface
- `tests/`: unit tests
- `drupal/tmwb_pcb_wiring/`: Drupal module (run `python scripts/sync_drupal_module.py` after changing the tool)
- `SOURCES.md`: where each formula and data table comes from

## Accuracy

The trace and via formulas are the IPC-2221 curves, a conservative starting point. IPC-2152 is newer and more detailed, and your board stack-up matters. Spacing tables are for general use only. Confirm with the standards and your board maker.

## License

[Apache License 2.0](LICENSE). Free for commercial and non-commercial use. You may modify it and add to it, as long as the license, the [NOTICE](NOTICE), and the "Developed and Maintained by TheMakersWorkbench.com" attribution stay intact.
