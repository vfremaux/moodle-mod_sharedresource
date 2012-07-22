<?php 

/**
 *
 * @author  Frédéric GUILLOU
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */

    // This php script displays the filled fields of the metadata
	// form and save these metadata and the resource. 
	// It informs the user if there are some errors and in that 
	// case, the resource is not saved and the user is sent back
	// to the metadata form
    //-----------------------------------------------------------
	
	
require_once("../../config.php");
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
$pluginchoice    = required_param('pluginchoice', PARAM_ALPHA);
require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$pluginchoice.'/plugin.class.php');

$mode          = required_param('mode', PARAM_ALPHA);
$add           = optional_param('add', 0, PARAM_ALPHA);
$update        = optional_param('update', 0, PARAM_INT);
$return        = optional_param('return', 0, PARAM_BOOL); //return to course/view.php if false or mod/modname/view.php if true
$type          = optional_param('type', '', PARAM_ALPHANUM);
$section       = optional_param('section', 0, PARAM_INT);
$course        = required_param('course', PARAM_INT);
$pagestep      = optional_param('pagestep', 1, PARAM_INT);
$insertinpage  = optional_param('insertinpage', false, PARAM_INT);

$entries = preg_grep("#^\d#", array_keys($_POST));
$metadataentries = array();
foreach($entries as $key => $value){
	$metadataentries[$value] = required_param($value, PARAM_TEXT);
}

if (! $course = get_record('course', 'id', $course)) {
	error(get_string('badcourseid', 'sharedresource'));
}

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
$pagetitle = strip_tags($course->shortname);

// build up navigation links
$navlinks = array();
$navlinks[] = array('name' => get_string('modulenameplural', 'sharedresource'), 'link' => "{$CFG->wwwroot}/mod/sharedresource/index.php?id=$course->id", 'type' => 'activity');
$navlinks[] = array('name' => get_string($mode.'sharedresourcetypefile', 'sharedresource'), 'link' => '', 'type' => 'title');
$navigation = build_navigation($navlinks);

print_header_simple($pagetitle, '', $navigation, '', '', false);
print_heading_with_help(get_string($mode.'sharedresourcetypefile', 'sharedresource'), 'addsharedresource', 'sharedresource');

$SESSION->error = '';
$sr_entry = $SESSION->sr_entry;
$sharedresource_entry = unserialize($sr_entry);

//if it's an update, metadata of the sharedresource should be deleted before adding new ones
if ($mode != 'add'){
	foreach($sharedresource_entry->metadata_elements as $key => $metadata){
		unset($sharedresource_entry->metadata_elements[$key]);
	}
}
$result = metadata_display_and_check($sharedresource_entry, $pluginchoice, $metadataentries);

//if there are errors in fields filled in by the user
if($result['error'] != array()){
	$sr_entry = serialize($sharedresource_entry);
	$SESSION->sr_entry = $sr_entry;
	$error = serialize($result['error']);
	$SESSION->error = $error;
	$object = 'sharedresource_plugin_'.$pluginchoice;
	$mtdstandard = new $object;
	echo '<center>';
	echo get_string('errormetadata','sharedresource');
	echo '<br/><br/>';
	foreach($result['error'] as $field => $errortype){
		$fieldnum = substr($field,0,strpos($field,':'));
		echo '<strong> - '.$fieldnum.' : '.$mtdstandard->METADATATREE[$fieldnum]['name'].'</strong><br/><br/>';
	}
	$fullurl = $CFG->wwwroot."/mod/sharedresource/metadataform.php?course={$course->id}&section={$section}&type={$type}&add=sharedresource&return={$return}&mode={$mode}&insertinpage={$insertinpage}&pluginchoice={$pluginchoice}";
	redirect($fullurl, get_string('wrongform','sharedresource'), 15);
	echo '</center>';
} else {
	echo '<center>';
	
	//these two lines in comment can be used if you want to show the user values of saved fields
	/*echo '<h1>'.get_string('attributes','sharedresource').'</h1><br/>';
	echo $result['display'];*/
	
	if ($mode == 'add' && !$sharedresource_entry->add_instance()) {
		error(get_string('failadd','sharedresource'));
	} else if ($mode != 'add' && !$sharedresource_entry->update_instance()) {
		error(get_string('failupdate','sharedresource'));
	} else {
		// if everything was saved correctly, go back to the search page
		$fullurl = $CFG->wwwroot."/mod/sharedresource/search.php?id={$sharedresource_entry->id}&course={$course->id}&section={$section}&type={$type}&add={$add}&return={$return}&insertinpage={$insertinpage}";
		redirect($fullurl, get_string('correctsave','sharedresource'),5);
	}
	echo '</center>';
}

echo '</center>';

print_footer();
?>