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
 * Edit a sharedresource publication.
 *
 * @package     mod_sharedresource
 * @author      Piers Harding  piers@catalyst.net.nz, Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/*
 * Because classes preloading (SHAREDRESOURCE_INTERNAL) perturbates config detection.
 * phpcs:disable moodle.Files.MoodleInternal.MoodleInternalGlobalState
 * sharedresource_check_access does the job.
 * phpcs:disable moodle.Files.RequireLogin.Missing
 */

// Here we need load some classes before config and session is setup to help unserializing.
require_once(dirname(__FILE__).'/classes/__autoload.php');

require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/forms/sharedresource_entry_form.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->libdir.'/filelib.php');

$config = get_config('sharedresource');

// Load metadata plugin.
require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$config->schema.'/plugin.class.php');
$mtdclass = '\\mod_sharedresource\\plugin_'.$config->schema;
$mtdstandard = new $mtdclass();

$ignorelist = [
    'mform_showadvanced_last',
    'MAX_FILE_SIZE',
    'return',
    'type',
    'section',
    'fromlibrary',
    'returnpage',
    'mode',
    'course',
    'submitbutton',
];

$ignorelist = array_merge($ignorelist, $mtdstandard->sharedresource_get_ignored());

// Get params.

$fromlibrary = optional_param('fromlibrary', 1, PARAM_BOOL); // Tells if we return to course (0) or to library (1).
$returnpage = optional_param('returnpage', 0, PARAM_TEXT); // Tells where to go when return.
$section = optional_param('section', 0, PARAM_INT); // Memorise the return course section in case we return to course in workflow.
$entryid = optional_param('entryid', 0, PARAM_INT); // When updating, or on late workflow steps.
$catid = optional_param('catid', 0, PARAM_INT); // Memorise the library catid, when coming from library.
$catpath = optional_param('catpath', '', PARAM_TEXT); // Memorise the full catpath when coming from library (helper).
$courseid = required_param('course', PARAM_INT); // The origin course, or course in context of use by the library.
$sharingcontext = optional_param('context', SITEID, PARAM_INT); // Memorize the sharing context in workflow.
$type = optional_param('type', '', PARAM_ALPHANUM); // Feeds back the resource type.
$mode = required_param('mode', PARAM_ALPHA); // Add or update workflow.

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

// Security.
$pagecontext = sharedresource_check_access($course);

// Compute returnurl.
if (!$fromlibrary) {
    // Origin action was adding a sharedresource instance in course.
    $returnurl = new moodle_url('/course/view.php', ['id' => $course->id, 'section' => $section]);
} else {
    // Origin is the shared resource library, in a site or course context. Preserve it.
    $params = ['course' => $course->id,
               'section' => $section,
               'fromlibrary' => $fromlibrary,
               'returnpage' => $returnpage,
               'catid' => $catid,
               'catpath' => $catpath];
    $returnurl = new moodle_url('/local/sharedresources/index.php', $params);
}

// Page construction.

$strtitle = get_string($mode.'sharedresourcetypefile', 'sharedresource');
$PAGE->set_pagelayout('standard');
$PAGE->set_context($pagecontext);
$params = ['mode' => $mode,
           'course' => $course->id,
           'entryid' => $entryid,
           'type' => $type,
           'section' => $section,
           'catid' => $catid,
           'catpath' => $catpath,
           'returnpage' => $returnpage,
           'fromlibrary' => $fromlibrary,
           'sharingcontext' => $sharingcontext,
];
$url = new moodle_url('/mod/sharedresource/edit.php', $params);
$PAGE->set_url($url);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
// Review navbar construction to be more consistant.
if ($course->id > SITEID) {
    $PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php', ['id' => $course->id, 'section' => $section]));
}
$PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'), $returnurl);
$PAGE->navbar->add(get_string($mode.'sharedresourcetype'.$type, 'sharedresource'));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');

$pagetitle = strip_tags($course->shortname);

// Sort out how we should look depending on add or update.

if ($mode == 'update') {

    $entryid = required_param('entryid', PARAM_INT);
    $shrentry = \mod_sharedresource\entry::get_by_id($entryid);
    $strpreview = get_string('preview', 'sharedresource');

    // Make a flat record for feeding the form.
    $updateformdata = $shrentry->get_record();

    if (empty($config->foreignurl)) {
        // Resource preview is on the same server it is accessible. openpopup can be used.
        $displayurl = new moodle_url('/mod/sharedresource/view.php', ['identifier' => $shrentry->identifier, 'inpopup' => true]);
        $jshandler = 'this.target=\'resource'.$shrentry->id.'\';';
        $jshandler .= 'return openpopup(\'/mod/sharedresource/view.php?inpopup=true&identifier={$shrentry->identifier}\', ';
        $jshandler .= '\'resource'.$shrentry->id.'\', \'resizable=1,scrollbars=1,directories=1,
                location=0,menubar=0,toolbar=0,status=1,width=800,height=600\');\';';
        $updateformdata->url_display = '<a href="'.$displayurl.'" onclick="'.$jshandler.'">('.$strpreview.')</a>';
    } else {
        // Resource preview changes apparent domain of the resource. openpopup fails.
        $url = str_replace('<%%ID%%>', $shrentry->identifier, $CFG->sharedresource_foreignurl);
        $updateformdata->url_display = '<a href="'.$url.'" target="_blank">('.$strpreview.')</a>';
    }

    // TODO : this should call the file storage API.
    $updateformdata->filename = $DB->get_field('files', 'filename', ['id' => $shrentry->file]);

} else {
    $mode = 'add';
    $entryclass = \mod_sharedresource\entry_factory::get_entry_class();
    $shrentry = new $entryclass(null);
}

$mform = false;
$mform = new mod_sharedresource_entry_form($url, ['mode' => $mode, 'entry' => $shrentry]);

if ($mform->is_cancelled()) {
    // Discard session image of the edited resource.
    unset($SESSION->sr_entry);
    redirect($returnurl);
}

// Is this a successful POST ?
$formdata = $mform->get_data();

if ($formdata) {

    // Fake feed formdata with directly query params when call for addition comes from other sources.
    if (empty($formdata)) {
        $formdata = new StdClass();
        $formdata->catid = $catid;
        $formdata->catpath = $catpath;
        $formdata->sharedresourcefile = $sharedresourcefile;
    }

    $shrentry->lang = $USER->lang;

    $fs = get_file_storage();

    if ($mode == 'add') {

        // Be sure we have a clean session.
        unset($SESSION->sr_must_clone_to);
        unset($SESSION->sr_no_identifier_change);

        // Locally defined resource ie. we are the master.
        $shrentry->type = 'file';
        $hasentry = false;

        $filepickeritemid = $formdata->sharedresourcefile;
        $context = context_user::instance($USER->id);
        $file = null;
        if ($draftfiles = $fs->get_area_files($context->id, 'user', 'draft', $filepickeritemid, 'id DESC', false)) {
            if (!empty($draftfiles)) {
                $file = reset($draftfiles);
            }
        }

        // Is this a local resource or a remote one?
        if (is_null($file) && !empty($formdata->url)) {
            $shrentry->url = $formdata->url;
            $shrentry->file = '';
            $shrentry->type = 'url';
            $shrentry->identifier = sha1($shrentry->url);
            $shrentry->mimetype = mimeinfo('type', $shrentry->url);
            $hasentry = true;
        } else if (!empty($file)) {
            // If resource is a real file we necessarily have one in the user's filepicker temp file area.
            $shrentry->identifier = $file->get_contenthash();
            $shrentry->file = $file->get_id(); // This temp file will be post processed at the end of the storage process.
            $shrentry->url = '';
            $hasentry = true;
        }
    } else if ($mode == 'update') {

        /*
         * mode is update.
         * We need to check if the new hashidentifier has changed. In this case, we need to create an complete cloned
         * entry and link them through a version chain (Using metadata relation). then we will ask for metadata changes.
         */
        $hasentry = true;
        if (empty($formdata->sharedresourcefile) && !empty($formdata->url)) {
            $newidentifier = sha1($formdata->url);
            if ($shrentry->identifier != $newidentifier) {
                echo " URL Marking session clone_to as old id {$shrentry->identifier} differs from new : $newidentifier ";
                $SESSION->sr_must_clone_to = $newidentifier;
                unset($SESSION->sr_no_identifier_change);
            } else {
                echo " URL UNMarking session clone_to as old id {$shrentry->identifier} equals new : $newidentifier ";
                unset($SESSION->sr_must_clone_to);
                $SESSION->sr_no_identifier_change = $newidentifier;
            }
        } else if (!empty($formdata->sharedresourcefile)) {
            // If resource is a real file we necessarily have one in the user's filepicker temp file area.
            $filepickeritemid = $formdata->sharedresourcefile;
            $context = context_user::instance($USER->id);
            if ($draftfiles = $fs->get_area_files($context->id, 'user', 'draft', $filepickeritemid, 'id DESC', false)) {
                $file = reset($draftfiles);

                $newidentifier = $file->get_contenthash();
                if ($shrentry->identifier != $newidentifier) {
                    // Save a reference in session to make metadatabinding on late save.
                    echo " FILE Marking session clone_to as old id {$shrentry->identifier} differs from new : $newidentifier ";
                    $SESSION->sr_must_clone_to = $newidentifier;
                    unset($SESSION->sr_no_identifier_change);
                } else {
                    echo " FILE UNMarking session clone_to as old id {$shrentry->identifier} equals new : $newidentifier ";
                    $SESSION->sr_no_identifier_change = $newidentifier;
                    unset($SESSION->sr_must_clone_to);
                }
                // Refresh session.
            } else {
                // Empty submission case.
                throw new moodle_exception("Should not happen. Reinforce form control if necessary.");
            }
        }
    } else {
        // Last edition step with metadata received.
        $params = ['course' => $course->id,
                   'fromlibrary' => $fromlibrary,
                   'returnpage' => $returnpage,
                   'section' => $section,
                   'context' => $sharingcontext,
                   'catid' => $catid,
                   'catpath' => $catpath];
        $fullurl = new moodle_url('/mod/sharedresource/metadatapreupdateconfirm.php', $params);
        redirect($fullurl);
    }

    if ($shrentry->exists()) {
        $srentry = serialize($shrentry);
        $SESSION->sr_entry = $srentry;
    }

    // Means add or update... common processing.
    $shrentry->thumbnail = null;
    if ($hasentry) {

        // Prepare thumbnail if any.
        if (empty($formdata->thumbnailgroup['clearthumbnail'])) {
            $thumbnailpickeritemid = $formdata->thumbnailgroup['thumbnail'];
            $thumbnailfile = false;
            $usercontext = context_user::instance($USER->id);
            if ($draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $thumbnailpickeritemid, 'id DESC', false)) {
                $thumbnailfile = reset($draftfiles);
            }
            if ($thumbnailfile) {
                // This temp file will be post processed at the end of the storage process.
                $shrentry->thumbnail = $thumbnailfile->get_id();
            }
        } else {
            unset($formdata->thumbnailgroup);
        }

        $shrentry->title = $formdata->title;
        if (is_array($formdata->description)) {
            $shrentry->description = $formdata->description['text'];
        } else {
            $shrentry->description = $formdata->description;
        }

        // Update in session till last save stage.
        $srentry = serialize($shrentry);
        $SESSION->sr_entry = $srentry;
        $error = 'no error';
        $SESSION->error = $error;

        $params = [
            'course' => $course->id,
            'section' => $section,
            'type' => $type,
            'fromlibrary' => $fromlibrary,
            'returnpage' => $returnpage,
            'mode' => $mode,
            'context' => $sharingcontext,
            'catid' => $catid,
            'catpath' => $catpath,
        ];
        $fullurl = new moodle_url('/mod/sharedresource/forms/metadata_form.php', $params);
		echo $OUTPUT->continue($fullurl);
        // redirect($fullurl);
    }
}

if (isset($updateformdata)) {
    $mform->set_data($updateformdata);
}

echo $OUTPUT->header();

// Display form.
unset($SESSION->sr_must_clone_to);
unset($SESSION->sr_no_identifier_change);
$mform->display();
echo $OUTPUT->footer($course);
