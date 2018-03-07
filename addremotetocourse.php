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
 * @package    mod_sharedresource
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
$context = context_course::instance($course->id);
require_any_capability(array('repository/sharedresources:use', 'repository/sharedresources:create'), $context;

// If we have a physical file to get, get it.
if ($mode == 'file' || ($mode == 'local' && !empty($filename))) {
    $url = required_param('url', PARAM_URL);
    $filename = required_param('file', PARAM_TEXT);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // Set it to pretty big files.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // Set it to retrieve any content type.
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Important.
    curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    if ($rawresponse = curl_exec($ch)) {
        $filename = preg_replace('/[0-9a-f]+-/i', '', basename($filename));  // Removes the unique shacode.
        $path = $CFG->dataroot.'/'.$course->id.'/'.$filename;
        $FILE = fopen($path, 'wb');
        fwrite($FILE, $rawresponse);
        fclose($FILE);
    }
    // If we are just getting the file, that's enough.
    if ($mode == 'file') {
        redirect($CFG->wwwroot.'/files/index.php?id='.$course->id);
    }
}
if ($mode != 'file') {
    /*
     * The resource IS NOT known in the local repository but we may have the identifier and the provider
     * if identifier is empty the resource is submitted from an external search interface.
     * if not empty, the resource comes from another MNET shared repository
     */
    $title = required_param('title', PARAM_TEXT);
    $desc = required_param('description', PARAM_TEXT);
    $provider = required_param('provider', PARAM_TEXT);
    $keywords = required_param('keywords', PARAM_TEXT);

    // Make a sharedresource_entry.
    $entryclass = \mod_sharedresource\entry_factory::get_entry_class();
    $shrentry = new $entryclass(false);
    $shrentry->title = $title;
    $shrentry->description = $desc;
    $shrentry->keywords = $keywords;
    $shrentry->url = $url;
    $shrentry->sharedresourcefile = '';
    if (!empty($identifier)) {
        $shrentry->identifier = $identifier;
    } else {
        $shrentry->identifier = sha1($url);
    }
    $shrentry->provider = $provider;
    if (!$DB->record_exists('sharedresource_entry', array('identifier' => $shrentry->identifier))) {
        $shrentry->add_instance();
    } else {
        if (!$shrentry = \mod_sharedresource\entry::read($identifier)) {
            print_error('errorinvalididentifier', 'sharedresource');
        }
    }

    // Add a sharedresource.
    $sharedresource = new \mod_sharedresource\base(0, $shrentry->identifier);
    $sharedresource->options = 0;
    $sharedresource->popup = 0;
    $sharedresource->type = 'file';
    $sharedresource->identifier = $shrentry->identifier;
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
            $resource = $DB->get_record('resource',array( 'id' => $resourceid));
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
    $module = $DB->get_record('modules', array('name' => $modulename));
    $cm->instance = $resourceid;
    $cm->module = $module->id;
    $cm->course = $courseid;
    $cm->section = 1;
    // Remoteid may be obtained by $shrentry->add_instance() plugin hooking !!
    if (!empty($shrentry->remoteid)) {
        $cm->idnumber = $shrentry->remoteid;
    }
    // Insert the course module in course.
    if (!$cm->coursemodule = add_course_module($cm)) {
        print_error('errorcmaddition', 'sharedresource');
    }
    if (!$sectionid = add_mod_to_section($cm)) {
        print_error('errorsectionaddition', 'sharedresource');
    }
    if (!$DB->set_field('course_modules', 'section', $sectionid, array('id' => $cm->coursemodule))) {
        print_error('errorcmsectionbinding', 'sharedresource');
    }
}

// Finish.
$params = array('id' => $courseid, 'url' => $url, 'file' => $filename);
$url = new moodle_url('/mod/sharedresource/addremotetocourse.php', $params);
$PAGE->set_url($url);
$PAGE->set_title('');
$PAGE->set_heading('');
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);
$PAGE->set_button('');
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php', array('id' => $course->id)));
$PAGE->navbar->add(get_string('addremote', 'sharedresource'));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('addremote', 'sharedresource'));

echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $courseid)));
echo $OUTPUT->footer($course);
die;
