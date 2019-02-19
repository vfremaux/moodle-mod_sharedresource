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
 * Displays the filled fields of the metadata
 * form and save these metadata and the resource. 
 * It informs the user if there are some errors and in that 
 * case, the resource is not saved and the user is sent back
 * to the metadata form
 *
 * @author  Frederic GUILLOU
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package    mod_sharedresource
 * @category   mod
 */
require_once('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_entry.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_metadata.class.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');

$config = get_config('sharedresource');

require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$config->schema.'/plugin.class.php');

// Receive params.

$mode = required_param('mode', PARAM_ALPHA);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$cancel = optional_param('cancel', 0, PARAM_BOOL);
$return = optional_param('return', 0, PARAM_INT); // Return to course/view.php if false or mod/modname/view.php if true.
$section = optional_param('section', 0, PARAM_INT);
$course = required_param('course', PARAM_INT);
$catid = optional_param('catid', 0, PARAM_INT);
$catpath = optional_param('catpath', '', PARAM_TEXT);
$type = 'file';
$sharingcontext = optional_param('context', 1, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $course))) {
    print_error('badcourseid', 'sharedresource');
}

// Security.
$systemcontext = context_system::instance();
$context = context_course::instance($course->id);
if ($course->id > 1) {
    require_login($course);
    require_capability('moodle/course:manageactivities', $context);
} else {
    require_login();
    $caps = array('repository/sharedresources:create', 'repository/sharedresources:manage');
    if (!has_any_capability($caps, context_system::instance())) {
        if (!sharedresources_has_capability_somewhere('repository/sharedresources:create', false, false, false, CONTEXT_COURSECAT.','.CONTEXT_COURSE)) {
            print_error('noaccess');
        }
    }
}

if ($cancel) {
    if ($return) {
        // We are coming from the library. Go back to it.
        if ($return == 1) {
            $fullurl = new moodle_url('/local/sharedresources/browse.php', array('course' => $course->id, 'catid' => $catid, 'catpath' => $catpath));
        } else {
            $fullurl = new moodle_url('/local/sharedresources/explore.php', array('course' => $course->id));
        }
        redirect($fullurl, get_string('correctsave', 'sharedresource'), 5);
    }

    // We are coming from a course sharedresource selection operation.
    $params = array('course' => $course->id, 'section' => $section, 'add' => 'sharedresource', 'return' => $return);
    $cancelurl = new moodle_url('/course/modedit.php', $params);
    redirect($cancelurl);
}

$SESSION->error = '';
$srentry = $SESSION->sr_entry;
$shrentry = unserialize($srentry);

if ($confirm) {
    $oldshrentryid = required_param('shentryid', PARAM_INT);
    // We asked to go to the old resource edition.
    $params = array('course' => $course->id,
                    'section' => $section,
                    'add' => 'sharedresource',
                    'type' => $type,
                    'mode' => 'update',
                    'catid' => $catid,
                    'catpath' => $catpath,
                    'return' => $return,
                    'entryid' => $oldshrentryid);
    $fullurl = new moodle_url('/mod/sharedresource/edit.php', $params);
    redirect($fullurl);
}

// Build and print the page.

$pagetitle = strip_tags($course->shortname);
$strtitle = $pagetitle;
$PAGE->set_pagelayout('standard');
$system_context = context_system::instance();
$PAGE->set_context($system_context);
$urlparams = array('mode' => $mode, 'course' => $course->id, 'section' => $section, 'return' => $return);
$url = new moodle_url('/mod/sharedresource/metadatapreupdateconfirm.php', $urlparams);
$PAGE->set_url($url);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);

// Navigation.
$PAGE->set_cacheable(false);

$renderer = $PAGE->get_renderer('mod_sharedresource');

$cancelurlparams = $urlparams;
$cancelurlparams['cancel'] = 1;
$cancelurl = new moodle_url('/mod/sharedresource/metadatapreupdateconfirm.php', $cancelurlparams);

$oldresource = \mod_sharedresource\entry::get_by_identifier($shrentry->identifier);

$confirmurlparams = $urlparams;
$confirmurlparams['confirm'] = 1;
$confirmurlparams['shentryid'] = $oldresource->id;
$confirmurl = new moodle_url('/mod/sharedresource/metadatapreupdateconfirm.php', $confirmurlparams);

$message = '<div id="sharedresource-in-the-way"><p>'.get_string('resourceintheway', 'sharedresource').'</p></div>';

$message .= $renderer->resourcecompare($shrentry, $oldresource, 'predata');

$message .= '<div id="sharedresource-update-confirm"><p>'.get_string('resourceaskupdate', 'sharedresource').'</p></div>';

echo $OUTPUT->header();
echo $OUTPUT->box($message, 'sharedresource-compare');
echo $OUTPUT->confirm('', $confirmurl, $cancelurl);
echo $OUTPUT->footer();
