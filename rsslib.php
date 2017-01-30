<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * This file adds support to rss feeds generation
 * This function is the main entry point to sharedresource module
 * rss feeds generation.
 * TODO : Not ready yet
 */
function sharedresource_rss_feeds() {
    global $CFG, $DB;

    include_once("$CFG->libdir/rsslib.php");

    $status = true;
    /*
    // Check CFG->enablerssfeeds.
    if (empty($CFG->enablerssfeeds)) {
        debugging("DISABLED (admin variables)");
    }
    // Check CFG->data_enablerssfeeds.
    else if (empty($CFG->sharedresource_enablerssfeeds)) {
        debugging("DISABLED (module configuration)");
    }
    // It's working so we start...
    else {
    $sql = 'SELECT * ' .
    "FROM {sharedresource_entry} " .
    "ORDER BY timemodified DESC LIMIT {$CFG->sharedresource_article_quantity}";
    if (!$sharedresources = $DB->get_records_sql($sql)) {
    return false;
    }
    // Get the first and put it back
    $lastrecord = array_shift($sharedresources); 
    array_unshift($sharedresources, $lastrecord);
    $lastrecord->id = 1;
    $xmlname = 'lastsharedres';
    $modname = 'sharedresources';
    $filename =  rss_file_name_local($modname, $xmlname);
    if (file_exists($filename)) {
    if (filemtime($filename) >= $lastrecord->timemodified) {
    return $status;
    }
    }
    $items = array();
    foreach ($sharedresources as $sharedresource) {
    $item = null;
    $item->title = $sharedresource->title;
    $item->description = $sharedresource->description;
    $item->pubdate = $sharedresource->timemodified;
    $item->link = $sharedresource->url;
    array_push($items, $item);
    }
    // First all rss feeds common headers.
    $header = rss_standard_header("Flux RSS Ressources Sankoré",$CFG->wwwroot . '/resources/');
    if (!empty($header)) {
    $articles = rss_add_items($items);
    }
    // Now all rss feeds common footers.
    if (!empty($header) && !empty($articles)) {
    $footer = rss_standard_footer();
    }
    // Now, if everything is ok, concatenate it.
    if (!empty($header) && !empty($articles) && !empty($footer)) {
    $rss = $header.$articles.$footer;
    //Save the XML contents to file.
        $status = rss_save_file_local($modname, $xmlname, $rss);
        } else {
            $status = false;
        }
    }
    return $status;
*/
}
