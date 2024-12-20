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
 * Displays the filled fields of the metadata form and save these metadata and the resource.
 *
 * It informs the user if there are some errors and in that
 * case, the resource is not saved and the user is sent back
 * to the metadata form
 *
 * @package     mod_sharedresource
 * @author      Frederic GUILLOU
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/*
 * sharedresource_check_access does the job.
 * phpcs:disable moodle.Files.RequireLogin.Missing
 * Prerequires perturbate config.php inclusion detection.
 * phpcs:disable moodle.Files.MoodleInternal.MoodleInternalGlobalState
 */

// Here we need load some classes before config and session is setup to help unserializing.
require_once(dirname(__FILE__).'/classes/__autoload.php');

require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_metadata.class.php');

use mod_sharedresource\metadata;

$config = get_config('sharedresource');

$mode = required_param('mode', PARAM_ALPHA);
$fromlibrary = optional_param('fromlibrary', 1, PARAM_BOOL); // Return to course or library.
$returnpage = required_param('returnpage', PARAM_TEXT); // Return to course or mod form, either browse or explore in library.
$section = optional_param('section', 0, PARAM_INT);
$courseid = required_param('course', PARAM_INT);
$catid = optional_param('catid', 0, PARAM_INT);
$catpath = optional_param('catpath', '', PARAM_TEXT);
$sharingcontext = optional_param('context', 1, PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

// Security.

$pagecontext = sharedresource_check_access($course);

$mtdstandard = sharedresource_get_plugin($config->schema);

// Receive input from form.
$metadataentries = data_submitted();
if (property_exists($metadataentries, 'cancel')) {
    if (in_array($returnpage, ['explore', 'browse'])) {
        // Edition process was comming from shared library.
        $params = [
            'course' => $courseid,
            'returnpage' => $returnpage,
        ];
        $cancelurl = new moodle_url('/local/sharedresources/index.php', $params);
        redirect($cancelurl);
    }
    $params = [
        'course' => $courseid,
        'section' => $section,
        'add' => 'sharedresource',
        'return' => $returnpage,
    ];
    $cancelurl = new moodle_url('/course/modedit.php', $params);
    redirect($cancelurl);
}

$pagetitle = strip_tags($course->shortname);
$strtitle = $pagetitle;
$PAGE->set_pagelayout('standard');
$systemcontext = context_system::instance();
$PAGE->set_context($pagecontext);
$url = new moodle_url('/mod/sharedresource/metadatarep.php');
$PAGE->set_url($url);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);

// Navigation.

$linkurl = new moodle_url('/mod/sharedresource/index.php', ['id' => $course->id]);
$PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'), $linkurl, 'activity');
$PAGE->navbar->add($strtitle, 'metadatarep.php', 'misc');
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

metadata::normalize_storage($shrentry->id);

// If there are errors in fields filled in by the user.

if ($result['error'] != []) {
    $srentry = serialize($shrentry);
    $SESSION->sr_entry = $srentry;
    $error = serialize($result['error']);
    $SESSION->error = $error;

    echo $OUTPUT->header();

    echo $OUTPUT->heading(get_string($mode.'sharedresourcetypefile', 'sharedresource'));

    $errortpl = new StdClass();

    foreach ($result['error'] as $field => $errortype) {
        $fieldnum = substr($field, 0, strpos($field, ':'));
        $errtpl = new StdClass();
        $errtpl->fieldnum = $fieldnum;
        $errtpl->fieldname = $mtdstandard->metadatatree[$fieldnum]['name'];
        $errortpl->errors[] = $errtpl;
    }

    $errortpl->errormetadatastr = get_string('errormetadata', 'sharedresource');

    $OUTPUT->render_from_template('mod_sharedresource/metadatacheckerrors', $errortpl);

    $OUTPUT->render_from_template('mod_sharedresource/metadatacheckreport', $result['display']);

    $params = [
        'course' => $course->id,
        'section' => $section,
        'add' => 'sharedresource',
        'catid' => $catid,
        'catpath' => $catpath,
        'fromlibrary' => $fromlibrary,
        'returnpage' => $returnpage,
        'mode' => $mode,
        'context' => $sharingcontext,
    ];
    $fullurl = new moodle_url('/mod/sharedresource/metadataform.php', $params);

    echo '<center>';
    $OUTPUT->continue($fullurl, get_string('wrongform', 'sharedresource'), 15);
    echo '</center>';

    echo $OUTPUT->footer();
    die;
}

// No errors in metadata.
// These two lines in comment can be used if you want to show the user values of saved fields.
if ($mode == 'add' && $shrentry->exists()) {

    // Save updated state in session.
    $srentry = serialize($shrentry);
    $SESSION->sr_entry = $srentry;

    // We are coming from the library. Go back to it.
    $params = [
        'course' => $course->id,
        'mode' => 'add',
        'add' => 1,
        'catid' => $catid,
        'catpath' => $catpath,
        'returnpage' => $returnpage,
        'fromlibrary' => $fromlibrary,
        'section' => $section,
        'context' => $sharingcontext,
    ];
    $fullurl = new moodle_url('/mod/sharedresource/metadataupdateconfirm.php', $params);
    redirect($fullurl);

} else if ($mode == 'add') {

    // Add a real new instance.
    if (!$shrentry->add_instance()) {
        throw new moodle_exception('failadd', 'sharedresource');
    }
    // If everything was saved correctly, go back to the search page or to the library.
    if ($fromlibrary) {
        // We are coming from the library. Go back to it using index.php router.
        $params = [
            'course' => $course->id,
            'section' => $section,
            'catid' => $catid,
            'catpath' => $catpath,
            'returnpage' => $returnpage,
        ];
        $fullurl = new moodle_url('/local/sharedresources/index.php', $params);
        redirect($fullurl, get_string('correctsave', 'sharedresource'), 5);
    } else {
        // We are coming from a new sharedresource instance call.
        $params = [
            'course' => $course->id,
            'section' => $section,
            'type' => $type,
            'add' => 'sharedresource',
            'return' => $returnpage,
            'entryid' => $shrentry->id,
        ];
        $fullurl = new moodle_url('/course/modedit.php', $params);
        redirect($fullurl, get_string('correctsave', 'sharedresource'), 5);
    }
} else if ($mode == 'update') {

    /*
     * Here we need update the instance. Update will react to any identifier alteration, by building
     * a complete new entry linked to the current unaltered entry by metadata chain.
     */
    if (!$shrentry->update_instance()) {
        unset($SESSION->sr_must_clone_to);
        throw new moodle_exception(get_string('failupdate', 'sharedresource'));
    }

    // We are coming necessarily from the library. Go back to it using index.php router.
    $params = [
        'course' => $course->id,
        'section' => $section,
        'catid' => $catid,
        'catpath' => $catpath,
        'returnpage' => $returnpage,
    ];
    $fullurl = new moodle_url('/local/sharedresources/index.php', $params);

    redirect($fullurl, get_string('correctsave', 'sharedresource'), 5);
}
