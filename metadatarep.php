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
 * Displays the filled fields of the metadata
 * form and save these metadata and the resource. 
 * It informs the user if there are some errors and in that 
 * case, the resource is not saved and the user is sent back
 * to the metadata form
 *
 * @author  Frederic GUILLOU
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */
<<<<<<< HEAD
    // This php script displays the filled fields of the metadata
	// form and save these metadata and the resource. 
	// It informs the user if there are some errors and in that 
	// case, the resource is not saved and the user is sent back
	// to the metadata form
    //-----------------------------------------------------------
	require_once("../../config.php");
	require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
	require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
	require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$CFG->pluginchoice.'/plugin.class.php');

	$mode          = required_param('mode', PARAM_ALPHA);
	$add           = optional_param('add', 0, PARAM_ALPHA);
	$update        = optional_param('update', 0, PARAM_INT);
	$return        = optional_param('return', 0, PARAM_BOOL); //return to course/view.php if false or mod/modname/view.php if true
	$section       = optional_param('section', 0, PARAM_INT);
	$course        = required_param('course', PARAM_INT);
	$sharingcontext = optional_param('context', 1, PARAM_INT);
	$entries = preg_grep("#^\d#", array_keys($_POST));
	$metadataentries = array();

	foreach($entries as $key => $value){
		$metadataentries[$value] = required_param($value, PARAM_TEXT);
	}

	if (! $course = $DB->get_record('course', array('id'=> $course))) {
		print_error('badcourseid', 'sharedresource');
	}

	require_login($course);
	$context = context_course::instance($course->id);

	$pagetitle = strip_tags($course->shortname);
	$strtitle = $pagetitle;
	$PAGE->set_pagelayout('standard');
	$system_context = context_system::instance();
	$PAGE->set_context($system_context);
	$url = new moodle_url('/mod/sharedresource/metadatarep.php');
	$PAGE->set_url($url);
	$PAGE->set_title($strtitle);
	$PAGE->set_heading($SITE->fullname);

	/* navigation */
	$PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'),"{$CFG->wwwroot}/mod/sharedresource/index.php?id=$course->id",'activity');
	$PAGE->navbar->add($strtitle,'metadatarep.php','misc');
	$PAGE->navbar->add(get_string($mode.'sharedresourcetypefile', 'sharedresource'));

	$PAGE->set_focuscontrol('');
	$PAGE->set_cacheable(false);
	$PAGE->set_button('');
	$PAGE->set_headingmenu('');

	$SESSION->error = '';
	$sr_entry = $SESSION->sr_entry;
	$sharedresource_entry = unserialize($sr_entry);

	//if it's an update, metadata of the sharedresource should be deleted before adding new ones

	if ($mode != 'add'){
		foreach($sharedresource_entry->metadata_elements as $key => $metadata){
			unset($sharedresource_entry->metadata_elements[$key]);
		}
	}
	$result = metadata_display_and_check($sharedresource_entry, $CFG->pluginchoice, $metadataentries);

	//if there are errors in fields filled in by the user
	if($result['error'] != array()){
		$sr_entry = serialize($sharedresource_entry);
		$SESSION->sr_entry = $sr_entry;
		$error = serialize($result['error']);
		$SESSION->error = $error;
		$object = 'sharedresource_plugin_'.$CFG->pluginchoice;
		$mtdstandard = new $object;
		echo $OUTPUT->header();
		echo $OUTPUT->heading(get_string($mode.'sharedresourcetypefile', 'sharedresource'));
		echo '<center>';
		echo get_string('errormetadata', 'sharedresource');
		echo '<br/><br/>';
		foreach($result['error'] as $field => $errortype){
			$fieldnum = substr($field,0,strpos($field,':'));
			echo '<strong> - '.$fieldnum.' : '.$mtdstandard->METADATATREE[$fieldnum]['name'].'</strong><br/><br/>';
		}
		$fullurl = $CFG->wwwroot."/mod/sharedresource/metadataform.php?course={$course->id}&section={$section}&add=sharedresource&return={$return}&mode={$mode}&context={$sharingcontext}";
		$OUTPUT->continue($fullurl, get_string('wrongform', 'sharedresource'), 15);
		echo '</center>';
		echo $OUTPUT->footer();
	} else {
		//these two lines in comment can be used if you want to show the user values of saved fields
		/*echo '<h1>'.get_string('attributes','sharedresource').'</h1><br/>';
		echo $result['display'];*/
		if ($mode == 'add' && !$sharedresource_entry->exists() && !$sharedresource_entry->add_instance()) {
			print_error('failadd', 'sharedresource');
		} else if (!$sharedresource_entry->update_instance()) {
			print_error('failupdate', 'sharedresource');
		} else {
			// if everything was saved correctly, go back to the search page or to the library
			if ($return){
				$fullurl = $CFG->wwwroot."/local/sharedresources/index.php?course={$course->id}";
				redirect($fullurl, get_string('correctsave', 'sharedresource'), 5);
			} else {
				$fullurl = $CFG->wwwroot."/mod/sharedresource/search.php?id={$sharedresource_entry->id}&course={$course->id}&section={$section}&add={$add}&return={$return}";
				redirect($fullurl, get_string('correctsave', 'sharedresource'), 5);
			}
			die;
		}
	}
=======

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$CFG->pluginchoice.'/plugin.class.php');

$mode          = required_param('mode', PARAM_ALPHA);
$add           = optional_param('add', 0, PARAM_ALPHA);
$update        = optional_param('update', 0, PARAM_INT);
$return        = optional_param('return', 0, PARAM_BOOL); //return to course/view.php if false or mod/modname/view.php if true
$section       = optional_param('section', 0, PARAM_INT);
$course        = required_param('course', PARAM_INT);
$sharingcontext = optional_param('context', 1, PARAM_INT);
$entries = preg_grep("#^\d#", array_keys($_POST));
$metadataentries = array();

foreach($entries as $key => $value){
    $metadataentries[$value] = required_param($value, PARAM_TEXT);
}

if (! $course = $DB->get_record('course', array('id'=> $course))) {
    print_error('badcourseid', 'sharedresource');
}

require_login($course);
$context = context_course::instance($course->id);

$pagetitle = strip_tags($course->shortname);
$strtitle = $pagetitle;
$PAGE->set_pagelayout('standard');
$system_context = context_system::instance();
$PAGE->set_context($system_context);
$url = new moodle_url('/mod/sharedresource/metadatarep.php');
$PAGE->set_url($url);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);

/* navigation */
$PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'),"{$CFG->wwwroot}/mod/sharedresource/index.php?id=$course->id",'activity');
$PAGE->navbar->add($strtitle,'metadatarep.php','misc');
$PAGE->navbar->add(get_string($mode.'sharedresourcetypefile', 'sharedresource'));

$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');
$PAGE->set_headingmenu('');

$SESSION->error = '';
$sr_entry = $SESSION->sr_entry;
$sharedresource_entry = unserialize($sr_entry);

// If it's an update, metadata of the sharedresource should be deleted before adding new ones.

if ($mode != 'add') {
    foreach ($sharedresource_entry->metadata_elements as $key => $metadata) {
        unset($sharedresource_entry->metadata_elements[$key]);
    }
}
$result = metadata_display_and_check($sharedresource_entry, $CFG->pluginchoice, $metadataentries);

// If there are errors in fields filled in by the user.

if ($result['error'] != array()) {
    $sr_entry = serialize($sharedresource_entry);
    $SESSION->sr_entry = $sr_entry;
    $error = serialize($result['error']);
    $SESSION->error = $error;
    $object = 'sharedresource_plugin_'.$CFG->pluginchoice;
    $mtdstandard = new $object;
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string($mode.'sharedresourcetypefile', 'sharedresource'));
    echo '<center>';
    echo get_string('errormetadata', 'sharedresource');
    echo '<br/><br/>';

    foreach ($result['error'] as $field => $errortype) {
        $fieldnum = substr($field,0,strpos($field,':'));
        echo '<strong> - '.$fieldnum.' : '.$mtdstandard->METADATATREE[$fieldnum]['name'].'</strong><br/><br/>';
    }

    $params = array('course' => $course->id,
                    'section' => $section,
                    'add' => 'sharedresource',
                    'return' => $return,
                    'mode' => $mode,
                    'context' => $sharingcontext);
    $fullurl = new moodle_url('/mod/sharedresource/metadataform.php', $params);
    $OUTPUT->continue($fullurl, get_string('wrongform', 'sharedresource'), 15);
    echo '</center>';
    echo $OUTPUT->footer();
} else {
    //these two lines in comment can be used if you want to show the user values of saved fields
    /*echo '<h1>'.get_string('attributes','sharedresource').'</h1><br/>';
    echo $result['display'];*/
    if ($mode == 'add' && !$sharedresource_entry->exists() && !$sharedresource_entry->add_instance()) {
        print_error('failadd', 'sharedresource');
    } elseif (!$sharedresource_entry->update_instance()) {
        print_error('failupdate', 'sharedresource');
    } else {
        // if everything was saved correctly, go back to the search page or to the library
        if ($return) {
            $fullurl = $CFG->wwwroot."/local/sharedresources/index.php?course={$course->id}";
            redirect($fullurl, get_string('correctsave', 'sharedresource'), 5);
        } else {
            $params = array('id' => $sharedresource_entry->id,
                            'course' => $course->id,
                            'section' => $section,
                            '&add' => $add,
                            'return' => $return);
            $fullurl = new moodle_url('/mod/sharedresource/search.php', $params);
            redirect($fullurl, get_string('correctsave', 'sharedresource'), 5);
        }
        die;
    }
}
>>>>>>> MOODLE_32_STABLE
