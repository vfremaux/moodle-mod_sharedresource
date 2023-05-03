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
 * this action screen allows adding a sharedresource from an external browse or search result
 * directly in the current course and the resource results being already known as a local proxy, or
 * it is a locally stored resource.
 * This possibility will only be available when
 * external resource repositories are queried from a course starting context.
 * Adding local resource should always provide identifier.
 *
 * @package    mod_sharedresource
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
use \mod_sharedresource\entry_factory;
use \mod_sharedresource\entry;
use \mod_sharedresource\entry_extended;

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/mod/sharedresource/forms/admin_convert_form.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/mod/scorm/lib.php');

$courseid = optional_param('id', '', PARAM_INT);
$section = optional_param('section', '', PARAM_INT);
$identifier = required_param('identifier', PARAM_TEXT);
$mode = optional_param('mode', 'shared', PARAM_ALPHA);
$course = $DB->get_record('course', array('id' => "$courseid"));

if (empty($course)) {
    print_error('coursemisconf');
}

if (!$section) {
    // When we add directly from library without course action.
    $section = sharedresource_get_course_section_to_add($course);
}

// Security.

require_login($course);
$context = context_course::instance($course->id);
if (!has_any_capability(array('repository/sharedresources:use', 'repository/sharedresources:create'), $context)) {
    print_error('noaccessform', 'sharedresource');
}

$config = get_config('sharedresource');

$strtitle = get_string('addlocal', 'sharedresource');

$params = array('id' => $courseid, 'identifier' => $identifier, 'mode' => $mode);
$url = new moodle_url('/mod/sharedresource/addlocaltocourse.php', $params);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($strtitle, 'addlocaltocourse.php', 'misc');
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');

$class = \mod_sharedresource\entry_factory::get_entry_class();
if ($class == '\mod_sharedresource\entry_extended') {
    $shrentry = entry_extended::read($identifier);
} else {
    $shrentry = entry::read($identifier);
}

if ($mode == 'file') {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('add'.$mode, 'sharedresource'));
    // This is the simple "file" mode that gets back the resource file into course file scope.
    print_string('fileadvice', 'sharedresource');
    $return = new moodle_url('/files/index.php', array('id' => $courseid));
    echo $OUTPUT->continue_button($return);
    echo $OUTPUT->footer($course);
    die;
}

/*
 * The sharedresource has been recognized as a deployable backup.
 * Take the physical file and deploy it with the activity publisher utility.
 */
if ($mode == 'deploy') {
    require_capability('moodle/course:manageactivities', $context);

    if (file_exists($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php')) {
        sharedresource_deploy_activity($shrentry, $course, $section);
        // TODO : Terminate procedure and return to course silently.
        redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
    }

    // No one should be here....
}

// Scorm package case.

if ($mode == 'scorm') {
    list($cm, $instance, $modname) = sharedresource_deploy_scorm($shrentry, $course, $section);
    // TODO : Terminate procedure and return to course silently.
    redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
}

/*
 * The sharedresource has been recognized as being a LTI descriptor
 */
if (($mode == 'ltiinstall') || ($mode == 'lticonfirm')) {
    $instance = sharedresource_deploy_lti($shrentry, $courseid, $section);

    $modulename = 'lti';
} else {
    /*
     * Sharedresource has been recognized as a scorm package, we deploy it as a scorm
     * activity. Scorm type will depend on global configuration of
     * the content integration section of sharedresource.
     */
    // Elsewhere add a sharedresource instance.
    // Make a shared resource on the sharedresource_entry.
    $instance = new \mod_sharedresource\base(0, $shrentry->identifier);
    $instance->options = 0;
    $instance->popup = 0;
    $instance->type = 'file';
    $instance->identifier = $shrentry->identifier;
    $instance->name = $shrentry->title;
    $instance->course = $courseid;
    $instance->intro = $shrentry->description;
    $instance->introformat = 0;
    $instance->alltext = '';
    $instance->timemodified = time();

    if (!$instance->id = $instance->add_instance($instance)) {
        print_error('erroraddinstance', 'sharedresource');
    }

    $modulename = 'sharedresource';
}

if (empty($cm)) {
    $cm  = sharedresource_build_cm($courseid, $section, $modulename, $shrentry, $instance);
}

// Reset the course modinfo cache.
$course->modinfo = null;
$DB->update_record('course', $course);

if (!$sectionid = course_add_cm_to_section($course, $cm->id, $section)) {
    print_error('errorsectionaddition', 'sharedresource');
}

if (!$DB->set_field('course_modules', 'section', $sectionid, array('id' => $cm->id))) {
    print_error('errorcmsectionbinding', 'sharedresource');
}

// If we are in page format, add page_item to section bound page.
if ($course->format == 'page') {
    require_once($CFG->dirroot.'/course/format/page/classes/page.class.php');
    require_once($CFG->dirroot.'/course/format/page/lib.php');
    $coursepage = \format\page\course_page::get_current_page($course->id);
    $coursepage->add_cm_to_page($cm->id);
}

$report = '';
// Finally if localization was asked, transform the sharedresource in real resource.
if (empty($modulename)) {
    if ($mode == 'local') {
        // We make a standard resource from the sharedresource.
        $instance->id = sharedresource_convertfrom($instance, $report);
        $modulename = 'resource';
    } else {
        $modulename = 'sharedresource';
    }
}

// Fire event.
$modcontext = context_module::instance($cm->id);
$eventdata = new StdClass();
$eventdata->modulename = $modulename;
$eventdata->courseid = $courseid;
$eventdata->sectionid = $sectionid;
$eventdata->modname = $eventdata->modulename;
$eventdata->id = $eventdata->coursemodule = $cm->id;
$eventdata->instance = $instance->id;
$eventdata->name = $instance->name;
$event = \core\event\course_module_created::create_from_cm($eventdata, $modcontext);
$event->trigger();

// Finish.

if ($CFG->debug == DEBUG_DEVELOPER) {
    echo $OUTPUT->header();
    echo '<pre>';
    echo $report;
    echo '</pre>';

    echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $course->id)));
    echo $OUTPUT->footer();
    die;
} else {
    // TODO : Terminate procedure and return to course silently.
    redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
    die;
}