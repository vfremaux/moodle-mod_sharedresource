<?php

/**
*
* @author  Frederic GUILLOU
* @version 0.0.1
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
* @package sharedresource
*
*/

// This php script is called using ajax
// It displays childs of a selected option in a SELECT
// when a classification is displayed
//-----------------------------------------------------------

	require_once("../../config.php");
	require_once($CFG->dirroot.'/mod/sharedresource/classificationlib.php');
	
	$name = required_param('name', PARAM_TEXT);
	$num = required_param('num', PARAM_INT);
	$key = required_param('key', PARAM_TEXT);
	$classif = required_param('classif', PARAM_TEXT);
	$value = required_param('value', PARAM_TEXT);
	
	// debug_trace("$name, $num, $key, $classif, $value");
	
	if ($classif != 'basicvalue'){
		print_classification_childs($name, $num, $key, $classif, $value);
	}
