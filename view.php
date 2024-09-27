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
 * main view of a sharedresource
 *
 * @package     mod_sharedresource
 * @author      Piers Harding  <piers@catalyst.net.nz>
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_entry.class.php');

use mod_sharedresource\entry;
use mod_sharedresource\base;

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID.
$identifier = optional_param('identifier', 0, PARAM_BASE64);    // SHA1 resource identifier.
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

$cmid = 0;

$systemcontext = context_system::instance();
$strtitle = get_string('sharedresourcedetails', 'sharedresource');

$url = new moodle_url('/mod/sharedresource/view.php', ['id' => $id, 'identifier' => $identifier]);
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);

if ($identifier) {
    $resource = $DB->get_record('sharedresource_entry', ['identifier' => $identifier], '*', MUST_EXIST);

    $resourceentry = new entry($resource);

    $originresource = $resourceentry;
    $resourceentry = $resourceentry->fetch_ahead();
    if ($CFG->debug == DEBUG_DEVELOPER) {
        if ($originresource->identifier != $resourceentry->identifier) {
            echo "Raising effective version up to $resourceentry->identifier ";
        }
    }

    $params = [
        'contenthash' => $resource->identifier,
        'component' => 'mod_sharedresource',
        'filearea' => 'sharedresource',
        'itemid' => $resource->id,
    ];
    if ($resource->file != '' && !$file = $DB->get_record('files', $params)) {
        sharedresource_not_found($cm->course, 'code 00-04');
    }

    $cmid = 0;
    $course = new StdClass();
    $course = $DB->get_record('course', ['id' => SITEID], '*', MUST_EXIST);
} else {
    if ($id) {
        if (!$cm = get_coursemodule_from_id('sharedresource', $id)) {
            sharedresource_not_found(SITEID, 'Code 01');
        }

        $sharedresource = $DB->get_record('sharedresource', ['id' => $cm->instance], '*', MUST_EXIST);
        $DB->get_record('sharedresource_entry', ['identifier' => $sharedresource->identifier], '*', MUST_EXIST);

        $params = [
            'contenthash' => $sharedresource->identifier,
            'component' => 'mod_sharedresource',
            'filearea' => 'sharedresource',
            'itemid' => $resource->id,
        ];
        if ($resource->file != '' && (!$file = $DB->get_record('files', $params))) {
            sharedresource_not_found($cm->course, 'code 04');
        }
    } else {
        sharedresource_not_found(SITEID, 'code 05');
    }

    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

    $coursecontext = context_course::instance($course->id);
    $PAGE->set_context($coursecontext);
    $PAGE->set_title($strtitle);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_heading($SITE->fullname);
    $PAGE->set_cacheable(false);

    require_course_login($course, true, $cm);
    $cmid = $cm->id;
}

if ($cmid) {
    $modulecontext = context_module::instance($cmid);
    $params = [
        'context' => $modulecontext,
        'objectid' => $resource->id,
    ];

    $event = \mod_sharedresource\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('sharedresource', $sharedresource);
    $event->add_record_snapshot('sharedresource_entry', $resource);
    $event->trigger();
}

$resourceinstance = new base($cmid, $identifier);

if ($inpopup) {
    $resourceinstance->inpopup();
}

// Handles the OUTPUT->header call.
$resourceinstance->display();

echo $OUTPUT->footer();
