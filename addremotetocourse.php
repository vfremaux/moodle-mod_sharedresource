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
 * Add a remote search result in course as sharedresource.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/*
 * this action screen allows adding a sharedresource from an external search result
 * directly in the current course. This possibility will only be available when
 * external resource repositories are queried from a course starting context
 */
require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/mod/sharedresource/forms/admin_convert_form.php');
require_once($CFG->dirroot.'/mod/scorm/lib.php');

$courseid = optional_param('id', '', PARAM_INT);
$section = optional_param('section', '', PARAM_INT);
$identifier = optional_param('identifier', '', PARAM_TEXT); // The remote identifier.
$filename = optional_param('filename', '', PARAM_TEXT); // The local filename of the remote file.
$filepath = optional_param('filepath', '', PARAM_TEXT); // The local filepath of the remote file.
$mode = optional_param('mode', 'shared', PARAM_ALPHA); // The deployment operation mode.
$token = optional_param('token', '', PARAM_TEXT);

$url = urldecode(required_param('url', PARAM_TEXT));
if (!empty($token)) {
    $url .= '&token='.$token;
}

$course = $DB->get_record('course', ['id' => "$courseid"]);
if (empty($course)) {
    throw new moodle_exception('coursemisconf');
}

// Some attributes of $PAGE must be defined.
$params = ['id' => $courseid, 'url' => $url, 'file' => $filename];
$pageurl = new moodle_url('/mod/sharedresource/addremotetocourse.php', $params);
$PAGE->set_url($pageurl);

// Security.

require_login($course);

$context = context_course::instance($course->id);
if (!has_any_capability(['repository/sharedresources:use', 'repository/sharedresources:create'], $context)) {
    throw new moodle_exception('noaccessform', 'sharedresource');
}

if (!$section) {
    // When we add directly from library without course action.
    $section = sharedresource_get_course_section_to_add($course);
}

$config = get_config('sharedresource');

// Check wether the deployment mode needs the remote file to be localized here.
$needsfileinlocalfs = ($mode == 'deploy') ||
            ($mode == 'file') ||
                    (($mode == 'scorm') && $config->scormintegration == SCORM_TYPE_LOCAL);

$tempfile = null;
if ($needsfileinlocalfs) {
    // Remote file will be restored in a local draft filearea at root filepath.
    $tempfile = sharedresource_get_remote_file($url, $filename);
}

// Make a sharedresource representation of the imported resource.
$title = required_param('title', PARAM_TEXT);
$desc = required_param('description', PARAM_TEXT);
$provider = required_param('provider', PARAM_TEXT);
$keywords = optional_param('keywords', '', PARAM_TEXT);

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

/*
 * The sharedresource has been recognized as being a LTI descriptor
 */
if ($mode == 'ltiinstall' || $mode == 'lticonfirm') {
    $instance = sharedresource_deploy_lti($shrentry, $courseid, $section, $url);
    $modulename = 'lti';
}

if ($mode == 'mplayerdeploy') {
    // Deployes asking for remote setting.
    $instance = sharedresource_add_mplayer($shrentry, $courseid, true);
    $modulename = 'mplayer';
}

/*
 * The sharedresource has been recognized as a deployable backup by the remote library.
 * Take the physical file and deploy it with the activity publisher utility.
 */
if ($mode == 'deploy') {
    require_capability('moodle/course:manageactivities', $context);

    if (file_exists($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php')) {
        sharedresource_deploy_activity($shrentry, $course, $section, $tempfile);
        // TODO : Terminate procedure and return to course silently.
        redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
    } else {
        throw new moodle_exception('Activity publisher not installed. Deployment cannot be performed.');
    }

    // No one should be here....
}

if ($mode == 'scorm') {
    require_capability('moodle/course:manageactivities', $context);

    // Check and fix scormintegration settings regarding scorm config. Defaults to local.
    $configkey = 'allow'.$config->scormintegration;
    if (empty($config->$configkey)) {
        set_config('scormintegration', SCORM_TYPE_LOCAL, 'sharedresource');
        $config->scormintegration = SCORM_TYPE_LOCAL;
    }

    $draftid = false;
    if (!empty($tempfile)) {
        // The remote scorm package has been relocalized as a local tempfile.
        $draftid = $tempfile->get_itemid();
    }
    if (empty($draftid) && ($config->scormintegration == SCORM_TYPE_LOCAL)) {
        throw new moodle_exception('errorscormtypelocalwithnofile', 'sharedresource');
    }

    list($cm, $instance, $modname) = sharedresource_deploy_scorm($shrentry, $course, $section, $draftid);

    if (empty($cm)) {
        throw new moodle_exception('errorscorm', 'sharedresource');
    }

    // TODO : Terminate procedure and return to course silently.
    redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
}

if (!in_array($mode, ['file', 'lticonfirm', 'ltiinstall', 'mplayerdeploy'])) {
    /*
     * The resource IS NOT known in the local repository but we may have the identifier and the provider
     * if identifier is empty the resource is submitted from an external search interface.
     * if not empty, the resource comes from another MNET shared repository
     */
    if (!$DB->record_exists('sharedresource_entry', ['identifier' => $shrentry->identifier])) {
        $shrentry->add_instance();
    } else {
        if (!$shrentry = \mod_sharedresource\entry::read($identifier)) {
            throw new moodle_exception('errorinvalididentifier', 'sharedresource');
        }
    }

    // Add a sharedresource instance.
    $instance = new \mod_sharedresource\base(0, $shrentry->identifier);
    $instance->options = 0;
    $instance->popup = 0;
    $instance->type = 'file';
    $instance->identifier = $shrentry->identifier;
    $instance->name = $title;
    $instance->course = $courseid;
    $instance->description = $desc;
    $instance->alltext = '';
    $instance->timemodified = time();

    if ($mode == 'local') {
        // We make a standard resource from the sharedresource.
        $resourceid = sharedresource_convertfrom($instance, false);

        // If we have a physical file we have to bind it to the resource.
        if (!empty($filename)) {
            $resource = $DB->get_record('resource', ['id' => $resourceid]);
            $resource->reference = basename($filename);
            $DB->update_record('resource', $resource);
        }

        $modulename = 'resource';
    } else {
        if (!$instance->id = $instance->add_instance($instance)) {
            throw new moodle_exception('erroraddinstance', 'sharedresource');
        }
        $modulename = 'sharedresource';
    }
}

$cm  = sharedresource_build_cm($courseid, $section, $modulename, $shrentry, $instance);

// Remoteid may be obtained by $shrentry->add_instance() plugin hooking !!
if (!empty($shrentry->remoteid)) {
    $cm->idnumber = $shrentry->remoteid;
}

if (!$sectionid = course_add_cm_to_section($courseid, $cm->id, $section)) {
    throw new moodle_exception('errorsectionaddition', 'sharedresource');
}

if (!$DB->set_field('course_modules', 'section', $sectionid, ['id' => $cm->id])) {
    throw new moodle_exception('errorcmsectionbinding', 'sharedresource');
}

// If we are in page format, add page_item to section bound page.
if ($course->format == 'page') {
    require_once($CFG->dirroot.'/course/format/page/classes/page.class.php');
    require_once($CFG->dirroot.'/course/format/page/lib.php');
    $coursepage = \format_page\course_page::get_current_page($course->id);
    $coursepage->add_cm_to_page($cm->id);
}

// Finish.
$PAGE->set_title('');
$PAGE->set_heading('');
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);
$PAGE->set_button('');
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php', ['id' => $course->id]));
$PAGE->navbar->add(get_string('addremote', 'sharedresource'));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('addremote', 'sharedresource').' : '.get_string('pluginname', $modulename));

echo $OUTPUT->continue_button(new moodle_url('/course/view.php', ['id' => $courseid]));
echo $OUTPUT->footer($course);
die;
