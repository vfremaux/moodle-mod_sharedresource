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
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/taoresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_base.class.php');

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID.
$identifier = optional_param('identifier', 0, PARAM_BASE64);    // SHA1 resource identifier.
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

$cmid = 0;

$systemcontext = context_system::instance();
$strtitle = get_string('sharedresourcedetails', 'sharedresource');

$url = new moodle_url('/mod/sharedresource/view.php', array('id' => $id, 'identifier' => $identifier));
$PAGE->set_url($url);

// echo $OUTPUT->header(); // will be done by sharedresource::display();

if ($identifier) {
    $resource = $DB->get_record('sharedresource_entry', array('identifier' => $identifier));

    $resourceentry = new sharedresource_entry($resource);
    $resourceentry = $resourceentry->fetch_ahead();

    if (!$resource) {
        sharedresource_not_found(SITEID, 'Code 00');
    }

    $params = array('contenthash' => $resource->identifier,
                    'component' => 'mod_sharedresource',
                    'filearea' => 'sharedresource',
                    'itemid' => $resource->id);
    if ($resource->file != '' && !$file = $DB->get_record('files', $params)) {
        sharedresource_not_found($cm->course, 'code 00-04');
    }

    $cmid = 0;
    $course = new StdClass();
    $course = $DB->get_record('course', array('id' => SITEID));
} else {
    if ($id) {
        if (!$cm = get_coursemodule_from_id('sharedresource', $id)) {
            sharedresource_not_found(SITEID, 'Code 01');
        }

        if (!$sharedresource =  $DB->get_record('sharedresource', array('id'=> $cm->instance))) {
            sharedresource_not_found($cm->course, 'Code 02');
        }

        if (!$resource = $DB->get_record('sharedresource_entry', array('identifier' => $sharedresource->identifier))) {
            sharedresource_not_found($cm->course, 'Code 03');
        }

        $params = array('contenthash' => $sharedresource->identifier,
                        'component' => 'mod_sharedresource',
                        'filearea' => 'sharedresource',
                        'itemid' => $resource->id);
        if ($resource->file != '' && !$file = $DB->get_record('files', $params)) {
            sharedresource_not_found($cm->course, 'code 04');
        }
    } else {
        sharedresource_not_found(SITEID, 'code 05');
    }

    if (!$course =  $DB->get_record('course', array('id' => $cm->course))) {
        print_error('badcourseid', 'sharedresource');
    }

    $coursecontext = context_course::instance($course->id);
    $PAGE->set_context($coursecontext);
    $PAGE->set_title($strtitle);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_heading($SITE->fullname);
    $PAGE->navbar->add($strtitle, 'view.php', 'misc');
    $PAGE->set_cacheable(false);

    require_course_login($course, true, $cm);
    $cmid = $cm->id;
}

if ($cmid) {
    $modulecontext = context_module::instance($cmid);
    $params = array(
        'context' => $modulecontext,
        'objectid' => $resource->id
    );

    $event = \mod_sharedresource\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('sharedresource', $sharedresource);
    $event->add_record_snapshot('sharedresource_entry', $resource);
    $event->trigger();
}

$resourceinstance = new \mod_sharedresource\base($cmid, $identifier);

if ($inpopup) {
    $resourceinstance->inpopup();
}

$resourceinstance->display();

echo $OUTPUT->footer();
