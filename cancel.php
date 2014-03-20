<?php

	include '../../config.php';
	$courseid = required_param('course', PARAM_INT);
	$return = required_param('return', PARAM_INT);
	
	if (!$course = $DB->get_record('course', array('id' => $courseid))){
		print_error('coursemisconf');
	}

	$context = context_course::instance($courseid);
	
	/// security
	
	require_course_login($course);
	require_capability('moodle/course:manageactivities', $context);
	
	/// Route depending on return
	
	unset($SESSION->sr_entry);
	
	if (!$return && ($courseid > SITEID)){
		redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
	} else {
		$systemcontext = context_system::instance();
		if (has_capability('repository/sharedresources:view', $systemcontext)){
			redirect($CFG->wwwroot.'/local/sharedresources/index.php');
		} else {
			$redirect($CFG->wwwroot);
		}
	}