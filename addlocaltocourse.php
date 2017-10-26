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
require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/mod/sharedresource/forms/admin_convert_form.php');
require_once($CFG->dirroot.'/course/lib.php');

$courseid = optional_param('id', '', PARAM_INT);
$section = optional_param('section', '', PARAM_INT);
$identifier = required_param('identifier', PARAM_TEXT);
$mode = optional_param('mode', 'shared', PARAM_ALPHA);
$course = $DB->get_record('course', array('id' => "$courseid"));

if (empty($course)) {
    print_error('coursemisconf');
}

// Security.

require_login($course);

$context = context_course::instance($course->id);
$strtitle = get_string('addlocal', 'sharedresource');

$params = array('id' => $courseid, 'identifier' => $identifier, 'mode' => $mode);
$url = new moodle_url('/mod/sharedresource/addlocaltocourse.php', $params);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($strtitle,'addlocaltocourse.php', 'misc');
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');

$shrentry = \mod_sharedresource\entry::read($identifier);

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

// The sharedresource has been recognized as a deployable backup.
// Take the physical file and deploy it with the activity publisher utility.
if ($mode == 'deploy') {
    require_capability('moodle/course:manageactivities', $context);

    if (file_exists($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php')) {
        include_once($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php');

        $fs = get_file_storage();

        $shrentry = $DB->get_record('sharedresource_entry', array('identifier' => required_param('identifier', PARAM_TEXT)));

        $file = $fs->get_file_by_id($shrentry->file);
        activity_publisher::restore_single_module($courseid, $file);

        // TODO : Terminate procedure and return to course silently.
        redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
        die;
    }

    // No one should be here....
}

if ($mode == 'ltiinstall') {

    // We build an LTI Tool instance.
    include_once($CFG->dirroot.'/mod/sharedresource/forms/lti_mod_form.php');
    include_once($CFG->dirroot.'/mod/lti/lib.php');

    $instance = new StdClass();
    $instance->name = $shrentry->title;
    $instance->intro = $shrentry->description;
    $instance->introformat = FORMAT_MOODLE;
    $time = time();
    $instance->timecreated = $time;
    $instance->timemodified = $time;
    $instance->typeid = 0;
    if (preg_match('#^https://#', $shrentry->url)) {
        $instance->toolurl = '';
        $instance->securetoolurl = $shrentry->url;
    } else {
        $instance->toolurl = $shrentry->url;
        $instance->securetoolurl = '';
    }
    $instance->instructorchoicesendname = 1; // Default lti form value.
    $instance->instructorchoicesendemailaddr = 1;
    $instance->instructorchoiceallowroster = 1;
    $instance->instructorchoiceallowsetting = 1;
    $instance->instructorcustomparameters = '';
    $instance->instructorchoiceacceptgrades = 1;
    $instance->grade = 0;
    $instance->launchcontainer = LTI_LAUNCH_CONTAINER_DEFAULT;
    $instance->resourcekey = ''; // Client identification key for remote service.
    $instance->password = ''; // Server password for accessing the service.
    $instance->debuglaunch = 0;
    $instance->showtitlelaunch = 0;
    $instance->showdescriptionlaunch = 0;
    $instance->servicesalt = ''; // Unique salt autocalculated.
    $instance->icon = '';
    $instance->secureicon = '';

    $mform = new lti_mod_form();
    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
    }
    if ($data = $mform->get_data()) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('add'.$mode, 'sharedresource'));

        $intancearr = (array)$instance;
        $data->intro = $data->introeditor['text'];
        $data->introformat = $data->introeditor['format'];

        // Report changes from form.
        foreach (array_keys($intancearr) as $key) {
            if (isset($data->$key)) {
                $instance->$key = $data->$key;
            }
        }
        $instance->course = $courseid;
        $instance->id = lti_add_instance($instance, null);
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('add'.$mode, 'sharedresource'));
        $instance->identifier = $identifier;
        $instance->mode = $mode;
        $instance->id = $courseid;
        $instance->section = $section;
        $mform->set_data($instance);
        $mform->display();
        echo $OUTPUT->footer();
        die;
    }

    $modulename = 'lti';
} else {
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

$sectionid = $DB->get_field('course_sections', 'id', array('course' => $courseid, 'section' => $section));

// Make a new course module.
$module = $DB->get_record('modules', array('name'=> $modulename));
$cm = new StdClass;
$cm->instance = $instance->id;
$cm->module = $module->id;
$cm->course = $courseid;
$cm->section = $sectionid;

// Remoteid may be obtained by $shrentry->add_instance() plugin hooking !!
// Valid also if LTI tool.
if (!empty($shrentry->remoteid)) {
    $cm->idnumber = $shrentry->remoteid;
}

// Insert the course module in course.
if (!$cm->id = add_course_module($cm)) {
    print_error('errorcmaddition', 'sharedresource');
}

// Reset the course modinfo cache.
$course->modinfo = null;
$DB->update_record('course', $course);

if (!$section) {
    // When we add directly from library without course action.
    $section = sharedresource_get_course_section_to_add($COURSE);
}

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
    $coursepage = course_page::get_current_page($course->id);
    $coursepage->add_cm_to_page($cm->id);
}

// Finally if localization was asked, transform the sharedresource in real resource.
if ($mode == 'local') {
    // We make a standard resource from the sharedresource.
    $instance->id = sharedresource_convertfrom($instance);
    $modulename = 'resource';
} else {
    $modulename = 'sharedresource';
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

// TODO : Terminate procedure and return to course silently.
redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
die;
