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
 * This script produces a metadata subtree form fragment for element having a list type, after the user
 * clicked on the add button
 *
 * @author  Frédéric GUILLOU
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classificationlib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_metadata.class.php');

$elementname = required_param('elementname', PARAM_TEXT);
$realoccur = optional_param('realoccur', null, PARAM_INT);

$elementid = \mod_sharedresource\metadata::html_to_storage($elementname);

$url = new moodle_url('/mod/sharedresource/ajax/getformelement.php');

$PAGE->set_url($url);

// Do not really check contextual security. We are just requiring a form part.
$PAGE->set_context(context_system::instance());

require_login();

$config = get_config('sharedresource');
$mtdstandard = sharedresource_get_plugin($config->schema);
list($nodeid, $instanceid) = explode(':', $elementid);
$element = $mtdstandard->getElement($nodeid);

$capability = metadata_get_user_capability();

$renderer = $PAGE->get_renderer('mod_sharedresource', 'metadata');

$template = new StdClass;
$renderer->part_form($template, $elementid, $capability, $realoccur);
$result = new StdClass;
$result->html = $OUTPUT->render_from_template('mod_sharedresource/metadataeditformchilds', $template);
$result->name = $elementname.'-'.$element->type;
echo json_encode($result);