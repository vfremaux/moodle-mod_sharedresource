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
 * Allows adding or updating a single taxon in the sharedresource_taxonomy table.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/forms/classificationvalue_form.php');

$classifid = required_param('classificationid', PARAM_INT); // The classification id.
$parent = required_param('parent', PARAM_INT); // The parent of this taxon.
$id = optional_param('id', 0, PARAM_INT); // The taxon id.

// Security.

$systemcontext = context_system::instance();
require_login();
require_capability('repository/sharedresources:manage', $systemcontext);

// Build page.

$url = new moodle_url('/mod/sharedresource/classificationvalue.php', ['classificationid' => $classifid, 'parent' => $parent]);
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');

$mform = new classificationvalue_form();

if ($mform->is_cancelled()) {
    $params = ['id' => $classifid, 'parent' => $parent];
    $redirecturl = new moodle_url('/mod/sharedresource/classificationvalues.php', $params);
    redirect($redirecturl);
}

$classification = $DB->get_record('sharedresource_classif', ['id' => $classifid]);

if ($data = $mform->get_data()) {
    if (!empty($data->id)) {
        $DB->update_record('sharedresource_taxonomy', $data);
    } else {
        $DB->insert_record('sharedresource_taxonomy', $data);
    }

    $params = ['id' => $classifid, 'parent' => $parent];
    $redirecturl = new moodle_url('/mod/sharedresource/classificationvalues.php', $params);
    redirect($redirecturl);
}

echo $OUTPUT->header();

$cmd = (empty($id)) ? 'add' : 'update';

echo $OUTPUT->heading(get_string($cmd.'classificationvalue', 'sharedresource'));

if ($id) {
    $taxon = $DB->get_record('sharedresource_taxonomy', ['id' => $id]);
    $mform->set_data($taxon);
} else {
    $taxon = new StdClass;
    $taxon->parent = $parent;
    $taxon->classificationid = $classifid;
    $params = ['classificationid' => $classifid, 'parent' => $parent];
    $maxorder = $DB->get_field('sharedresource_taxonomy', 'MAX(sortorder)', $params);
    if (!is_null($maxorder)) {
        $taxon->sortorder = $maxorder + 1;
    } else {
        $taxon->sortorder = $classification->sqlsortorderstart;
    }
    $mform->set_data($taxon);
}

$mform->display();

echo $OUTPUT->footer();
