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
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package    sharedresource
 * @category   mod
 */
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

$ignorelist = array(
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

$ignorelist = array_merge($ignorelist, $mtdstandard->sharedresource_get_ignored());

// Get params.

$add = optional_param('add', 0, PARAM_ALPHA);
$update = optional_param('update', 0, PARAM_INT);
$return = optional_param('return', 0, PARAM_INT);
$type = optional_param('type', '', PARAM_ALPHANUM);
$section = optional_param('section', 0, PARAM_INT);
$mode = required_param('mode', PARAM_ALPHA);
$catid = optional_param('catid', 0, PARAM_INT);
$catpath = optional_param('catpath', '', PARAM_TEXT);
$course = required_param('course', PARAM_INT);
$sharingcontext = optional_param('context', SITEID, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $course))) {
    print_error('coursemisconf');
}

// Security.

$systemcontext = context_system::instance();
$context = context_course::instance($course->id);
if ($course->id > 1) {
    require_login($course);
    require_capability('moodle/course:manageactivities', $context);
} else {
    // Use system level test as shortpath.
    $caps = array('repository/sharedresources:create', 'repository/sharedresources:manage');
    if (!has_any_capability($caps, context_system::instance())) {
        if (!sharedresources_has_capability_somewhere('repository/sharedresources:create', false, false, false, CONTEXT_COURSECAT.','.CONTEXT_COURSE)) {
            print_error('noaccess');
        }
    }
}

// Compute returnurl.
if ($return == 1) {
    $returnurl = new moodle_url('/local/sharedresource/index.php', array('id' => $course->id));
} else {
    if ($course->id > SITEID) {
        $returnurl = new moodle_url('/mod/sharedresource/index.php', array('id' => $course->id));
    } else {
        $returnurl = new moodle_url('/course/view.php', array('id' => $course->id));
    }
}

// Page construction.

$strtitle = get_string($mode.'sharedresourcetypefile', 'sharedresource');
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
$params = array('mode' => $mode, 'course' => $course->id, 'sharingcontext' => $sharingcontext, 'add' => $add, 'update' => $update);
$url = new moodle_url('/mod/sharedresource/edit.php', $params);
$PAGE->set_url($url);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'), $returnurl);
$PAGE->navbar->add(get_string($mode.'sharedresourcetypefile', 'sharedresource'));
$PAGE->navbar->add($strtitle, 'edit.php', 'misc');
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');

$pagetitle = strip_tags($course->shortname);

$formdata = new StdClass();

// Sort out how we should look depending on add or update.

if ($mode == 'update') {

    $entryid = required_param('entryid', PARAM_INT);
    $shrentry = \mod_sharedresource\entry::get_by_id($entryid);
    $strpreview = get_string('preview', 'sharedresource');

    // Make a flat record for feeding the form.
    $formdata = $shrentry->get_record();

    if (empty($config->foreignurl)) {
        // Resource preview is on the same server it is accessible. openpopup can be used.
        $displayurl = new moodle_url('/mod/sharedresource/view.php', array('identifier' => $shrentry->identifier, 'inpopup' => true));
        $jshandler = 'this.target=\'resource'.$shrentry->id.'\';';
        $jshandler .= 'return openpopup(\'/mod/sharedresource/view.php?inpopup=true&identifier={$shrentry->identifier}\', ';
        $jshandler .= '\'resource'.$shrentry->id.'\', \'resizable=1,scrollbars=1,directories=1,location=0,menubar=0,toolbar=0,status=1,width=800,height=600\');\';';
        $formdata->url_display = '<a href="'.$displayurl.'" onclick="'.$jshandler.'">('.$strpreview.')</a>';
    } else {
        // Resource preview changes apparent domain of the resource. openpopup fails.
        $url = str_replace('<%%ID%%>', $shrentry->identifier, $CFG->sharedresource_foreignurl);
        $formdata->url_display = '<a href="'.$url.'" target="_blank">('.$strpreview.')</a>';
    }

    // @TODO : this should call the file storage API
    $formdata->filename = $DB->get_field('files', 'filename', array('id' => $shrentry->file));

} else {
    $mode = 'add';
    $entryclass = \mod_sharedresource\entry_factory::get_entry_class();
    $shrentry = new $entryclass(null, null);
}

$formdata->catid = $catid;
$formdata->catpath = $catpath;

$mform = false;
$mform = new mod_sharedresource_entry_form($mode);
$mform->set_data(($formdata));

if ($mform->is_cancelled()) {
    unset($SESSION->sr_entry);
    redirect($returnurl);
}

// Is this a successful POST ?

if (($formdata = $mform->get_data()) ||
        ($sharedresourcefile = optional_param('sharedresourcefile', null, PARAM_INT))) {

    if (empty($formdata)) {
        $formdata = new StdClass;
    }
    $formdata->catid = $catid;
    $formdata->catpath = $catpath;

    // Fake feed formdata with directly query params when call for addition comes from other sources.
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
                $shrentry->add_element($key, clean_param($value, PARAM_URL));
            } else {
                if (is_array($value)) {
                    $shrentry->add_element($key, clean_param_array($value, PARAM_CLEANHTML));
                } else {
                    $shrentry->add_element($key, clean_param($value, PARAM_CLEANHTML));
                }
            }
        }
    }

    $shrentry->lang = $USER->lang;

    $fs = get_file_storage();

    if ($mode == 'add') {
        $hasentry = false;
        // Locally defined resource ie. we are the master.
        $shrentry->type = 'file'; // Obsolete ?

        $hasentry = false;
        // Is this a local resource or a remote one?
        if (!empty($formdata->url)) {
            $shrentry->url = $formdata->url;
            $shrentry->file = '';
            $shrentry->identifier = sha1($shrentry->url);
            $shrentry->mimetype = mimeinfo('type', $shrentry->url);
            $hasentry = true;
        } else {
            // If resource is a real file we necessarily have one in the user's filepicker temp file area.
            $filepickeritemid = $formdata->sharedresourcefile;
            $context = context_user::instance($USER->id);
            if ($draftfiles = $fs->get_area_files($context->id, 'user', 'draft', $filepickeritemid, 'id DESC', false)) {
                $file = reset($draftfiles);

                $shrentry->identifier = $file->get_contenthash();
                $shrentry->file = $file->get_id(); // This temp file will be post processed at the end of the storage process.
                $shrentry->url = '';
                $hasentry = true;
            }
        }
    } else {
        $hasentry = true;
    }

    if ($hasentry) {
        // Catch the case the identifier is already known for this object.
        // Save updated state in session.
        if (($mode == 'add') && $shrentry->exists()) {
            $srentry = serialize($shrentry);
            $SESSION->sr_entry = $srentry;

            // We are coming from the library. Go back to it.
            $params = array('course' => $course->id,
                            'mode' => 'add',
                            'add' => 1,
                            'return' => $return,
                            'section' => $section,
                            'context' => $sharingcontext,
                            'catid' => $catid,
                            'catpath' => $catpath);
            $fullurl = new moodle_url('/mod/sharedresource/metadatapreupdateconfirm.php', $params);
            redirect($fullurl);
        }

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

        $srentry = serialize($shrentry);
        $SESSION->sr_entry = $srentry;
        $error = 'no error';
        $SESSION->error = $error;

        $params = array('course' => $course->id,
                        'section' => $section,
                        'type' => $type,
                        'add' => 'sharedresource',
                        'return' => $return,
                        'mode' => $mode,
                        'context' => $sharingcontext,
                        'catid' => $catid,
                        'catpath' => $catpath);
        $fullurl = new moodle_url('/mod/sharedresource/forms/metadata_form.php', $params);
        redirect($fullurl);
    }
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

// Display form.
$mform->display();
echo $OUTPUT->footer($course);
// Page local functions.
// Grab and clean form value.

