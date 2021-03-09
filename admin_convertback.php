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
 * this admin screen allows converting massively sharedresources into local resources or urls.
 *
 * @package    mod_sharedresource
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/mod/sharedresource/forms/admin_convert_form.php');

$courseid = optional_param('course', SITEID, PARAM_INT);

if ($courseid > SITEID) {
    if (!$course = $DB->get_record('course', array('id' => "$courseid"))) {
        print_error('coursemisconf');
    }

    // Security.

    $context = context_course::instance($courseid);
    require_login($course);
    require_capability('moodle/course:manageactivities', $context);
    $PAGE->set_context($context);
} else {
    $systemcontext = context_system::instance();
    require_login();
    require_capability('repository/sharedresources:manage', $systemcontext);
    $PAGE->set_context($systemcontext);
}

$PAGE->set_title(get_string('resourceconversion', 'sharedresource'));
$PAGE->set_heading(get_string('resourceconversion', 'sharedresource'));
$url = new moodle_url('/mod/sharedresource/admin_convertback.php', array('course' => $courseid));
$PAGE->set_url($url);
$PAGE->navbar->add(get_string('resourceconversion', 'sharedresource'));
$PAGE->navbar->add(get_string('repositorytoresource', 'sharedresource'));

$report = '';

// Get courses.
if (empty($courseid)) {
    $alllps = $DB->get_records_menu('course', array('format' => 'learning'), 'shortname', 'id,id');
    $form = new sharedresource_choosecourse_form($alllps);

    echo $OUTPUT->header();
    print ($OUTPUT->heading(get_string('resourceconversion', 'sharedresource'), 1));
    $form->display();
    echo $OUTPUT->footer();
    exit();
} else {
    $sharedresources = $DB->get_records('sharedresource', array('course' => $courseid), 'name');
    if (empty($sharedresources)) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('resourceconversion', 'sharedresource'), 1);
        echo $OUTPUT->notification(get_string('noresourcestoconvert', 'sharedresource'));
        echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $courseid, 'action' => 'activities')));
        echo $OUTPUT->footer();
        exit();
    }
    // Filter convertible resources :
    // We only can convert back non effectively shared resources.
    foreach ($sharedresources as $id => $sharedresource) {
        if ($DB->count_records_select('sharedresource', " course <> {$courseid} AND identifier = '{$sharedresource->identifier}' ") != 0) {
            unset($sharedresources[$id]);
        }
    }
    $form2 = new sharedresource_selectresources_form($url,  array('resources' => $sharedresources));

    if ($form2->is_cancelled()) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('resourceconversion', 'sharedresource'));
        echo $OUTPUT->notification(get_string('conversioncancelled', 'sharedresource'));
        $buttonurl = new moodle_url('/course/view.php', array('id' => $courseid));
        echo $OUTPUT->continue_button($buttonurl);
        echo $OUTPUT->footer();
        exit;
    }

    // If data submitted, proceed.
    if ($data = $form2->get_data()) {
        $reskeys = preg_grep("/cnv_/" , array_keys(get_object_vars($data)));
        if (!empty($reskeys)) {
            foreach ($reskeys as $reskey) {
                // Convert selected resources.
                if ($data->$reskey == 1) {
                    $resid = str_replace('rcnv_', '', $reskey);
                    $sharedresource = $DB->get_record('sharedresource', array('id' => $resid));
                    $report .= get_string('convertingsharedresource', 'sharedresource', $sharedresource);
                    sharedresource_convertfrom($sharedresource, $report);
                }
            }
        }
        rebuild_course_cache($courseid);
    } else {
        // Print form.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('resourceconversion', 'sharedresource'));
        $formdata = new StdClass;
        $formdata->course = $courseid;
        $form2->set_data($formdata);
        $form2->display();
        echo $OUTPUT->footer();
        exit;
    }
}

echo $OUTPUT->header();

if (!empty($report)) {
    echo '<pre>';
    echo $report;
    echo '</pre>';
}

echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $courseid)));
echo $OUTPUT->footer();
