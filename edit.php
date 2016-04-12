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
 * @author  Valery Fremaux  valery.fremaux@gmail.com
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package sharedresource
 *
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/sharedresource_entry_form.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->libdir.'/filelib.php');

// Load metadata plugin.
require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$CFG->pluginchoice.'/plugin.class.php');
$object = 'sharedresource_plugin_'.$CFG->pluginchoice;
$mtdstandard = new $object;

$ignore_list = array(
    'mform_showadvanced_last', 
    /* 'pagestep', */ 
    'MAX_FILE_SIZE', 
    'add', 
    'update', 
    'return', 
    'type', 
    'section', 
    'mode', 
    'course', 
    'submitbutton'
);

$ignore_list = array_merge($ignore_list, $mtdstandard->sharedresource_get_ignored());

// Get params.

$add           = optional_param('add', 0, PARAM_ALPHA);
$update        = optional_param('update', 0, PARAM_INT);
$return        = optional_param('return', 0, PARAM_BOOL); //return to course/view.php if false or mod/modname/view.php if true
$type          = optional_param('type', '', PARAM_ALPHANUM);
$section       = optional_param('section', 0, PARAM_INT);
$mode          = required_param('mode', PARAM_ALPHA);
$course        = required_param('course', PARAM_INT);
// $pagestep      = optional_param('pagestep', 1, PARAM_INT);
$sharingcontext  = optional_param('context', SITEID, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $course))) {
    print_error('coursemisconf');
}

// Security.

$system_context = context_system::instance();
$context = context_course::instance($course->id);
require_login($course);
require_capability('moodle/course:manageactivities', $context);

// Page construction.

$strtitle = get_string($mode.'sharedresourcetypefile', 'sharedresource');
$PAGE->set_pagelayout('standard');
$PAGE->set_context($system_context);
$url = new moodle_url('/mod/sharedresource/edit.php');
$PAGE->set_url($url);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'), "{$CFG->wwwroot}/mod/sharedresource/index.php?id=$course->id");
$PAGE->navbar->add(get_string($mode.'sharedresourcetypefile', 'sharedresource'));
$PAGE->navbar->add($strtitle,'edit.php','misc');
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');

$pagetitle = strip_tags($course->shortname);

$formdata = new StdClass();

// Sort out how we should look depending on add or update.

if ($mode == 'update') {

    $entryid = required_param('entry_id', PARAM_INT);
    $sharedresource_entry = sharedresource_entry::get_by_id($entryid);
    $strpreview = get_string('preview', 'sharedresource');

    // Make a flat record for feeding the form.
    $formdata = $sharedresource_entry->sharedresource_entry;

    if (empty($CFG->sharedresource_foreignurl)) {
        // Resource preview is on the same server it is accessible. openpopup can be used.
        $formdata->url_display =  "<a href=\"{$CFG->wwwroot}/mod/sharedresource/view.php?identifier={$sharedresource_entry->identifier}&amp;inpopup=true\" "
          . "onclick=\"this.target='resource{$sharedresource_entry->id}'; return openpopup('/mod/sharedresource/view.php?inpopup=true&amp;identifier={$sharedresource_entry->identifier}', "
          . "'resource{$sharedresource_entry->id}','resizable=1,scrollbars=1,directories=1,location=0,menubar=0,toolbar=0,status=1,width=800,height=600');\">(".$strpreview.")</a>";
    } else {
        // Resource preview changes apparent domain of the resource. openpopup fails.
        $url = str_replace('<%%ID%%>', $sharedresource_entry->identifier, $CFG->sharedresource_foreignurl);
        $formdata->url_display = "<a href=\"{$url}\" target=\"_blank\">(".$strpreview.")</a>";
    }

    // @TODO : this should call the file storage API 
    $formdata->filename = $DB->get_field('files', 'filename', array('id' => $sharedresource_entry->file));

} else {
    $mode = 'add';
    $sharedresource_entry = new sharedresource_entry(false);
}

$mform = false;
$mform = new mod_sharedresource_entry_form($mode);
$mform->set_data(($formdata));

if ($mform->is_cancelled()) {
    //cancel - go back to course
    redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
}

// Is this a successful POST ?

if (($formdata = $mform->get_data()) || ($sharedresourcefile = optional_param('sharedresourcefile', null, PARAM_INT))) {

    // Fake feed formdata with directly query params when call for addition comes from other sources
    if (empty($formdata)) {
        $formdata = new StdClass();
        $formdata->sharedresourcefile = $sharedresourcefile;
    }

    // Check for hidden values.
    if ($hidden = optional_param('sharedresource_hidden', '', PARAM_CLEANHTML)) {
        $hidden = explode('|', $hidden);
        foreach ($hidden as $field) {
            $formdata->$field = sharedresource_clean_field($field);
        }
    }

    // Process the form contents.
    // Add form data to table object - skip the elements until we know what the identifier is.
    foreach ($formdata as $key => $value) {
        if (in_array($key, $SHAREDRESOURCE_CORE_ELEMENTS) && !empty($value)) {
            if ($key == 'url') {
                $sharedresource_entry->add_element($key, clean_param($value, PARAM_URL));
            } else {
                if (is_array($value)) {
                    $sharedresource_entry->add_element($key, clean_param_array($value, PARAM_CLEANHTML));
                } else {
                    $sharedresource_entry->add_element($key, clean_param($value, PARAM_CLEANHTML));
                }
            }
        }
    }

    $sharedresource_entry->lang = $USER->lang;

    $fs = get_file_storage();

    if ($mode == 'add') {
        // Locally defined resource ie. we are the master.
        $sharedresource_entry->type = 'file'; // obsolete ?

        // Is this a local resource or a remote one?
        if (!empty($formdata->url)) {
            $sharedresource_entry->url = $formdata->url;
            $sharedresource_entry->file = '';
            $sharedresource_entry->identifier = sha1($sharedresource_entry->url);
            $sharedresource_entry->mimetype = mimeinfo('type', $sharedresource_entry->url);
        } else {
            // If resource is a real file we necessarily have one in the user's filepicker temp file area.
            $filepickeritemid = $formdata->sharedresourcefile;
            $context = context_user::instance($USER->id);
            if (!$draftfiles = $fs->get_area_files($context->id, 'user', 'draft', $filepickeritemid, 'id DESC', false)) {
                print_error('errorprogramming', 'sharedresource');
            }
            $file = reset($draftfiles);

            $sharedresource_entry->identifier = $file->get_contenthash();
            $sharedresource_entry->file = $file->get_id(); // this temp file will be post processed at the end of the storage process

            // $formdata->identifier = $sharedresource_entry->identifier;
            // $formdata->file = $sharedresource_entry->file;
            // $formdata->uploadname = $file->get_filename();// $sharedresource_entry->uploadname;
            // $formdata->mimetype = $sharedresource_entry->mimetype;
            $sharedresource_entry->url = '';
        }
    }

    // Prepare thumbnail if any
    if (empty($formdata->thumbnailgroup['clearthumbnail'])) {
        $thumbnailpickeritemid = $formdata->thumbnailgroup['thumbnail'];
        $thumbnailfile = false;
        if (!$draftfiles = $fs->get_area_files($context->id, 'user', 'draft', $thumbnailpickeritemid, 'id DESC', false)) {
            $thumbnailfile = reset($draftfiles);
        }
        if ($thumbnailfile) {
            // This temp file will be post processed at the end of the storage process.
            $sharedresource_entry->thumbnail = $thumbnailfile->get_id();
        }
    } else {
        unset($formdata->thumbnailgroup);
    }

    $sr_entry = serialize($sharedresource_entry);
    $SESSION->sr_entry = $sr_entry;
    $error = 'no error';
    $SESSION->error = $error;

    $params = array('course' => $course->id,
                    'section' => $section,
                    'type' => $type,
                    'add' => 'sharedresource',
                    'return' => $return,
                    'mode' => $mode,
                    'context' => $sharingcontext);
    $fullurl = new moodle_url('/mod/sharedresource/metadataform.php', $params);
    redirect($fullurl);
}

// Do we have hidden elements that we need to save.
if ($hidden = optional_param('sharedresource_hidden', '', PARAM_CLEANHTML)) {
    $hidden = explode('|', $hidden);
    foreach ($hidden as $field) {
        $value = sharedresource_clean_field($field);
        $mform->_form->addElement('hidden', $field, $value);
    }
    $mform->_form->addElement('hidden', 'sharedresource_hidden', join('|', $hidden));
}

echo $OUTPUT->header();

// display form
$mform->display();
echo $OUTPUT->footer($course);
// page local functions
// grab and clean form value

function sharedresource_clean_field($field) {
    switch ($field) {
        case 'identifier':
            $value = optional_param($field, '', PARAM_BASE64);
            break;
        case 'file':
            $value = optional_param($field, '', PARAM_PATH);
            break;
        case 'mimetype':
            $value = optional_param($field, '', PARAM_URL);
            break;
        default:
            $value = optional_param($field, '', PARAM_RAW);
            break;
    }
    return $value;
}
