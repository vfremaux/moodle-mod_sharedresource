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
 * @author  Frederic GUILLOU
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package    mod_sharedresource
 * @category   mod
 */

// This php script displays the
// metadata form
// -----------------------------------------------------------

// Here we need load some classes before config and session is setup to help unserializing
require_once(dirname(dirname(__FILE__)).'/classes/__autoload.php');

require('../../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classificationlib.php');

$returnpage    = optional_param('returnpage', '', PARAM_TEXT); // Return to course/view.php if false or mod/modname/view.php if true.
$fromlibrary   = optional_param('fromlibrary', '', PARAM_BOOL); // Return to course/view.php or to library
$section       = optional_param('section', 0, PARAM_INT);
$mode          = required_param('mode', PARAM_ALPHA);
$type          = required_param('type', PARAM_ALPHA);
$catid         = optional_param('catid', 0, PARAM_INT);
$catpath       = optional_param('catpath', '', PARAM_TEXT);
$courseid      = required_param('course', PARAM_INT);
$sharingcontext = required_param('context', PARAM_INT);

// Working context check.

$course = $DB->get_record('course', ['id' => $courseid],'*', MUST_EXIST);

$systemcontext = context_system::instance();
$context = context_course::instance($course->id);
$config = get_config('sharedresource');

// Security.
$pagecontext = sharedresource_check_access($course);

// Check incoming resource.

if (!isset($SESSION->sr_entry)) {
    if ($course > SITEID) {
        redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
    } else {
        redirect($CFG->wwwroot);
    }
}

$tempentry = $SESSION->sr_entry;
$shrentry = unserialize($tempentry);
// Load working metadata plugin.

$mtdstandard = sharedresource_get_plugin($config->schema);

// Building $PAGE.

$strtitle = get_string($mode.'metadataform', 'sharedresource');
$PAGE->set_pagelayout('standard');
$PAGE->set_context($pagecontext);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$resurl = new moodle_url('/mod/sharedresource/index.php', array('id' => $course->id));
$PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'), $resurl);
$PAGE->navbar->add(get_string($mode.'sharedresourcetypefile', 'sharedresource'));
$PAGE->requires->js_call_amd('mod_sharedresource/metadata', 'init', array($config->schema));
$PAGE->requires->js_call_amd('mod_sharedresource/metadataedit', 'init', array($config->schema));

$params = [
    'returnpage' => $returnpage,
    'fromlibrary' => $fromlibrary,
    'section' => $section,
    'mode' => $mode,
    'catid' => $catid,
    'catpath' => $catpath,
    'course' => $courseid,
    'context' => $sharingcontext
];
$url = new moodle_url('/mod/sharedresource/forms/metadata_form.php', $params);
$PAGE->set_url($url);

echo $OUTPUT->header();

/*
if ($mode == 'update') {
    echo $OUTPUT->notification('DEBUG : Must clone to '.($SESSION->sr_must_clone_to ?? ''));
    echo $OUTPUT->notification('DEBUG : No change '.($SESSION->sr_no_identifier_change ?? ''));
    echo $OUTPUT->notification('DEBUG : Original identifier '.$shrentry->identifier);
    echo $OUTPUT->notification('DEBUG : Type '.$type);
    echo $OUTPUT->notification('DEBUG : Return '.$return);
}
*/

$renderer = $PAGE->get_renderer('mod_sharedresource', 'metadata');

if (has_capability('repository/sharedresources:systemmetadata', $context)) {
    $capability = 'system';
} else if (has_capability('repository/sharedresources:indexermetadata', $context)) {
    $capability = 'indexer';
} else {
    $capability = 'author';
}

if (!empty($CFG->METADATATREE_DEFAULTS)) {
    $mtdstandard->load_defaults($CFG->METADATATREE_DEFAULTS);
}

metadata_initialise_core_elements($mtdstandard, $shrentry);

echo $renderer->metadata_edit_form($capability, $mtdstandard);

echo $OUTPUT->footer($course);
