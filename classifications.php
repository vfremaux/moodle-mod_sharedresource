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
 * This php script display the admin part of the classification
 * configuration. You can add, delete or apply a restriction
 * on a classification, or configure a specific classification
 * by accessing another page
 *
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/ddllib.php');

$id = optional_param('id', 0, PARAM_TEXT);
$classname = optional_param('classificationname', '', PARAM_TEXT);
$mode = optional_param('mode', 0, PARAM_ALPHA);
$target = optional_param('target', '', PARAM_TEXT);
$table = optional_param('table', '', PARAM_TEXT);
$parent = optional_param('parent', 0, PARAM_TEXT);
$label = optional_param('label', '', PARAM_TEXT);
$ordering = optional_param('ordering', 0, PARAM_TEXT);
$orderingmin = optional_param('orderingmin', 0, PARAM_INT);

// Security.

$systemcontext = context_system::instance();
require_login();
require_capability('moodle/site:config', $systemcontext);

// Build page.

$url = new moodle_url('/mod/sharedresource/classifications.php');
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('classifications', 'sharedresource'));

if (!empty($mode)) {
    include($CFG->dirroot.'/mod/sharedresource/classificationconfigure.controller.php');
}

$classifications = $DB->get_records('sharedresource_classif');

$renderer = $PAGE->get_renderer('mod_sharedresource', 'classification');

if (empty($classifications)) {
    echo $OUTPUT->notification(get_string('noclassification', 'sharedresource'));
} else {
    echo $renderer->classifications($classifications);
}

echo '<hr><br/>';
echo '<center>';
$label = get_string('backadminpage','sharedresource');
$hrefurl = new moodle_url('/admin/settings.php', array('section' => 'modsettingsharedresource'));
echo $OUTPUT->continue_button($hrefurl, $label);
echo '</center>';
echo '<br/>';
echo $OUTPUT->footer();
