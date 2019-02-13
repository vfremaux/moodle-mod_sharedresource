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
 * @package block_dashboard
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version Moodle 2.2
 */
ob_start();
require('../../../config.php');
require_once($CFG->dirroot.'/blocks/dashboard/pro/copyconfig_form.php');

// Setting contexts.

$id = required_param('id', PARAM_INT); // Course ID.
$instanceid = required_param('instance', PARAM_INT); // Block instance ID.
$action = optional_param('what', '', PARAM_TEXT);

if (!$course = $DB->get_record('course', array('id' => "$id"))) {
    print_error('invalidcourseid');
}

if (!$instance = $DB->get_record('block_instances', array('id' => "$instanceid"))) {
    print_error('badblockinstance', 'block_dashboard');
}

$theBlock = block_instance('dashboard', $instance);

// Security.

require_login($course);
require_capability('block/dashboard:configure', context_system::instance(0));

// Get a copy of block configuration.

if ($action == 'get') {
    ob_end_clean();
    header("Content-Type:text/raw\n\n");
    echo $instance->configdata;
    die;
}

// Process form.

$url = new moodle_url('/blocks/dashboard/pro/copyconfig.php', array('id' => $course->id, 'instance' => $instanceid));

$mform = new CopyConfig_Form($url);

if ($mform->is_cancelled()) {
    $params = array('id' => $id, 'bui_edit' => $instanceid, 'sesskey' => sesskey());
    redirect(new moodle_url('/course/view.php', $params));
}

if ($data = $mform->get_data()) {
    $DB->set_field('block_instances', 'configdata', $data->configdata, array('id' => "$instanceid"));
    $params = array('id' => $id, 'bui_edit' => $instanceid, 'sesskey' => sesskey());
    redirect(new moodle_url('/course/view.php', $params));
}

$PAGE->set_url($url);
$PAGE->set_title($SITE->shortname);
$PAGE->set_heading($SITE->shortname);
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('configcopy', 'block_dashboard'));

$mform->display();

echo $OUTPUT->footer($course);
