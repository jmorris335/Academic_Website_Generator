# Academic Website Generator
This repository provides a PHP script for generating a list of publications and links from a Biblatex file.

## License
This work is licensed under the MIT license, meaning it can be used just about however you want. Specific details are in the license file.

## Setup
This repository includes example files that can be loaded and viewed immediately. Just include the web folder in the same domain as the `publications_page.php` file. To customize for yourself, you'll need to replace the BibTeX file and dummy PDF files.

To see how this looks on a live website, you can visit the author's own webpage [here](https://www.people.clemson.edu/jhmrrs/).

### Website Structure
- `publications_page.php`: a minimalistic HTML page that calls the PHP parser.
- `web/`:
  - `assets/files`: a list of PDF files for any of the sources. Note that the name of each PDF file must correspond to the CitationKey of the reference it should be attached to.
  - `assets/library.bib`: the BibTeX file containing all the references.
  - `css/styles.css`: the stylesheet for modifying fonts, colors, and other stylistic information.
  - `js/app.js`: contains code for the toggle links (making elements appear and disappear from the screen when clicked on).
  - `php/bib_scraper.php`: the PHP script that parses the BibTeX file, returning the HTML for displaying all the publications.

#### Helpful Tips
The easiest way to create the BibTeX file is to use Zotero. I make a unique library containing all the references for things that should go on the publications page: articles, presentations, tools, etc. This folder is exported as a bibtex file using the BetterBibtex plugin. This makes a nice bib file that Zotero will keep updated, so that anytime I make changes to the my Zotero source it updates on the bib file. I personally use the BetterBibLaTex version of the export rather than BetterBibTex because BibLaTex is better suited for grabbing information like venues and dates. If you use this script without making any changes you'll need to use that form as well.

## Process
### Generating the Biblatex File
The script depends on a BibTeX file (following the BibLaTeX style). The PHP parser will look for the following fields in each citation:
- `title`, the main heading on the publication
- `author`, list of authors for the publication
- `date`, following `{yyyy-mm-dd}` format
- `eventtitle`/`journal`/`journaltitle`/`organization`/`booktitle`/`publisher`/`location` (processed in that order), for where the publication should be listed as occurring or being published in
- `keywords`, where any keyword prefixed by `exp:` will be used to determine where the publication should be shown, according to the following:
  - `exp:journal` for journal articles.
  - `exp:conference` for conference articles.
  - `exp:presentation` for presentations.
  - `exp:inprogress` for works in progress
  - `exp:tool` for reports and tools.
- `abstract`
- `annotation`, providing additional fields for display, as described below. Accepted annotation `key:value` pairs are:
  - `Distinction: Text` Adds "Text" as a highlighted label by the publication, such as "Best Paper" or similar.
  - `Video: URL` Embeds a viewer for the video linked at the URL.

#### Adding additional resources
If you want PDF files to be displayed you'll need to add them separately into the `assets/files` folder. Make sure the name for each PDF is equivalent to the CitationKey of the corresponding publication (the text following the `@type{` prefix).

You can also add other elements to the scraper by adding an annotation field to the BibTeX file. In Zotero, this is done by writing a colon-separated `label: value` in the "Extra" field. Currently supported tags include the following:
- `Distinction: Text` Adds "Text" as a highlighted label by the publication, such as "Best Paper" or similar.
- `Video: URL` Embeds a viewer for the video linked at the URL.

Examples of these, and how they are used, are included in the dummy folders.

### Adjusting the Formatting
The PHP script goes through the bibtex file and does string matching to figure out which articles should go where. Everything is function based, so if you want to edit the formatting it shouldn't be too hard to do so. By default, it outputs references as 

**Title**  
Authors  
Mon Year | *Venue*  
Links... 

The master function for formatting (and changing the order) is the `__toString()` in the BibBlob class (line 261).

Styles should be adjusted in a CSS file (fonts, colors, bolding, etc.) by changing the css file attached. For instance, if you wanted to change the link color to red, you could change the `--accent_color` on line 5 to a different hex code, and if you wanted to make the title's all sans serif you could change for formatting in h3 to have a line saying `font-family: Helvetica, sans-serif;`.