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
 * @author  Valery Fremaux
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package    sharedresource
 * @subpackage mod_sharedresource
 * @category   mod
 *
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/forms/classification_form.php');

$id = optional_param('id', 0, PARAM_TEXT); // Token id.
$classifid = optional_param('classifid', 0, PARAM_TEXT); // Classification id.

// Security.

$systemcontext = context_system::instance();
require_login();
require_capability('repository/sharedresources:manage', $systemcontext);

// Build page.

$url = new moodle_url('/mod/sharedresource/token.php', array('id' => $id, 'classifid' => $classifid));
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');

$classif = $DB - get_record('sharedresource_classif', array('id' => $classifid));

$mform = new token_form($url, array($classif));

if ($mform->is_cancelled()) {
    $redirecturl = new moodle_url('/mod/sharedresource/tokens.php', array('id' => $classifid));
    redirect($redirecturl);
}

if ($data = $mform->get_data()) {
    if (!empty($data->id)) {
        $DB->update_record($classif->tablename, $data);
    } else {
        $newtokenid = $DB->insert_record($classif->tablename, $data);

        if (!empty($classif->tokenrestriction)) {
            // Must add new id to restriction set.
            $classif->tokenrestriction .= ','.$newtokenid;
            $DB->update_record('sharedresource_classif', $classif);
        }
    }

    $redirecturl = new moodle_url('/mod/sharedresource/tokens.php', array('id' => $classifid));
    redirect($redirecturl);
}

echo $OUTPUT->header();

$cmd = ($id) ? 'add' : 'update';

echo $OUTPUT->heading(get_string($cmd.'token', 'sharedresource'));

if ($id) {
    $tokenrec = $DB->get_record($classif->tablename, array('id' => $id));
    $mform->set_data($tokenrec);
}

$mform->display();

echo $OUTPUT->footer();
