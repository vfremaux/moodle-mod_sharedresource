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
 * Allows managing access rules for this classification. this affects the availability of taxonomy in sharedresource edition
 * form, in search form, and filters resources results when bound to restricted taxonomies only.
 * Alternatively it allows setting an access control definition to a resource instance.
 *
 */
require('../../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/pro/forms/classificationacl_form.php');

$classifid = optional_param('classificationid', 0, PARAM_INT); // The classification id.
$resourceid = optional_param('resourceid', 0, PARAM_INT); // The resource id.
$courseid = optional_param('courseid', 0, PARAM_INT); // The environment course id if any.
$return = optional_param('return', '', PARAM_TEXT); // Where to return after operation.

// Security.

$systemcontext = context_system::instance();
require_login();
require_capability('repository/sharedresources:manage', $systemcontext);

// Build page.

$params = array('classificationid' => $classifid, 'resourceid' => $resourceid, 'courseid' => $courseid, 'return' => $return);
$url = new moodle_url('/mod/sharedresource/pro/classificationacls.php', $params);
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');

if ($classifid) {
    $classification = $DB->get_record('sharedresource_classif', array('id' => $classifid));
    $acls = unserialize($classification->accessctl);
} else if ($resourceid) {
    $shrentry = \mod_sharedresource\entry::read_by_id($resourceid);
    $acls = unserialize($shrentry->accessctl);
    $return = optional_param('return', 'locallib', PARAM_TEXT);
    $courseid = optional_param('courseid', 0, PARAM_INT);
} else {
    print_error(get_string('erroraclmisconf', 'sharedresource'));
}

$mform = new classificationacl_form($url, array('profnum' => count(@$acls->profilefields), 'capnum' => count(@$acls->capabilities)));

if ($mform->is_cancelled()) {
    if ($classifid) {
        $redirecturl = new moodle_url('/mod/sharedresource/classifications.php', array('id' => $classifid));
    } else {
        if ($return == 'localindex') {
            $params = array('courseid' => $courseid);
            $redirecturl = new moodle_url('/local/sharedresources/index.php', $params);
        }
    }
    redirect($redirecturl);
}

$data = $mform->get_data();
$data = (object)$_POST; // Some weird behaviour.

if (!empty($_POST)) {

    $accessctl = new StdClass;
    $notempty = false;

    // Make a serializable object array
    for ($i = 0; $i < $data->profile_numfields; $i++) {
        if (!empty($data->profilefield[$i])) {
            $profileobj = new Stdclass;
            $profileobj->profilefield = $data->profilefield[$i];
            $profileobj->values = $data->values[$i];
            $accessctl->profilefields[] = $profileobj;
            $notempty = true;
        }
    }

    for ($i = 0; $i < $data->capability_numfields; $i++) {
        if (!empty($data->capability[$i])) {
            $capobj = new Stdclass;
            $capobj->capability = $data->capability[$i];
            $capobj->contextlevel = $data->contextlevel[$i];
            $capobj->instanceid = $data->instanceid[$i];
            $accessctl->capabilities[] = $capobj;
            $notempty = true;
        }
    }

    if ($classifid) {
        if ($notempty) {
            // Keep settings in DB.
            $accessctlsr = serialize($accessctl);
            $DB->set_field('sharedresource_classif', 'accessctl', $accessctlsr, array('id' => $classifid));
        } else {
            $DB->set_field('sharedresource_classif', 'accessctl', '', array('id' => $classifid));
        }

        $params = array('id' => $classifid);
        $redirecturl = new moodle_url('/mod/sharedresource/classifications.php', $params);
    } else if (!empty($resourceid)) {
        if ($notempty) {
            // Keep settings in DB.
            $accessctlsr = serialize($accessctl);
            $DB->set_field('sharedresource_entry', 'accessctl', $accessctlsr, array('id' => $resourceid));
        } else {
            $DB->set_field('sharedresource_entry', 'accessctl', '', array('id' => $resourceid));
        }

        $params = array('id' => $resourceid);
        if ($return == 'localindex') {
            $params = array('courseid' => $courseid);
            $redirecturl = new moodle_url('/local/sharedresources/index.php', $params);
        }
    } else {
        echo "Storing error";
    }

    redirect($redirecturl);
}

echo $OUTPUT->header();

if ($classifid) {
    echo $OUTPUT->heading(get_string('classificationacls', 'sharedresource', $classification->name));
} else {
    echo $OUTPUT->heading(get_string('resourceacls', 'sharedresource', $shrentry->title));
}

if ($classifid) {
    if (!empty($classification->accessctl)) {

        // Get stored data.
        $formdata = $mform->decompact_acls($acls);
        $mform->set_data((object)$formdata);
    }
} else {
    if (!empty($shrentry->accessctl)) {

        // Get stored data.
        $formdata = $mform->decompact_acls($acls);
        $mform->set_data((object)$formdata);
    }
}

$mform->display();

echo $OUTPUT->footer();
