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
 * Displays the metadata notice form.
 *
 * @package     mod_sharedresource
 * @author      Frederic Guillou, Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

require("../../config.php");
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classificationlib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_metadata.class.php');

use mod_sharedresource\entry;
use mod_sharedresource\metadata;

$id = optional_param('id', 0, PARAM_INT); // The course module where resource is published.
$courseid = optional_param('course', 0, PARAM_INT); // The course in navigation context.
$identifier = optional_param('identifier', 0, PARAM_TEXT);

$config = get_config('sharedresource');
$libraryconfig = get_config('local_sharedresources');

// Check access.
if (!empty($libraryconfig->privatecatalog)) {
    if ($courseid) {
        $context = context_course::instance($courseid);
        $fromcourse = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        require_login($fromcourse);
    } else {
        $context = context_system::instance();
        require_login();
    }
    $where = CONTEXT_COURSECAT.','.CONTEXT_COURSE;
    if (!sharedresource_has_capability_somewhere('repository/sharedresources:view', false, false, false, $where)) {
        throw new moodle_exception(get_string('noaccess', 'local_sharedresources'));
    }
}


$systemcontext = context_system::instance();
$strtitle = get_string('metadatanotice', 'sharedresource');
$PAGE->set_pagelayout('popup');
$PAGE->set_context($systemcontext);
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

if ($identifier) {
    $shrentry = $DB->get_record('sharedresource_entry', ['identifier' => $identifier], '*', MUST_EXIST);
} else {
    if ($id) {
        if (! $cm = get_coursemodule_from_id('sharedresource', $id)) {
            sharedresource_not_found();
        }
        $resource = $DB->get_record('sharedresource', ['id' => $cm->instance], '*', MUST_EXIST);
        $shrentryrec = $DB->get_record('sharedresource_entry', ['identifier' => $resource->identifier], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    } else {
        sharedresource_not_found();
    }
}

$shrentry = entry::read($shrentry->identifier);

metadata::normalize_storage($shrentry->id);

$pagetitle = strip_tags($SITE->fullname);
// Build up navigation links.

$capability = metadata_get_user_capability();

echo $OUTPUT->header();

$html = '<span class="mtd-resource-name">'.format_string($shrentry->title).'</span>';
echo $OUTPUT->heading(get_string('sharedresourcenotice', 'sharedresource', $html));

require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$config->schema.'/plugin.class.php');

echo $renderer->notice($shrentry, $capability);

echo $OUTPUT->footer();
