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
 * @author  Piers Harding  piers@catalyst.net.nz
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package mod_sharedresource
 * @category mod
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/forms/search_form.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');

$courseid       = required_param('course', PARAM_INT);
$add            = optional_param('add', 0, PARAM_ALPHA);
$return         = optional_param('return', 0, PARAM_BOOL); // Return to course/view.php if false or local/sharedresources/index.php if true.
$type           = optional_param('type', 'file', PARAM_ALPHANUM);
$section        = optional_param('section', 0, PARAM_ALPHANUM);
$id             = optional_param('id', false, PARAM_INT); // The originating course id.
$page           = optional_param('page', false, PARAM_INT);

// Query string parameters to ignore.

$excludeinputs = array('course', 'section', 'add', 'update', 'return', 'type', 'id', 'page', 'MAX_FILE_SIZE', 'submitbutton');

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('coursemisconf');
}

// Security.

$systemcontext = context_system::instance();
$context = context_course::instance($course->id);
require_course_login($course, false);
require_capability('moodle/course:manageactivities', $context);

if ($return) {
    $params = array();
    if ($id) {
        $params = array('id' => $id);
    }
    redirect(new moodle_url('/local/sharedresources/index.php', $params));
}

$strtitle = get_string('addinstance', 'sharedresource');
$PAGE->set_pagelayout('standard');
$params = array('course' => $courseid, 'add' => $add, 'return' => $return, 'section' => $section, 'id' => $id);
$url = new moodle_url('/mod/sharedresource/search.php', $params);
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($strtitle, 'metadataconfigure.php', 'misc');
$linkurl = new moodle_url('/mod/sharedresource/index.php', array('id' => $course->id, 'section' => $section));
$PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'), $linkurl, 'activity');
$PAGE->navbar->add(get_string('searchsharedresource', 'sharedresource'));

$renderer = $PAGE->get_renderer('mod_sharedresource');

// Process search form.

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('addinstance', 'sharedresource'));

echo $renderer->add_instance_form($section, $return);

echo $OUTPUT->footer();
