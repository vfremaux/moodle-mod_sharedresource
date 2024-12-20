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
 * Administration of a single classification domain.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/forms/classification_form.php');

$id = optional_param('id', 0, PARAM_TEXT);

// Security.

$systemcontext = context_system::instance();
require_login();
require_capability('repository/sharedresources:manage', $systemcontext);

// Build page.

$url = new moodle_url('/mod/sharedresource/classification.php', ['id' => $id]);
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');

$mform = new classification_form();

if ($mform->is_cancelled()) {
    $redirecturl = new moodle_url('/mod/sharedresource/classifications.php');
    redirect($redirecturl);
}

if ($data = $mform->get_data()) {
    if (empty($data->enabled)) {
        $data->enabled = 0;
    }
    if (!empty($data->id)) {
        $DB->update_record('sharedresource_classif', $data);
    } else {
        $DB->insert_record('sharedresource_classif', $data);
    }

    $redirecturl = new moodle_url('/mod/sharedresource/classifications.php');
    redirect($redirecturl);
}

echo $OUTPUT->header();

$cmd = ($id) ? 'add' : 'update';

echo $OUTPUT->heading(get_string($cmd.'classification', 'sharedresource'));

if ($id) {
    $classification = $DB->get_record('sharedresource_classif', ['id' => $id]);
    $mform->set_data($classification);
}

$mform->display();

echo $OUTPUT->footer();
