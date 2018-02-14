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

/**
 * Code to search for courses in response to an ajax call from a course selector.
 *
 * @package core_course
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once('../../../../../config.php');
require_once($CFG->dirroot . '/mod/sharedresource/pro/classes/selector/lib.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/sharedresource/pro/classes/selector/search.php');

echo $OUTPUT->header();

// Check access.
require_login();
require_sesskey();

// Get the search parameter.
$search = required_param('search', PARAM_RAW);

// Get and validate the selectorid parameter.
$selectorhash = required_param('selectorid', PARAM_ALPHANUM);
if (!isset($USER->taxonselectors[$selectorhash])) {
    print_error('unknowncourseselector');
}

// Get the options.
$options = $USER->taxonselectors[$selectorhash];

// Create the appropriate taxonselector.
$classname = $options['class'];
unset($options['class']);
$name = $options['name'];
unset($options['name']);
if (isset($options['file'])) {
    require_once($CFG->dirroot . '/' . $options['file']);
    unset($options['file']);
}
$taxonselector = new $classname($name, $options);

// Do the search and output the results.
$results = $taxonselector->find_taxons($search);
$json = array();
foreach ($results as $groupname => $taxons) {
    $groupdata = array('name' => $groupname, 'taxons' => array());
    foreach ($taxons as $taxon) {
        $output = new stdClass;
        $output->id = $taxon->id;
        $output->name = $taxonselector->output_taxon($taxon);
        if (!empty($taxon->disabled)) {
            $output->disabled = true;
        }
        if (!empty($taxon->infobelow)) {
            $output->infobelow = $taxon->infobelow;
        }
        $groupdata['taxons'][] = $output;
    }
    $json[] = $groupdata;
}

echo json_encode(array('results' => $json));
