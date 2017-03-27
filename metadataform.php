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
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package mod_sharedresource
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classificationlib.php');

$PAGE->requires->js('/mod/sharedresource/js/jquery-1.8.2.min.js');
$PAGE->requires->js('/mod/sharedresource/js/metadata.php');
$PAGE->requires->js('/mod/sharedresource/js/metadata_yui.php');

$add = optional_param('add', 0, PARAM_ALPHA);
$update = optional_param('update', 0, PARAM_INT);
$return = optional_param('return', 0, PARAM_BOOL); // Return to course/view.php if false or mod/modname/view.php if true.
$section = optional_param('section', 0, PARAM_INT);
$mode = required_param('mode', PARAM_ALPHA);
$courseid = required_param('course', PARAM_INT);
$sharingcontext = required_param('context', PARAM_INT);

// Working context check.

if (! $course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('coursemisconf');
}

$system_context = context_system::instance();
$context = context_course::instance($course->id);

if ($courseid > SITEID) {
    require_course_login($course, true);
    $pagecontext = $context;
} else {
    require_login();
    $pagecontext = $system_context;
}

// Check incoming resource.

if (!isset($SESSION->sr_entry)) {
    if ($course > SITEID) {
        redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
    } else {
        redirect($CFG->wwwroot);
    }
}

$sr_entry = $SESSION->sr_entry;
$sharedresource_entry = unserialize($sr_entry);

// Load working metadata plugin.

require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$CFG->pluginchoice.'/plugin.class.php');
$object = 'sharedresource_plugin_'.$CFG->pluginchoice;
$mtdstandard = new $object;

// Building $PAGE.

$strtitle = get_string($mode.'metadataform', 'sharedresource');
$PAGE->set_pagelayout('standard');
$PAGE->set_context($pagecontext);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'),"{$CFG->wwwroot}/mod/sharedresource/index.php?id=$course->id");
$PAGE->navbar->add(get_string($mode.'sharedresourcetypefile', 'sharedresource'));

$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');
$PAGE->set_headingmenu('');

$url = new moodle_url('/mod/sharedresource/metadataform.php');
$PAGE->set_url($url);

echo $OUTPUT->header();

if (has_capability('repository/sharedresources:systemmetadata', $context)) {
    $capability = 'system';
} else if (has_capability('repository/sharedresources:indexermetadata', $context)) {
    $capability = 'indexer';
} else if (has_capability('repository/sharedresources:authormetadata', $context)) {
    $capability = 'author';
} else {
    print_error('noaccessform', 'sharedresource');
}

if (!empty($CFG->METADATATREE_DEFAULTS)) {
    $mtdstandard->load_defaults($CFG->METADATATREE_DEFAULTS);
}

metadata_initialise_core_elements($mtdstandard, $sharedresource_entry);

$nbrmenu = count($mtdstandard->METADATATREE[0]['childs']);

echo '<center>';
echo '<div id="ecform_container" align="center">';

echo '<div align="center" id="ecform_title">'.get_string('metadatadescr', 'sharedresource').' ('.$mtdstandard->pluginname.')';
echo '</div>';
echo '<br/>';

echo '<div id="ecform_onglet" class="ecformtab tabtree">';
echo '<ul id="menu" class="tabrow0">';
echo '<li id="menu_0" class="first onerow here selected" style="float: left;display: inline;">';
echo '<a id="_0" class="current" onclick="multiMenu(this.id,'.$nbrmenu.')" alt="menu0"><span>'.get_string('DMused','sharedresource').'</span></a>';
echo '</li>';
echo metadara_create_tab($capability, $mtdstandard);
echo '</ul>';
echo '</div><br/>';

echo '<div id="ecform_content" style="margin-right: auto; margin-left: auto">';
echo '<div id="tab_0" class="on content">';
echo '<div class="titcontent">';

echo '<h2 >'.get_string('DMuse', 'sharedresource').' '.$mtdstandard->pluginname.'</h2>';
echo '<h3>'.get_string('DMdescription', 'sharedresource').' '.$mtdstandard->pluginname.'</h3><br/>';

echo '<fieldset style="width:90%;margin-right: auto; margin-left: auto">';
echo '<div style="text-align:justify;align=left;">';
echo get_string('standarddescription', 'sharedmetadata_'.$mtdstandard->pluginname);
echo '</div>';
echo '</fieldset>';

echo '</div>';
echo '</div>';

echo metadata_create_panels($capability, $mtdstandard);
echo '</div><br/>';

echo '<div align="center">';
$jshandler = 'document.forms["monForm"].submit()';
echo '<input type="button"
             value="'.get_string('validateform', 'sharedresource').'"
             id="btsubmit"
             onClick="'.$jshandler.'" alt="Submit"/>';

$params = array('course' => $course->id, 'section' => $section, 'add' => 'sharedresource', 'return' => $return);
$returl = new moodle_url('/course/modedit.php', $params);

echo ' <input type="button"
              value="'.get_string('cancelform', 'sharedresource').'"
              onClick="window.location.href="'.$returl.'">';
echo '</div>';

echo '</div>';
echo '</center>';

echo $OUTPUT->footer($course);
