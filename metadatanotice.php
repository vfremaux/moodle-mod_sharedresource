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
 *
 * @author  Frédéric GUILLOU
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 * This php script displays the metadata form
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classificationlib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_metadata.class.php');

$system_context = context_system::instance();
$strtitle = get_string('metadatanotice', 'sharedresource');
$PAGE->set_pagelayout('popup');
$PAGE->set_context($system_context);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('sharedresourcenotice', 'sharedresource'));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');
$PAGE->set_headingmenu('');
$url = new moodle_url('/mod/sharedresource/metadatanotice.php');
$PAGE->set_url($url);
$PAGE->requires->js_call_amd('mod_sharedresource/metadatanotice', 'init');

$renderer = $PAGE->get_renderer('mod_sharedresource', 'metadata');

$config = get_config('sharedresource');

$id = optional_param('id', 0, PARAM_INT);
$identifier = optional_param('identifier', 0, PARAM_TEXT);

if ($identifier) {
    if (!$shrentry = $DB->get_record('sharedresource_entry', array('identifier' => $identifier))) {
        sharedresource_not_found();
    }
} else {
    if ($id) {
        if (! $cm = get_coursemodule_from_id('sharedresource', $id)) {
            sharedresource_not_found();
        }
        if (!$resource = $DB->get_record('sharedresource', array('id' => $cm->instance))) {
            sharedresource_not_found($cm->course);
        }
        if (!$shrentry_rec = $DB->get_record('sharedresource_entry', array('identifier' => $resource->identifier))) {
            sharedresource_not_found($cm->course);
        }
        if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
            print_error('badcourseid', 'sharedresource');
        }
    } else {
        sharedresource_not_found();
    }
}

$shrentry = \mod_sharedresource\entry::read($shrentry->identifier);

\mod_sharedresource\metadata::normalize_storage($shrentry->id);

$pagetitle = strip_tags($SITE->fullname);
// build up navigation links

$capability = metadata_get_user_capability();

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('sharedresourcenotice', 'sharedresource', '<span class="mtd-resource-name">'.format_string($shrentry->title).'</span>'));

require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$config->schema.'/plugin.class.php');

echo $renderer->notice($shrentry, $capability);

echo $OUTPUT->footer();
