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
 * this admin screen allows converting massively resources into sharedresources indexable entries.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/*
 * sharedresource_check_access does the job.
 * phpcs:disable moodle.Files.RequireLogin.Missing
 */

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/mod/sharedresource/forms/admin_convert_form.php');

$courseid = optional_param('course', SITEID, PARAM_INT);
$url = new moodle_url('/mod/sharedresource/admin_convertall.php', ['course' => $courseid]);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = sharedresource_check_access($course);

$PAGE->set_context($context);
$PAGE->set_title(get_string('resourceconversion', 'sharedresource'));
$PAGE->set_heading(get_string('resourceconversion', 'sharedresource'));
$PAGE->set_url('/mod/sharedresource/admin_convertall.php', ['course' => $courseid]);
$PAGE->set_pagelayout('standard');

// Navigation.
$PAGE->navbar->add(get_string('resourceconversion', 'sharedresource'));
$PAGE->navbar->add(get_string('resourcetorepository', 'sharedresource'));

// Get courses.

if (empty($courseid)) {
    // If no course choosen (comming from general sections) make the choice of one.
    $allcourses = $DB->get_records_menu('course', null, 'shortname', 'id, fullname');
    $form = new sharedresource_choosecourse_form($allcourses);

    if ($form->is_cancelled()) {
        redirect(new moodle_url('/local/sharedresources/index.php'));
    }

    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
    die;
} else {

    $course = $DB->get_record('course', ['id' => "$courseid"], '*', MUST_EXIST);

    // Back to library if cancelled.
    $form = new sharedresource_choosecourse_form(null);
    if ($form->is_cancelled()) {
        redirect(new moodle_url('/local/sharedresources/index.php'));
    }

    $resources = $DB->get_records('resource', ['course' => $courseid], 'name');

    $urls = $DB->get_records('url', ['course' => $courseid], 'name');

    $buttonurl = new moodle_url('/course/view.php', ['id' => $courseid]);
    if (empty($resources) && empty($urls)) {
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('noresourcestoconvert', 'sharedresource'));
        echo $OUTPUT->continue_button($buttonurl);
        echo $OUTPUT->footer();
        exit();
    }

    $form2 = new sharedresource_selectresources_form($url, ['resources' => $resources, 'urls' => $urls]);

    if ($form2->is_cancelled()) {
        if ($courseid) {
            redirect($buttonurl);
        } else {
            redirect(new moodle_url('/resources/index.php'));
        }
    }

    // If data submitted, proceed.
    if ($data = $form2->get_data()) {
        $reskeys = preg_grep("/rcnv_/" , array_keys(get_object_vars($data)));
        $report = '';
        if (!empty($reskeys)) {
            foreach ($reskeys as $reskey) {
                // Convert selected resources.
                if ($data->$reskey == 1) {
                    $resid = str_replace('rcnv_', '', $reskey);
                    $resource = $DB->get_record('resource', ['id' => $resid], '*', MUST_EXIST);
                    if (debugging()) {
                        $report .= "converting resource {$resource->id} : {$resource->name}\n";
                    }
                    $report .= sharedresource_convertto($resource, 'resource');
                }
            }
        }
        $reskeys = preg_grep("/ucnv_/" , array_keys(get_object_vars($data)));
        if (!empty($reskeys)) {
            foreach ($reskeys as $reskey) {
                // Convert selected resources.
                if ($data->$reskey == 1) {
                    $resid = str_replace('ucnv_', '', $reskey);
                    $url = $DB->get_record('url', ['id' => $resid]);
                    if (debugging()) {
                        $report .= "converting url $url->id : $url->name \n";
                    }
                    $report .= sharedresource_convertto($url, 'url');
                }
            }
        }
        rebuild_course_cache($courseid);
    } else {
        // Print form.
        echo $OUTPUT->header();
        $form2->set_data(['course' => $course->id]);
        $form2->display();
        echo $OUTPUT->footer();
        die;
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('resourceconversion', 'sharedresource'), 1);

if (!empty($report)) {
    echo '<pre>';
    echo $report;
    echo '</pre>';
}

$courseurl = new moodle_url('/course/view.php', ['id' => $courseid]);
echo $OUTPUT->continue_button($courseurl);
echo $OUTPUT->footer();
