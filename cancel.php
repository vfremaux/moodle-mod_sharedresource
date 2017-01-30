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
 * @package    sharedresource
 * @subpackage mod_sharedresource
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
require('../../config.php');
$courseid = required_param('course', PARAM_INT);
$return = required_param('return', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('coursemisconf');
}

$context = context_course::instance($courseid);

// Security.

require_course_login($course);
require_capability('moodle/course:manageactivities', $context);

// Route depending on return.

unset($SESSION->sr_entry);

if (!$return && ($courseid > SITEID)) {
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
} else {
    $systemcontext = context_system::instance();
    if (has_capability('repository/sharedresources:view', $systemcontext)) {
        redirect($CFG->wwwroot.'/local/sharedresources/index.php');
    } else {
        $redirect($CFG->wwwroot);
    }
}