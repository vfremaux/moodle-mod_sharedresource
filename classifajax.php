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
<<<<<<< HEAD
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
=======
 * This php script is called using ajax
 * It displays childs of a selected option in a SELECT
 * when a classification is displayed
 *
 * @author  Frederic GUILLOU
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */
require_once('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/classificationlib.php');

$name = required_param('name', PARAM_TEXT);
$num = required_param('num', PARAM_INT);
$key = required_param('key', PARAM_TEXT);
$classif = required_param('classif', PARAM_TEXT);
$value = required_param('value', PARAM_TEXT);

if ($classif != 'basicvalue') {
    print_classification_childs($name, $num, $key, $classif, $value);
}
>>>>>>> MOODLE_32_STABLE
