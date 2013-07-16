<?php

/**
 *
 * @author  Frederic GUILLOU
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */

// This php script displays the metadata form

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classificationlib.php');

$mode            = required_param('mode', PARAM_ALPHA);
$course          = required_param('course', PARAM_INT);
$sharingcontext  = required_param('context', PARAM_INT);
$pluginchoice    = required_param('pluginchoice', PARAM_ALPHA);
/* $pagestep      = optional_param('pagestep', 1, PARAM_INT); */
$add           = optional_param('add', 0, PARAM_ALPHA);
$update        = optional_param('update', 0, PARAM_INT);
$return        = optional_param('return', 0, PARAM_BOOL); //return to course/view.php if false or mod/modname/view.php if true
$section       = optional_param('section', 0, PARAM_INT);

$sr_entry = $SESSION->sr_entry;
$sharedresource_entry = unserialize($sr_entry);

require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$pluginchoice.'/plugin.class.php');

if (! $course = $DB->get_record('course', array('id'=> $course))) {
    print_error('coursemisconf');
}

/// security

require_login($course);
$system_context = context_system::instance();
$context = context_course::instance($course->id);

$strtitle = get_string($mode.'metadataform', 'sharedresource');
$PAGE->set_pagelayout('standard');
$PAGE->set_context($system_context);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
/* SCANMSG: may be additional work required for $navigation variable */
$PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'),"{$CFG->wwwroot}/mod/sharedresource/index.php?id=$course->id");
$PAGE->navbar->add(get_string($mode.'sharedresourcetypefile', 'sharedresource'));

$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');
$PAGE->set_headingmenu('');

$url = new moodle_url('/mod/sharedresource/metadataform.php');
$PAGE->set_url($url);
echo $OUTPUT->header();

$PAGE->requires->js('/mod/sharedresource/js/jquery-1.8.2.min.js');
$PAGE->requires->js('/mod/sharedresource/js/metadata_yui.php');

if(has_capability('mod/sharedresource:systemmetadata', $context)){
    $capability = 'system';
} elseif(has_capability('mod/sharedresource:indexermetadata', $context)){
    $capability = 'indexer';
} elseif(has_capability('mod/sharedresource:authormetadata', $context)){
    $capability = 'author';
} else {
    print_error('noaccessform', 'sharedresource');
}

$object = 'sharedresource_plugin_'.$pluginchoice;
$mtdstandard = new $object;
if (!empty($CFG->METADATATREE_DEFAULTS)){
    $mtdstandard->load_defaults($CFG->METADATATREE_DEFAULTS);
}
$nbrmenu = count($mtdstandard->METADATATREE[0]['childs']);

// If a metadata card has already been submitted in an another metadata model, we warn the user about that
if ($mode != 'add'){
    echo metadata_detect_change_DM($sharedresource_entry, $pluginchoice);
}

echo '<center>';
echo '<div id="ecform_container" align="center">';

echo '<div align="center" id="ecform_title">'.get_string('metadatadescr','sharedresource').' ('.$mtdstandard->pluginname.')';
echo '</div>';
echo '<br/>';

echo '<div id="ecform_onglet" class="ecformtab">';
echo '<ul id="menu" class="tabrow0">';
echo '<li class="first onerow here selected" style="float: left;display: inline;">';
echo '<a id="_0" class="current" onclick="multiMenu(this.id,'.$nbrmenu.')" alt="menu0"><span>'.get_string('DMused','sharedresource').'</span></a>';
echo '</li>';
echo metadara_create_tab($capability, $mtdstandard);
echo '</ul>';
echo '</div><br/>';

echo '<div id="ecform_content" style="margin-right: auto; margin-left: auto">';
echo '<div id="tab_0" class="on content">';
echo '<div class="titcontent">';

echo '<h2 >'.get_string('DMuse','sharedresource').' '.$mtdstandard->pluginname.'</h2>';
echo '<h3>'.get_string('DMdescription','sharedresource').' '.$mtdstandard->pluginname.'</h3><br/>';

echo '<fieldset style="width:90%;margin-right: auto; margin-left: auto">';
echo '<div style="text-align:justify;align=left;">';
echo get_string('description'.$mtdstandard->pluginname,'sharedresource');
echo '</div>';
echo '</fieldset>';

echo '</div>';
echo '</div>';

echo metadata_create_panels($capability, $mtdstandard);
echo '</div><br/>';

echo '<div align="center">';
echo '<input type="button" value="'.get_string('validateform','sharedresource').'" id="btsubmit" onClick=\'document.forms["monForm"].submit()\' alt="Submit"/>';
echo ' <input type="button" value="'.get_string('cancelform','sharedresource').'" OnClick="window.location.href=\''.$CFG->wwwroot."/course/modedit.php?course={$course->id}&section={$section}&add=sharedresource&return={$return}".'/\'">';
echo '</div>';

echo '</div>';
echo '</center>';

echo $OUTPUT->footer($course);
