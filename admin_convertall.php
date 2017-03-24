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
 * this admin screen allows converting massively resources into sharedresources
 * indexable entries.
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

$courseid = optional_param('course', SITEID, PARAM_INT);

<<<<<<< HEAD
	/// security
	
	    $context = context_course::instance($courseid);
	    require_login($course);
	    require_capability('moodle/course:manageactivities', $context);
    	$PAGE->set_context($context);
	} else {
	    $systemcontext = context_system::instance();
	    require_login();
	    require_capability('mod/sharedresource:editcatalog', $systemcontext);
    	$PAGE->set_context($systemcontext);
	}
=======
if ($courseid > SITEID) {
    if (!$course = $DB->get_record('course', array('id'=> "$courseid"))){
        print_error('coursemisconf');
    }
>>>>>>> MOODLE_32_STABLE

// Security.

    $context = context_course::instance($courseid);
    require_login($course);
    require_capability('moodle/course:manageactivities', $context);
    $PAGE->set_context($context);
} else {
    $systemcontext = context_system::instance();
    require_login();
    require_capability('mod/sharedresource:editcatalog', $systemcontext);
    $PAGE->set_context($systemcontext);
}

<<<<<<< HEAD
    if (empty($courseid)){
    	// if no course choosen (comming from general sections) make the choice of one.
        $allcourses = $DB->get_records_menu('course', null, 'shortname', 'id,fullname');
        $form = new sharedresource_choosecourse_form($allcourses);
        if ($form->is_cancelled()){
            redirect($CFG->wwwroot.'/resources/index.php');
        }
    	echo $OUTPUT->header();
        $form->display();
        echo $OUTPUT->footer();
        die;
    } else {
        // back to library if cancelled
        $form = new sharedresource_choosecourse_form(null);
        if ($form->is_cancelled()){
            redirect($CFG->wwwroot.'/resources/index.php');
        }
        $resources = $DB->get_records('resource', array('course' => $courseid), 'name');
        $urls = $DB->get_records('url', array('course' => $courseid),'name');
        if (empty($resources) && empty($urls)){
    		echo $OUTPUT->header();  
            echo $OUTPUT->notification(get_string('noresourcestoconvert', 'sharedresource'));
            echo $OUTPUT->continue_button($CFG->wwwroot."/course/view.php?id={$courseid}&amp;action=activities");
            echo $OUTPUT->footer();
            exit();
        }
=======
$PAGE->set_title(get_string('resourceconversion', 'sharedresource'));
$PAGE->set_heading(get_string('resourceconversion', 'sharedresource'));
$PAGE->set_url('/mod/sharedresource/admin_convertall.php', array('course' => $courseid));
$PAGE->set_pagelayout('standard');

// Navigation.
$PAGE->navbar->add(get_string('resourceconversion', 'sharedresource'));
$PAGE->navbar->add(get_string('resourcetorepository', 'sharedresource'));

// Get courses.

if (empty($courseid)){
    // If no course choosen (comming from general sections) make the choice of one.
    $allcourses = $DB->get_records_menu('course', null, 'shortname', 'id,fullname');
    $form = new sharedresource_choosecourse_form($allcourses);
    if ($form->is_cancelled()){
        redirect($CFG->wwwroot.'/resources/index.php');
    }
    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
    die;
} else {
    // Back to library if cancelled.
    $form = new sharedresource_choosecourse_form(null);
    if ($form->is_cancelled()){
        redirect($CFG->wwwroot.'/resources/index.php');
    }
    $resources = $DB->get_records('resource', array('course' => $courseid), 'name');
    $urls = $DB->get_records('url', array('course' => $courseid),'name');
    if (empty($resources) && empty($urls)){
        echo $OUTPUT->header();  
        echo $OUTPUT->notification(get_string('noresourcestoconvert', 'sharedresource'));
        echo $OUTPUT->continue_button($CFG->wwwroot."/course/view.php?id={$courseid}&amp;action=activities");
        echo $OUTPUT->footer();
        exit();
    }
>>>>>>> MOODLE_32_STABLE

    $form2 = new sharedresource_selectresources_form($course, $resources, $urls);

    /// if data submitted, proceed
    if ($data = $form2->get_data()){
        if ($form2->is_cancelled()){
            if ($courseid){
                print_string('conversioncancelledtocourse', 'sharedresource');
                redirect($CFG->wwwroot."/course/view.php?id={$courseid}&amp;action=activities");
            } else {
                print_string('conversioncancelledtolibrary', 'sharedresource');
                redirect($CFG->wwwroot."/resources/index.php");
            }
        }
        $reskeys = preg_grep("/rcnv_/" , array_keys(get_object_vars($data)));
        if (!empty($reskeys)){
            foreach($reskeys as $reskey){
                // convert selected resources.
                if ($data->$reskey == 1){
                    $resid = str_replace('rcnv_', '', $reskey);
                    $resource = $DB->get_record('resource', array('id' => $resid));
                    mtrace("converting resource {$resource->id} : {$resource->name}<br/>\n");
                    sharedresource_convertto($resource, 'resource');
                }
            }
        }
        $reskeys = preg_grep("/ucnv_/" , array_keys(get_object_vars($data)));
        if (!empty($reskeys)){
            foreach($reskeys as $reskey){
                // convert selected resources.
                if ($data->$reskey == 1){
                    $resid = str_replace('ucnv_', '', $reskey);
                    $url = $DB->get_record('url', array('id' => $resid));
                    mtrace("converting url {$url->id} : {$url->name}<br/>\n");
                    sharedresource_convertto($url, 'url');
                }
            }
        }
    } else {
        // print form
        echo $OUTPUT->header();
        $form2->display();
        if ($course){
             print ($OUTPUT->footer($course));
        } else {
<<<<<<< HEAD
            // print form
    		echo $OUTPUT->header();
            $form2->display();
            if ($course){
             	print ($OUTPUT->footer($course));
            } else {
                print ($OUTPUT->footer());
            }
            die;
=======
            print ($OUTPUT->footer());
>>>>>>> MOODLE_32_STABLE
        }
        die;
    }
}

<<<<<<< HEAD
	echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('resourceconversion', 'sharedresource'), 1);

    echo $OUTPUT->continue_button($CFG->wwwroot."/course/view.php?id=$courseid");
    echo $OUTPUT->footer();
=======
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('resourceconversion', 'sharedresource'), 1);

echo $OUTPUT->continue_button($CFG->wwwroot."/course/view.php?id=$courseid");
echo $OUTPUT->footer();
>>>>>>> MOODLE_32_STABLE
