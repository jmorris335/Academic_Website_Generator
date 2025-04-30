<?php
class BibBlob {
    public $key; //string: citation key
    public $title;
    public $authors;
    public $date;
    public $link;
    public $link_is_doi;
    public $venue;
    public $award; // Demarcated by "Description: " in the annotation field
    public $bibtex; //array: list of lines in the blob
    public $abstract;
    public $file_path;
    public $types; //`exp:` tags on the file (such as `exp:journal` and the like)
    public $file; // path to PDF file
    public $video; // link to Youtube video

    public function __construct($bibtex_array) {
        $this->bibtex = $bibtex_array;
        $this->key = $this->getKey();
        $this->title = $this->getTitle();
        $this->link = $this->getLink();
        $this->authors = $this->getAuthors();
        $this->date = $this->getDate();
        $this->venue = $this->getVenue();
        $this->types = $this->getTypes();
        $this->parseAnnotation();
        $this->abstract = $this->getAbstract();
        $this->file = $this->getFile();
    }

    public function findBibLine($tag): string|null {
        //Finds the line in the bibtex array starting with the given tag.
        foreach ($this->bibtex as $line) {
            $eq_pos = strpos($line, "=") - 1;
            if ($eq_pos !== -1) {
                $linetag = substr($line, 0, $eq_pos);
                if ($tag === $linetag) {
                    $lb_pos = strpos($line, "{") + 1;
                    $length = strlen($line) - 1 - $lb_pos;
                    if (substr($line, -1) == ",") {
                        $length = $length - 1;
                    }
                    $out = substr($line, $lb_pos, $length);
                    return $out;
                }
            }
        }
        return null;
    }

    public function cleanBibLine($line): array|string {
        // Cleans a string of common formmating marks.
        $out = str_replace(['{', '}', '\\'], '', $line);
        return $out;
    }

    // These getter functions find the correct value in the bibtex array and set it in the class.
    public function getKey(): string {
        $line = $this->bibtex[0];
        $start = strpos($line, "{") + 1;
        $end = strlen($line) - $start - 1;
        $key = substr($line, $start, $end);
        return $key;
    }

    public function getTitle(): array|string {
        $title_line = $this->findBibLine("title");
        $title = $this->cleanBibLine($title_line);
        return $title;
    }

    public function getLink(): string|null {
        $this->link_is_doi = false;
        $link = $this->findBibLine("doi");
        if (is_null($link)) {$link = $this->findBibLine("url");}
        else {$this->link_is_doi = true;}
        if (is_null($link)) {$link = "";}
        if (!$this->link_is_doi) {
            if (strpos(strtoupper($link), "DOI") !== false) {
                $this->link_is_doi = true;
                $pos = strpos($link, "10.");
                $link = substr($link, $pos);
            }
        }
        return $link;
    }

    public function getAuthors(): array {
        $authors_line = $this->findBibLine("author");
        $authors_line = $this->cleanBibLine($authors_line);
        $author_lines = explode(" and ", $authors_line);
        $authors = [];
        foreach ($author_lines as $author_line) {
            $pos = strpos($author_line, ",");
            $first = substr($author_line, $pos + 2);
            $last = substr($author_line, 0, $pos);
            $authors[] = [$first, $last];
        }
        return $authors;
    }

    public function getDate(): DateTime {
        $date_line = $this->findBibLine("date");
        if (is_null($date_line)) {
            return new DateTime();
        } else { // Trim excess date information
            $date_line = substr($date_line, 0, 10);
        }
        $date = new DateTime($date_line);
        return $date;
    }

    public function getVenue(): string {
        $venue = $this->findBibLine(tag: "eventtitle");
        if ($venue === null) {$venue = $this->findBibLine("journal");}
        if ($venue === null) {$venue = $this->findBibLine("journaltitle");}
        if ($venue === null) {$venue = $this->findBibLine("organization");}
        if ($venue === null) {$venue = $this->findBibLine("booktitle");}
        if ($venue === null) {$venue = $this->findBibLine("publisher");}
        if ($venue === null) {$venue = $this->findBibLine("location");}
        if ($venue === null) {$venue = "";}
        $venue = $this->cleanBibLine($venue);
        return $venue;
    }

    public function getTypes(): array {
        $types = [];
        $line = $this->findBibLine("keywords");
        $keywords = explode(",", $line);
        foreach ($keywords as $kw) {
            if (substr($kw, 0, 4) === "exp:") {
                $type = substr($kw, 4);
                $types[] = $type;
            }
        }
        return $types;
    }

    public function getAbstract(): string|null {
        $abstract_line = $this->findBibLine("abstract");
        if ($abstract_line === null) {return null;}
        $abstract = $this->cleanBibLine($abstract_line);
        return $abstract;
    }

    public function parseAnnotation(): void {
        $line = $this->findBibLine("annotation");
        if ($line === null) {return;}
        $annotations = explode("\\\\", $line);
        foreach ($annotations as $annotation) {
            if ($pos = strpos($annotation, ":")) {
                $key = substr($annotation, 0, $pos);
                $value = substr($annotation, $pos + 2);
                if ($key === "Distinction") {
                    $this->award = $value;
                }
                if ($key === "Description") {
                    $this->abstract = $value;
                }
                if ($key === "Video") {
                    $this->video = $value;
                }
            }
        }
    }

    public function getFile(string $path="./web/assets/files/"): string|null {
        $filename = $path . $this->key . ".pdf";
        if (file_exists($filename) && is_file($filename)) {
            return $filename;
        }
        return null;
    }

    // These toString functions format the class properties for printing.
    public function bibtex_toString(): string {
        // We don't need to export every line:
        $exporting = [];
        foreach ($this->bibtex as $line) {
            $eq_pos = strpos($line, "=") - 1;
            if ($eq_pos !== -1) {
                $tag = substr($line, 0, $eq_pos);
                if ($tag !== "keywords" and $tag !== "annotation" and $tag !== "abstract") {
                    $exporting[] = "&nbsp;&nbsp;&nbsp;&nbsp;" . $line;
                }
            }
            else {$exporting[] = $line;}
        }
        $out = implode("<br>", $exporting);
        return $out;
    }

    public function link_toString(): string {
        if (strlen($this->link) == 0) {return "";}
        if ($this->link_is_doi) {
            $text = "DOI";
            $url = "https://doi.org/" . $this->link;
        } else {
            $text = "Link";
            $url = $this->link;
        }
        $out = "<b><a class=\"toggleButton\" href= {$url}> {$text} </a></b>";
        return $out;
    }

    public function authors_toString(): string {
        $authornames = [];
        foreach ($this->authors as $author) {
            $authornames[] = "{$author[0]} {$author[1]}";
        }
        $out = implode(", ", $authornames);
        return $out;
    }

    public function date_toString(): string {
        $out = $this->date->format('M Y');
        return $out;
    }

    public function file_toString(): string {
        $out = "<object data=\"{$this->file}\" type=\"application/pdf\" width=\"100%\" height=\"300px\">";
        $out .= "<p>Unable to display PDF file. <a href=\"{$this->file}\">Download</a> instead.</p>";
        $out .= "</object>";
        return $out;
    }

    public function video_toString(): string {
        $out = "<iframe src=\"{$this->video}\" title=\"{$this->title}\" width=\"100%\" height=\"315px\" frameborder=\"0\"";
        $out .= "allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\"";
        $out .= "referrerpolicy=\"strict-origin-when-cross-origin\" allowfullscreen></iframe>";
        return $out;
    }

    public function buttons_toString() {
        $out = $this->link_toString();
        if (!is_null($this->abstract) and strlen($this->abstract) > 0) {
            $label = "Abstract";
            if (in_array(needle: "tool", haystack: $this->types)) {
                $label = "Description";
            }
            $out .=$this->button_toString(class: "abstract", label: $label);
        }
        $out .=$this->button_toString(class: "bibtex", label: "Cite");
        if ($this->file !== null) {
            $out .=$this->button_toString(class: "file", label: "PDF");
        }
        if ($this->video !== null) {
            $out .=$this->button_toString(class: "video", label: "Video");
        }

        // Make div containers with text for toggle buttons
        if ($this->abstract !== null and strlen(string: $this->abstract) > 0) {
            $out .=$this->toggleDiv_toString(class: "abstract", text: $this->abstract);
        }
        $out .=$this->toggleDiv_toString(class: "bibtex", text: $this->bibtex_toString());
        if ($this->file !== null) {
            $out .=$this->toggleDiv_toString(class: "file", text: $this->file_toString());
        }
        if ($this->video !== null) {
            $out .=$this->toggleDiv_toString(class: "video", text: $this->video_toString());
        }
        return $out;
    }

    public function button_toString(string $class, string $label) {
        $out = "<button class=\"{$class} toggleButton\" onclick=\"toggleVisibility({$class}{$this->key})\">{$label}</button>";
        return $out;
    }

    public function toggleDiv_toString(string $class, string $text) {
        $out = "<div class=\"{$class} toggleDiv\" id=\"{$class}{$this->key}\"> {$text} <br> </div>";
        return $out;
    }

    // Formats the blob for printing
    public function __toString() {
        $out = "<div>";
        $out = "<h3> {$this->title} ";

        // Print award if given
        if (!is_null($this->award)) {
            $out .="<span class=\"label\">&nbsp;{$this->award}&nbsp;</span>";
        }
        $out .="</h3>";

        // Don't print authors for presentations or tools
        if (!in_array("presentation", $this->types) and 
            !in_array("tool", $this->types)) {
            $out .=$this->authors_toString() . "<br>";
        }

        $out .=$this->date_toString() . " | <em>{$this->venue}</em><br>";
        $out .=$this->buttons_toString();
        $out .="</div>";
        return $out;
    }
}

function getBlobs(string $bib_file): array {
    if ($handle = fopen(filename: $bib_file, mode: 'r')) {
        $blobs = [];
        $bibtex = [];
        while (($line = fgets($handle)) !== false) {
            $line = trim(string: $line);
            if (strncmp(string1: $line, string2: '@', length: 1) === 0) {
                $bibtex = [$line];
            }
            elseif ($line === "}") {
                $bibtex[] = $line;
                $blobs[] = new BibBlob(bibtex_array: $bibtex);
            }
            else {
                $bibtex[] = $line;
            }
        }

        fclose($handle);
    } else {
        echo "file not found";
    }

    return $blobs;
}

/**
 * Master caller that prepares, orders and prints the publictionas.
 * @param string $bib_file file_path to bibtex file.
 * @return void
 */
function printPublications(string $bib_file = "./web/assets/library.bib") {
    $blobs = getBlobs($bib_file);
    $blobs = sortBlobsByDate($blobs);
    list($inprogress, $remaining) = separateByType($blobs, "inprogress");

    list($journals, $remaining) = separateByType($remaining, "journal");
    printBlobList("Journal Articles", $journals);

    printBlobList("In Progress", $inprogress);

    list($conferences, $remaining) = separateByType($remaining, "conference");
    printBlobList("Conference Articles", $conferences);

    list($presentations, $remaining) = separateByType($remaining, "presentation");
    printBlobList("Presentations", $presentations);

    list($tools, $remaining) = separateByType($remaining, "tool");
    list($reports, $remaining) = separateByType($remaining, "report");
    printBlobList("Reports and Tools", array_merge($tools, $reports));
    
    printBlobList("Other", $remaining);
}

function printBlobList(string $title, array $blobs) {
    if (count($blobs) == 0) {return;}
    print "<h2> {$title} </h2> <ul>" ;
    foreach ($blobs as $blob) {
        print "<li> {$blob} </li>";
    }
    print "</ul>" ;
}

function sortBlobsByDate(array $blobs): array {
    usort($blobs, function($a, $b) {
        return $b->date <=> $a->date;
    }); 
    return $blobs;
}

function separateByType($blobs, string $type): array {
    $has_type = [];
    $not_has_type = [];
    foreach ($blobs as $blob) {
        $had_type = false;
        foreach ($blob->types as $blob_type) {
            if ($blob_type === $type) {
                $has_type[] = $blob;
                $had_type = true;
                break;
            }
        }
        if (!$had_type) {$not_has_type[] = $blob;}
    }
    return [$has_type, $not_has_type];
}



?>