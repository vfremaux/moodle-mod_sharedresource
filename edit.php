<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package sharedresource
 *
 */
    require('../../config.php');
    require_once($CFG->dirroot.'/mod/sharedresource/sharedresource_entry_form.php');
    // require_once($CFG->dirroot.'/mod/sharedresource/sharedresource_entry_extra_form.php');
    require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
    require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
    require_once($CFG->libdir.'/filelib.php');

    // include "debugging.php";

    $ignore_list = array('mform_showadvanced_last', /* 'pagestep', */ 'MAX_FILE_SIZE', 'add', 'update', 'return', 'type', 'section', 'mode', 'course', 'submitbutton');
    $plugins = sharedresource_get_plugins();

    foreach($plugins as $plugin){
        $ignore_list = array_merge($ignore_list, $plugin->sharedresource_get_ignored());
    }

    $add           = optional_param('add', 0, PARAM_ALPHA);
    $update        = optional_param('update', 0, PARAM_INT);
    $return        = optional_param('return', 0, PARAM_BOOL); //return to course/view.php if false or mod/modname/view.php if true
    $type          = optional_param('type', '', PARAM_ALPHANUM);
    $section       = optional_param('section', 0, PARAM_INT);
    $mode          = required_param('mode', PARAM_ALPHA);
    $course        = required_param('course', PARAM_INT);
    // $pagestep      = optional_param('pagestep', 1, PARAM_INT);
    $sharingcontext  = optional_param('context', SITEID, PARAM_INT);

    if (!$course = $DB->get_record('course', array('id' => $course))) {
        print_error('coursemisconf');
    }

/// security         

    $system_context = context_system::instance();
    $context = context_course::instance($course->id);
	require_login($course);
    require_capability('moodle/course:manageactivities', $context);

/// page construction

    $strtitle = get_string($mode.'sharedresourcetypefile', 'sharedresource');
    $PAGE->set_pagelayout('standard');
    $PAGE->set_context($system_context);
    $PAGE->set_title($strtitle);
    $PAGE->set_heading($SITE->fullname);
    /* SCANMSG: may be additional work required for $navigation variable */
    $PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'), "{$CFG->wwwroot}/mod/sharedresource/index.php?id=$course->id");
    $PAGE->navbar->add(get_string($mode.'sharedresourcetypefile', 'sharedresource'));
    $PAGE->navbar->add($strtitle,'edit.php','misc');
    $PAGE->set_focuscontrol('');
    $PAGE->set_cacheable(false);
    $PAGE->set_button('');
    $PAGE->set_headingmenu('');
    $url = new moodle_url('/mod/sharedresource/edit.php');
    $PAGE->set_url($url);

    $pagetitle = strip_tags($course->shortname);

    // sort out how we should look depending on add or update
    if ($mode == 'update') {
        $entryid = required_param('entry_id', PARAM_INT);
        $sharedresource_entry = sharedresource_entry::get_by_id($entryid);
        $strpreview = get_string('preview', 'sharedresource');

        if (empty($CFG->sharedresource_foreignurl)){
            // ressouce preview is on the same server it is accessible. openpopup can be used
            $sharedresource_entry->url_display =  "<a href=\"{$CFG->wwwroot}/mod/sharedresource/view.php?identifier={$sharedresource_entry->identifier}&amp;inpopup=true\" "
              . "onclick=\"this.target='resource{$sharedresource_entry->id}'; return openpopup('/mod/sharedresource/view.php?inpopup=true&amp;identifier={$sharedresource_entry->identifier}', "
              . "'resource{$sharedresource_entry->id}','resizable=1,scrollbars=1,directories=1,location=0,menubar=0,toolbar=0,status=1,width=800,height=600');\">(".$strpreview.")</a>";
        } else {
            // resource preview changes apparent domain of the resource. openpopup fails
            $url = str_replace('<%%ID%%>', $sharedresource_entry->identifier, $CFG->sharedresource_foreignurl);
    //        $sharedresource_entry->url_display = "<a href=\"{$url}&amp;inpopup=true\" "
    //          . "onclick=\"this.target='resource{$sharedresource_entry->id}'; return openpopup('{$url}', "
    //          . "'resource{$sharedresource_entry->id}','resizable=1,scrollbars=1,directories=1,location=0,menubar=0,toolbar=0,status=1,width=800,height=600');\">(".$strpreview.")</a>";
            $sharedresource_entry->url_display = "<a href=\"{$url}\" target=\"_blank\">(".$strpreview.")</a>";
        }

        $sharedresource_entry->sharedresourcefile = $sharedresource_entry->file;
        $sharedresource_entry->filename = $DB->get_field('files', 'filename', array('id' => $sharedresource_entry->file));
    } else {
        $mode = 'add';
        $sharedresource_entry = new sharedresource_entry();
    }
    // which form phase are we in - step 1 or step 2
    $mform = false;
        $mform = new mod_sharedresource_entry_form($mode);
        $mform->set_data(($sharedresource_entry));        
    if ( $mform->is_cancelled() ){
        //cancel - go back to course
        redirect($CFG->wwwroot."/course/view.php?id={$course->id}");
    }

    // is this a successful POST ?

    if ($formdata = $mform->get_data()) {
        // check for hidden values
        if ($hidden = optional_param('sharedresource_hidden', '', PARAM_CLEANHTML)) {
            $hidden = explode('|', $hidden);
            foreach ($hidden as $field) {
                $formdata->$field = sharedresource_clean_field($field);
            }
        }

        // process the form contents
        // add form data to table object - skip the elements until we know what the identifier is
        foreach ($formdata as $key => $value) {
            if (in_array($key, $SHAREDRESOURCE_CORE_ELEMENTS) && !empty($value)) {
                if ($key == 'url') {
                    $sharedresource_entry->add_element($key, clean_param($value, PARAM_URL));
                } else {
                	if (is_array($value)){
	                    $sharedresource_entry->add_element($key, clean_param_array($value, PARAM_CLEANHTML));
                	} else {
	                    $sharedresource_entry->add_element($key, clean_param($value, PARAM_CLEANHTML));
	                }
                }
            }
        }

        $sharedresource_entry->lang = $USER->lang;
        if ($mode == 'add') {
            // locally defined resource ie. we are the master
            $sharedresource_entry->type = 'file';
            // page step 1
            // if ($pagestep == 1) {
                // is this a local resource or a remote one?
                if (!empty($sharedresource_entry->url)) {
                    $sharedresource_entry->identifier = sha1($sharedresource_entry->url);
                    $sharedresource_entry->mimetype = mimeinfo('type', $sharedresource_entry->url);
                } else {
                    // if resource uploaded then move to temp area until user has
                    //save the file 
                    $file  = $mform->save_stored_file('sharedresourcefile', SITEID, 'mod_sharedresource', 'sharedresource', 0, "/", null, true, $USER->id);
                    $sharedresource_entry->identifier = $file->get_contenthash();
                    $sharedresource_entry->file = $file->get_id();
                    $formdata->identifier = $sharedresource_entry->identifier;
                    $formdata->file = $sharedresource_entry->file;
                    $formdata->uploadname = $filename;// $sharedresource_entry->uploadname;
                    //$formdata->mimetype = $sharedresource_entry->mimetype;
                }
        }

		$sr_entry = serialize($sharedresource_entry);
		$SESSION->sr_entry = $sr_entry;
		$error = 'no error';
		$SESSION->error = $error;
		$plugin = $plugins[$CFG->{'pluginchoice'}];
		$nameplugin = $plugin->pluginname;
		$fullurl = $CFG->wwwroot."/mod/sharedresource/metadataform.php?course={$course->id}&section={$section}&type={$type}&add=sharedresource&return={$return}&mode={$mode}&context={$sharingcontext}&pluginchoice={$nameplugin}";
		redirect($fullurl);
    }

    // do we have hidden elements that we need to salvage
    if ($hidden = optional_param('sharedresource_hidden', '', PARAM_CLEANHTML)) {
        $hidden = explode('|', $hidden);
        foreach ($hidden as $field) {
            $value = sharedresource_clean_field($field);
            $mform->_form->addElement('hidden', $field, $value);
        }
        $mform->_form->addElement('hidden', 'sharedresource_hidden', join('|', $hidden));
    }
    echo '<center>';

    if ($mode == 'update') {
        //$mform->addElement('hidden', 'entry_id', $entry_id);
    }

    echo $OUTPUT->header();

    // display form
    $mform->display();
    echo '</center>';
    print($OUTPUT->footer($course));
    // page local functions
    // grab and clean form value

    function sharedresource_clean_field($field) {
        switch ($field) {
            case 'identifier' :
                $value = optional_param($field, '', PARAM_BASE64);
                break;
            case 'file' :
                $value = optional_param($field, '', PARAM_PATH);
                break;
            case 'uploadname' :
                $value = optional_param($field, '', PARAM_PATH);
                break;
            case 'mimetype' :
                $value = optional_param($field, '', PARAM_URL);
                break;
            default:
                $value = optional_param($field, '', PARAM_RAW);
                break;
        }
        return $value;
    }
?>