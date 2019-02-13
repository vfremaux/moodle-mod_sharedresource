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

$config = get_config('sharedresource');

require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$config->schema.'/plugin.class.php');

// Receive params.

$mode = required_param('mode', PARAM_ALPHA);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$cancel = optional_param('cancel', 0, PARAM_BOOL);
$return = optional_param('return', 0, PARAM_BOOL); // Return to course/view.php if false or mod/modname/view.php if true.
$section = optional_param('section', 0, PARAM_INT);
$catid = optional_param('catid', 0, PARAM_INT);
$catpath = optional_param('catpath', '', PARAM_TEXT);
$course = required_param('course', PARAM_INT);
$type = 'file';
$sharingcontext = optional_param('context', 1, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $course))) {
    print_error('badcourseid', 'sharedresource');
}

// Security.

require_login($course);
$context = context_course::instance($course->id);
require_capability('repository/sharedresources:create', $context);

if ($cancel) {
    if ($return) {
        // We are coming from the library. Go back to it.
        if ($return == 1) {
            $cancelurl = new moodle_url('/local/sharedresources/browse.php', array('course' => $course->id, 'catid' => $catid, 'catpath' => $catpath));
        } else {
            $cancelurl = new moodle_url('/local/sharedresources/explore.php', array('course' => $course->id));
        }
    } else {
        $params = array('course' => $course->id,
                        'section' => $section,
                        'add' => 'sharedresource',
                        'return' => $return,
                        'catid' => $catid,
                        'catpath' => $catpath);
        $cancelurl = new moodle_url('/course/modedit.php', $params);
    }
    redirect($cancelurl);
}

$SESSION->error = '';
$srentry = $SESSION->sr_entry;
$shrentry = unserialize($srentry);

if ($confirm) {

    // It's an update, metadata of the sharedresource should be deleted before adding new ones.
    /*
    foreach ($shrentry->metadataelements as $key => $metadata) {
        unset($shrentry->metadataelements[$key]);
    }
    */

    // These two lines in comment can be used if you want to show the user values of saved fields.
    if (!$shrentry->update_instance()) {
        print_error('failadd', 'mod_sharedresource');
    }

    // If everything was saved correctly, go back to the search page or to the library.
    if ($return) {
        // We are coming from the library. Go back to it.
        if ($return == 1) {
            $fullurl = new moodle_url('/local/sharedresources/browse.php', array('course' => $course->id, 'catid' => $catid, 'catpath' => $catpath));
        } else {
            $fullurl = new moodle_url('/local/sharedresources/explore.php', array('course' => $course->id));
        }
    } else {
        // We are coming from a new sharedresource instance call.
        $params = array('course' => $course->id,
                        'section' => $section,
                        'type' => $type,
                        'add' => 'sharedresource',
                        'return' => $return,
                        'entryid' => $shrentry->id);
        $fullurl = new moodle_url('/course/modedit.php', $params);
    }
    redirect($fullurl, get_string('correctsave', 'sharedresource'), 5);
}
=======
}

// Build and print the page.

$pagetitle = strip_tags($course->shortname);
$strtitle = $pagetitle;
$PAGE->set_pagelayout('standard');
$system_context = context_system::instance();
$PAGE->set_context($system_context);
$urlparams = array('mode' => $mode, 'course' => $course->id, 'section' => $section, 'return' => $return);
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

