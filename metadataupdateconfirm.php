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
 *
 * @package     mod_sharedresource
 * @author      Frederic Guillou, Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/*
 * form and save these metadata and the resource.
 * It informs the user if there are some errors and in that
 * case, the resource is not saved and the user is sent back
 * to the metadata form
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_entry.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_metadata.class.php');

$config = get_config('sharedresource');

require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$config->schema.'/plugin.class.php');

// Receive params.

$mode = required_param('mode', PARAM_ALPHA); // Mode 'add' or 'update' sequence.
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$cancel = optional_param('cancel', 0, PARAM_BOOL);
$return = optional_param('return', '', PARAM_ALPHA); // Tells where to return.
$section = optional_param('section', 0, PARAM_INT);
$catid = optional_param('catid', 0, PARAM_INT);
$catpath = optional_param('catpath', '', PARAM_TEXT);
$course = required_param('course', PARAM_INT);
$sharingcontext = optional_param('context', 1, PARAM_INT);

if (!$course = $DB->get_record('course', ['id' => $course])) {
    throw new moodle_exception(get_string('badcourseid', 'sharedresource'));
}

// Security.

require_login($course);
$context = context_course::instance($course->id);
require_capability('repository/sharedresources:create', $context);

if ($cancel) {
    // We are coming from the library. Go back to it.
    if ($return != 'course') {
        $params = [
            'course' => $course->id,
            'section' => $section,
            'return' => $return,
            'catid' => $catid,
            'catpath' => $catpath,
        ];
        $cancelurl = new moodle_url('/local/sharedresources/index.php', $params);
    } else {
        // We are coming from a course, more specially from a sharedresource instance modedit form.
        $params = [
            'course' => $course->id,
            'sr' => $section,
            'add' => 'sharedresource',
            'return' => 0,
        ];
        $cancelurl = new moodle_url('/course/modedit.php', $params);
    }
    redirect($cancelurl);
}

$SESSION->error = '';
$srentry = $SESSION->sr_entry;
$shrentry = unserialize($srentry);

if ($confirm) {

    // These two lines in comment can be used if you want to show the user values of saved fields.
    $shrentry->update_instance();

    // If everything was saved correctly, go back to the search page or to the library.
    if ($return) {
        // We are coming from the library. Go back to it.
        if ($return == 1) {
            $params = ['course' => $course->id, 'catid' => $catid, 'catpath' => $catpath];
            $fullurl = new moodle_url('/local/sharedresources/browse.php', $params);
        } else {
            $fullurl = new moodle_url('/local/sharedresources/explore.php', ['course' => $course->id]);
        }
    } else {
        // We are coming from a new sharedresource instance call.
        $params = [
            'course' => $course->id,
            'section' => $section,
            'type' => $type,
            'add' => 'sharedresource',
            'return' => $return,
            'entryid' => $shrentry->id,
        ];
        $fullurl = new moodle_url('/course/modedit.php', $params);
    }
    redirect($fullurl, get_string('correctsave', 'sharedresource'), 5);
}

// Build and print the page.

$pagetitle = strip_tags($course->shortname);
$strtitle = $pagetitle;
$PAGE->set_pagelayout('standard');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$urlparams = ['mode' => $mode, 'course' => $course->id, 'section' => $section, 'return' => $return];
$url = new moodle_url('/mod/sharedresource/metadataupdateconfirm.php', $urlparams);
$PAGE->set_url($url);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);

// Navigation.
$PAGE->set_cacheable(false);

$renderer = $PAGE->get_renderer('mod_sharedresource');

$cancelurlparams = $urlparams;
$cancelurlparams['cancel'] = 1;
$cancelurl = new moodle_url('/mod/sharedresource/metadataupdateconfirm.php', $cancelurlparams);

$confirmurlparams = $urlparams;
$confirmurlparams['confirm'] = 1;
$confirmurl = new moodle_url('/mod/sharedresource/metadataupdateconfirm.php', $confirmurlparams);

$message = '<div id="sharedresource-in-the-way"><p>'.get_string('resourceintheway', 'sharedresource').'</p></div>';
$oldresource = \mod_sharedresource\entry::get_by_identifier($shrentry->identifier);

$message .= $renderer->resourcecompare($shrentry, $oldresource);

$message .= '<div id="sharedresource-update-confirm"><p>'.get_string('resourceupdate', 'sharedresource').'</p></div>';

echo $OUTPUT->header();
echo $OUTPUT->box($message, 'sharedresource-compare');
echo $OUTPUT->confirm('', $confirmurl, $cancelurl);
echo $OUTPUT->footer();
