<?php
/**
 *
 * @author  Fr�d�ric GUILLOU
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 * This php script displays the
 * metadata form
 */

	require_once("../../config.php");
	require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
	require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
	require_once($CFG->dirroot.'/mod/sharedresource/classificationlib.php');

	//require_js('yui_yahoo');
	//require_js('yui_dom');
	//require_js('yui_utilities');
	//require_js('yui_connection');
	$PAGE->requires->js('/mod/sharedresource/js/metadata_yui.php', true);

	$system_context = context_system::instance();
	$strtitle = get_string("metadatanotice",'sharedresource');
	$PAGE->set_pagelayout('popup');
	$PAGE->set_context($system_context);
	$PAGE->set_title($strtitle);
	$PAGE->set_heading($SITE->fullname);
	/* SCANMSG: may be additional work required for $navigation variable */
	$PAGE->navbar->add(get_string('sharedresourcenotice', 'sharedresource'));
	$PAGE->set_focuscontrol('');
	$PAGE->set_cacheable(false);
	$PAGE->set_button('');
	$PAGE->set_headingmenu('');
	$url = new moodle_url('/mod/sharedresource/metadatanotice.php');
	$PAGE->set_url($url);

	echo $OUTPUT->header();

	$id = optional_param('id', 0, PARAM_INT);
	$identifier = optional_param('identifier', 0, PARAM_TEXT);
	if ($identifier) {
	    if (!$sharedresource_entry =  $DB->get_record('sharedresource_entry', array('identifier'=> $identifier))) {
	        sharedresource_not_found();
	        //error('Resource Identifier was incorrect');
	    }
	} else {
	    if ($id) {
	        if (! $cm = get_coursemodule_from_id('sharedresource', $id)) {
	            sharedresource_not_found();
	//                error('Course Module ID was incorrect');
	        }
	        if (!$resource =  $DB->get_record('sharedresource', array('id'=> $cm->instance))) {
	            sharedresource_not_found($cm->course);
	//                error('Resource ID was incorrect');
	        }
	        if (!$sharedresource_entry_rec =  $DB->get_record('sharedresource_entry', array('identifier'=> $resource->identifier))){
	            sharedresource_not_found($cm->course);
	        }
		    if (!$course =  $DB->get_record('course',array('id'=> $cm->course))) {
		        print_error('badcourseid', 'sharedresource');
		    }
	    } else {
	        sharedresource_not_found();
	//            error('No valid parameters!!');
	    }
	}

	$sharedresource_entry = sharedresource_entry::read($sharedresource_entry->identifier);
	$pluginchoice = optional_param('pluginchoice', $CFG->pluginchoice, PARAM_ALPHA);
	$pagetitle = strip_tags($SITE->fullname);
	// build up navigation links

	echo $OUTPUT->heading(get_string('sharedresourcenotice', 'sharedresource', format_text($sharedresource_entry->title)));
	
	if(has_capability('mod/sharedresource:systemmetadata', context_system::instance())){
		$capability = 'system';
	} else {
		$capability = 'indexer';
	}

	require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$pluginchoice.'/plugin.class.php');
	$object = 'sharedresource_plugin_'.$pluginchoice;
	$mtdstandard = new $object;
	$nbrmenu = count($mtdstandard->METADATATREE[0]['childs']);
	echo '<center>';
	echo '<div id="ecform_container" align="center">';
	echo '<div align="center" id="ecform_title">'.get_string('metadatadescr','sharedresource').' ('.$mtdstandard->pluginname.')</div><br/>';
	echo '<div id="ecform_onglet" class="ecformtab">';
	echo '<ul id="notice-menu" class="tabrow0">';
	echo '<li class="first onerow here selected" style="float: none;float: left;display: inline;">';
	echo '<a id="_0" class="current" onclick="multiMenu(this.id,'.$nbrmenu.')" alt="menu0"><span>'.get_string('DMused','sharedresource').'</span></a>';
	echo '</li>';
	echo metadara_create_tab($capability, $mtdstandard);
	echo '</ul>';
	echo '</div><br/>';
	echo '<div id="ecform_content" style="margin-right: auto; margin-left: auto">';
	echo '<div id="tab_0" class="on content">';
	echo '<h2>'.get_string('DMuse','sharedresource').' '.$mtdstandard->pluginname.'</h2>';
	echo '<h3>'.get_string('DMdescription','sharedresource').' '.$mtdstandard->pluginname.'</h3>';
	echo '<fieldset style="width:90%;margin-right: auto; margin-left: auto">';
	echo '<div style="text-align:justify;align=left;">';
	echo get_string('description'.$mtdstandard->pluginname,'sharedresource');
	echo '</div>';
	echo '</fieldset>';
	echo '</div>';
	echo '</div>';
	echo metadata_create_notice_panels($sharedresource_entry, $capability, $mtdstandard);
	echo '</div><br/>';
	echo '<div align="center">';
	echo '</div>';
	echo '</div>';
	echo '</center>';

	echo '<script type="text/javascript">'."\n";
	echo "// select the general tab by default\n";
	echo 'multiMenu(\'_1\','.count($mtdstandard->METADATATREE[0]['childs']).")\n";
	echo '</script>';
	
	echo $OUTPUT->footer();
