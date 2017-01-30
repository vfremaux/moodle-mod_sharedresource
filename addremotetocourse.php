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
 * this action screen allows adding a sharedresource from an external search result
 * directly in the current course. This possibility will only be available when
 * external resource repositories are queried from a course starting context
 *
 * @package    sharedresource
 * @subpackage mod_sharedresource
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/mod/sharedresource/admin_convert_form.php');

$courseid = optional_param('id', '', PARAM_INT);
$identifier = optional_param('identifier', '', PARAM_TEXT);
$mode = optional_param('mode', 'shared', PARAM_ALPHA);

$course =  $DB->get_record('course', array('id' => "$courseid"));
if (empty($course)) {
    print_error('coursemisconf');
}

// Security.

require_login($course);

// If we have a physical file to get, get it.
if ($mode == 'file' || ($mode == 'local' && !empty($filename))) {
    $url = required_param('url', PARAM_URL);
    $filename = required_param('file', PARAM_TEXT);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // set it to pretty big files
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // set it to retrieve any content type
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // important
    curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    if ($rawresponse = curl_exec($ch)) {
        $filename = preg_replace('/[0-9a-f]+-/i', '', basename($filename));  // removes the unique shacode
        $path = $CFG->dataroot.'/'.$course->id.'/'.$filename;
        $FILE = fopen($path, 'wb');
        fwrite($FILE, $rawresponse);
        fclose($FILE);
    }
    // if we are just getting the file, that's enough
    if ($mode == 'file') {
        redirect($CFG->wwwroot.'/files/index.php?id='.$course->id);
    }
}
if ($mode != 'file') {
    // The resource IS NOT known in the local repository but we may have the identifier and the provider
    // if identifier is empty the resource is submitted from an external search interface.
    // if not empty, the resource comes from another MNET shared repository
    $title = required_param('title', PARAM_TEXT);
    $desc = required_param('description', PARAM_TEXT);
    $provider = required_param('provider', PARAM_TEXT);
    $keywords = required_param('keywords', PARAM_TEXT);
    // make a sharedresource_entry
    $sharedresource_entry = new sharedresource_entry(false);
    $sharedresource_entry->title = $title;
    $sharedresource_entry->description = $desc;
    $sharedresource_entry->keywords = $keywords;
    $sharedresource_entry->url = $url;
    $sharedresource_entry->sharedresourcefile = '';
    if (!empty($identifier)) {
        $sharedresource_entry->identifier = $identifier;
    } else {
        $sharedresource_entry->identifier = sha1($url);
    }
    $sharedresource_entry->provider = $provider;
    if (!$DB->record_exists('sharedresource_entry', array('identifier' => $sharedresource_entry->identifier))) {
        $sharedresource_entry->add_instance();
    } else {
        if (!$sharedresource_entry = sharedresource_entry::read($identifier)) {
            print_error('errorinvalididentifier', 'sharedresource');
        }
    }
    // Add a sharedresource
    $sharedresource = new sharedresource_base(0, $sharedresource_entry->identifier);
    $sharedresource->options = 0;
    $sharedresource->popup = 0;
    $sharedresource->type = 'file';
    $sharedresource->identifier = $sharedresource_entry->identifier;
    $sharedresource->name = $title;
    $sharedresource->course = $courseid;
    $sharedresource->description = $desc;
    $sharedresource->alltext = '';
    $sharedresource->timemodified = time();
    if ($mode == 'local') {
        // We make a standard resource from the sharedresource.
        $resourceid = sharedresource_convertfrom($sharedresource, false);
        $modulename = 'resource';
        // If we have a physical file we have to bind it to the resource.
        if (!empty($filename)) {
            $resource =  $DB->get_record('resource',array( 'id'=> $resourceid));
            $resource->reference = basename($filename);
            $DB->update_record('resource', $resource);
        }
    } else {
        if (!$resourceid = $sharedresource->add_instance($sharedresource)) {
            print_error('erroraddinstance', 'sharedresource');
        }
        $modulename = 'sharedresource';
    }
    // Make a new course module.
    $module =  $DB->get_record('modules', array('name'=> $modulename));
    $cm->instance = $resourceid;
    $cm->module = $module->id;
    $cm->course = $courseid;
    $cm->section = 1;
    // Remoteid may be obtained by $sharedresource_entry->add_instance() plugin hooking !!;
    if (!empty($sharedresource_entry->remoteid)) {
        $cm->idnumber = $sharedresource_entry->remoteid;
    }
    // insert the course module in course
    if (!$cm->coursemodule = add_course_module($cm)) {
        print_error('errorcmaddition', 'sharedresource');
    }
    if (!$sectionid = add_mod_to_section($cm)) {
        print_error('errorsectionaddition', 'sharedresource');
    }
    if (! $DB->set_field('course_modules', 'section', $sectionid, array('id' => $cm->coursemodule))) {
        print_error('errorcmsectionbinding', 'sharedresource');
    }
}

// Finish.

$PAGE->set_url($CFG->wwwroot.'/mod/sharedresource/addremotetocourse.php?id='.$courseid.'&url='.$url.'&file='.$filename);
$PAGE->set_title('');
$PAGE->set_heading('');
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);
$PAGE->set_button('');
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($course->shortname, $CFG->wwwroot."/course/view.php?id={$course->id}");
$PAGE->navbar->add(get_string('addremote', 'sharedresource'));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('addremote', 'sharedresource'));

echo $OUTPUT->continue_button($CFG->wwwroot."/course/view.php?id={$courseid}");
echo $OUTPUT->footer($course);
die;
