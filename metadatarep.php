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
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_metadata.class.php');

$config = get_config('sharedresource');

require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$config->schema.'/plugin.class.php');

$mode = required_param('mode', PARAM_ALPHA);
$add = optional_param('add', 0, PARAM_ALPHA);
$update = optional_param('update', 0, PARAM_INT);
$return = optional_param('return', 0, PARAM_BOOL); // Return to course/view.php if false or mod/modname/view.php if true.
$section = optional_param('section', 0, PARAM_INT);
$courseid = required_param('course', PARAM_INT);
$type = 'file';
$sharingcontext = optional_param('context', 1, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('badcourseid', 'sharedresource');
}

require_login($course);
$context = context_course::instance($courseid);
require_capability('repository/sharedresources:create', $context);

$mtdstandard = sharedresource_get_plugin($config->schema);

// Receive input from form.
$metadataentries = data_submitted();
if (array_key_exists('cancel', $metadataentries)) {
    $params = array('course' => $courseid, 'section' => $section, 'add' => 'sharedresource', 'return' => $return);
    $cancelurl = new moodle_url('/course/modedit.php', $params);
    redirect($cancelurl);
}

$pagetitle = strip_tags($course->shortname);
$strtitle = $pagetitle;
$PAGE->set_pagelayout('standard');
$system_context = context_system::instance();
$PAGE->set_context($system_context);
$url = new moodle_url('/mod/sharedresource/metadatarep.php');
$PAGE->set_url($url);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);

// Navigation.

$linkurl = new moodle_url('/mod/sharedresource/index.php', array('id' => $course->id));
$PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'), $linkurl, 'activity');
$PAGE->navbar->add($strtitle,'metadatarep.php','misc');
$PAGE->navbar->add(get_string($mode.'sharedresourcetypefile', 'sharedresource'));

$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_headingmenu('');

$SESSION->error = '';
$srentry = $SESSION->sr_entry;
$shrentry = unserialize($srentry);

// If it's an update, metadata of the sharedresource should be deleted before adding new ones.
if ($mode != 'add') {
    foreach ($shrentry->metadataelements as $key => $metadata) {
        unset($shrentry->metadataelements[$key]);
    }
}
$result = metadata_display_and_check($shrentry, $metadataentries);
\mod_sharedresource\metadata::normalize_storage($shrentry->id);

// If there are errors in fields filled in by the user.

if ($result['error'] != array()) {
    $srentry = serialize($shrentry);
    $SESSION->sr_entry = $srentry;
    $error = serialize($result['error']);
    $SESSION->error = $error;

    echo $OUTPUT->header();

    echo $OUTPUT->heading(get_string($mode.'sharedresourcetypefile', 'sharedresource'));

    $errortpl = new StdClass;

    foreach ($result['error'] as $field => $errortype) {
        $fieldnum = substr($field, 0, strpos($field,':'));
        $errtpl = new StdClass;
        $errtpl->fieldnum = $fieldnum;
        $errtpl->fieldname = $mtdstandard->METADATATREE[$fieldnum]['name'];
        $errortpl->errors[] = $errtpl;
    }

    $errortpl->errormetadatastr = get_string('errormetadata', 'sharedresource');

    $OUTPUT->render_from_template('mod_sharedresource/metadatacheckerrors', $errortpl);

    $OUTPUT->render_from_template('mod_sharedresource/metadatacheckreport', $result['display']);

    $params = array('course' => $course->id,
                    'section' => $section,
                    'add' => 'sharedresource',
                    'return' => $return,
                    'mode' => $mode,
                    'context' => $sharingcontext);

    $fullurl = new moodle_url('/mod/sharedresource/metadataform.php', $params);

    echo '<center>';
    $OUTPUT->continue($fullurl, get_string('wrongform', 'sharedresource'), 15);
    echo '</center>';

    echo $OUTPUT->footer();

} else {
    // No errors in metadata.
    // These two lines in comment can be used if you want to show the user values of saved fields.
    if ($mode == 'add' && $shrentry->exists()) {

        // Save updated state in session.
        $srentry = serialize($shrentry);
        $SESSION->sr_entry = $srentry;

        // We are coming from the library. Go back to it.
        $params = array('course' => $course->id,
                        'mode' => 'add',
                        'add' => 1,
                        'return' => $return,
                        'section' => $section,
                        'context' => $sharingcontext);
        $fullurl = new moodle_url('/mod/sharedresource/metadataupdateconfirm.php', $params);
        redirect($fullurl);

    } else if ($mode == 'update') {
        if (!$shrentry->update_instance()) {
            print_error('failupdate', 'sharedresource');
        }
        $fullurl = new moodle_url('/local/sharedresources/index.php', array('course' => $course->id));
        redirect($fullurl, get_string('correctsave', 'sharedresource'), 5);
    } else {
        if (!$shrentry->add_instance()) {
            print_error('failadd', 'sharedresource');
        }
        // If everything was saved correctly, go back to the search page or to the library.
        if ($return) {
            // We are coming from the library. Go back to it.
            $fullurl = new moodle_url('/local/sharedresources/index.php', array('course' => $course->id));
            redirect($fullurl, get_string('correctsave', 'sharedresource'), 5);
        } else {
            // We are coming from a new sharedresource instance call.
            $params = array('course' => $course->id,
                            'section' => $section,
                            'type' => $type,
                            'add' => 'sharedresource',
                            'return' => $return,
                            'entryid' => $shrentry->id);
            $fullurl = new moodle_url('/course/modedit.php', $params);
            redirect($fullurl, get_string('correctsave', 'sharedresource'), 5);
        }
        die;
    }
}
