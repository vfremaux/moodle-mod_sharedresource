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
 * @author  Frederic GUILLOU
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package    sharedresource
 * @subpackage mod_sharedresource
 * @category   mod
 *
 * This php script provides a form based interface to select valid and usable taxons from
 * a taxonomy source. 
 */
require('../../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/pro/classes/selector/selected_taxons_selector.php');
require_once($CFG->dirroot.'/mod/sharedresource/pro/classes/selector/potential_taxons_selector.php');
require_once($CFG->dirroot.'/mod/sharedresource/pro/classes/output/classification_extended_renderer.php');

$classifid = required_param('id', PARAM_INT);

// Security.

$systemcontext = context_system::instance();
require_login();
require_capability('moodle/site:config', $systemcontext);

// Build page.

$url = new moodle_url('/mod/sharedresource/pro/classificationtaxonselect.php', array('id' => $classifid));
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');

$renderer = $PAGE->get_renderer('sharedresource', 'classification');
$prorenderer = new  \mod_sharedresource\output\pro_classification_renderer($PAGE, '');

$potentialtaxonsselector = new \mod_sharedresource\selectors\potential_taxons_selector($classifid);
$selectedtaxonsselector = new \mod_sharedresource\selectors\selected_taxons_selector($classifid);

// Process incoming role assignments.
$errors = array();
if (optional_param('add', false, PARAM_TEXT) && confirm_sesskey()) {

    $taxonstostore = $potentialtaxonsselector->get_selected_taxons();

    if (!empty($taxonstostore)) {

        $params = array('id' => $classifid);

        $selection = $DB->get_field('sharedresource_classif', 'taxonselection', $params);
        $selectionarr = explode(',', $selection);

        $storearr = array_keys($taxonstostore);
        foreach ($storearr as $tid) {
            if (!in_array($tid, $selectionarr)) {
                $selectionarr[] = $tid;
            }
        }

        $DB->set_field('sharedresource_classif', 'taxonselection', implode(',', $selectionarr), $params);

        $potentialtaxonsselector->invalidate_selected_taxons();
        $selectedtaxonsselector->invalidate_selected_taxons();
    }
}

// Process incoming role unassignments.
if (optional_param('remove', false, PARAM_TEXT) && confirm_sesskey()) {

    $taxonstoremove = $selectedtaxonsselector->get_selected_taxons();

    if (!empty($taxonstoremove)) {
        $params = array('id' => $classifid);
        $selection = $DB->get_field('sharedresource_classif', 'taxonselection', $params);
        $selectionarr = explode(',', $selection);

        $taxonstostore = array();
        foreach ($selectionarr as $tid) {
            if (!in_array($tid, array_keys($taxonstoremove))) {
                $taxonstostore[] = $tid;
            }
        }

        $DB->set_field('sharedresource_classif', 'taxonselection', implode(',', $taxonstostore), $params);

        $potentialtaxonsselector->invalidate_selected_taxons();
        $selectedtaxonsselector->invalidate_selected_taxons();

    }
}

echo $OUTPUT->header();

echo $prorenderer->selecttaxonsform($classifid, $selectedtaxonsselector, $potentialtaxonsselector);

echo '<center>';
$buttonurl = new moodle_url('/mod/sharedresource/classifications.php', array('id' => $classifid));
echo $OUTPUT->single_button($buttonurl, get_string('backtoconfig', 'sharedresource'));
echo '</center>';

echo $OUTPUT->footer();