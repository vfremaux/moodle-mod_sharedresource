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
    require_once($CFG->dirroot.'/mod/sharedresource/sharedresource_entry_extra_form.php');
    require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
    require_once($CFG->libdir.'/filelib.php');
    // include "debugging.php";
    
    $ignore_list = array('mform_showadvanced_last', 'pagestep', 'MAX_FILE_SIZE', 'add', 'update', 'return', 'type', 'section', 'mode', 'course', 'submitbutton');
    
    require_login();
    
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
    $pagestep      = optional_param('pagestep', 1, PARAM_INT);
    $insertinpage  = optional_param('insertinpage', false, PARAM_INT);
    
    if (! $course = get_record('course', 'id', $course)) {
        error("This course doesn't exist");
    }
    require_login($course);
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
    require_capability('moodle/course:manageactivities', $context);
    
    $pagetitle = strip_tags($course->shortname);
    
    // sort out how we should look depending on add or update
    if ($mode == 'update') {
        $entry_id = required_param('entry_id', PARAM_INT);
        $sharedresource_entry = sharedresource_entry::get_by_id($entry_id);
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
    } else {
        $mode = 'add';
        $sharedresource_entry = new sharedresource_entry();
    }
    
    // which form phase are we in - step 1 or step 2
    $mform = false;
    if ($pagestep == 1) {
        $mform = new mod_sharedresource_entry_form($mode);
        $mform->set_data(stripslashes_recursive($sharedresource_entry));
        $mform->_form->addElement('hidden', 'insertinpage', $insertinpage);
    } else {
        $mform = new mod_sharedresource_entry_extra_form($mode);
        $mform->set_data($sharedresource_entry);
        $mform->_form->addElement('hidden', 'insertinpage', $insertinpage);
    }
    
    if ( $mform->is_cancelled() ){
        //cancel - go back to course
        redirect($CFG->wwwroot."/course/view.php?id={$course->id}");
    }
    
    // is this a successful POST ?
    if ($formdata = $mform->get_data()) {
        // check for hidden values
        if ($hidden = optional_param('sharedresource_hidden', '', PARAM_CLEAN)) {
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
                    $sharedresource_entry->add_element($key, stripslashes(clean_param($value, PARAM_CLEAN)));
                }
            }
        }
    
        $sharedresource_entry->lang = $USER->lang;
    
        if ($mode == 'add') {
            // locally defined resource ie. we are the master
            $sharedresource_entry->type = 'file';
    
            // page step 1
            if ($pagestep == 1) {
                // is this a local resource or a remote one?
                if (!empty($sharedresource_entry->url)) {
                    $sharedresource_entry->identifier = sha1($sharedresource_entry->url);
                    $sharedresource_entry->mimetype = mimeinfo('type', $sharedresource_entry->url);
    
                } else {
                    // if resource uploaded then move to temp area until user has
                    // completed metadata
                    if (!sharedresource_check_and_create_moddata_temp_dir()) {
                        error("Error - can't create resources temp dir");
                    }
                    $tempfile = $_FILES['sharedresourcefile']['tmp_name'];
                    $sharedresource_entry->uploadname = clean_param($_FILES['sharedresourcefile']['name'], PARAM_PATH);
                    $sharedresource_entry->mimetype = clean_param($_FILES['sharedresourcefile']['type'], PARAM_URL);
    
                    if (empty($tempfile) || !$hash = sharedresource_sha1file($tempfile)) {
                        error("Error - can't create hash of incoming resource file");
                    }
    
                    $sharedresource_entry->identifier = $hash;
                    $sharedresource_entry->file = $hash.'-'.$sharedresource_entry->uploadname;
                    $sharedresource_entry->tempfilename = $CFG->dataroot.SHAREDRESOURCE_TEMPPATH.$sharedresource_entry->file;
    
    
                    $formdata->identifier = $sharedresource_entry->identifier;
                    $formdata->file = $sharedresource_entry->file;
                    $formdata->uploadname = $sharedresource_entry->uploadname;
                    $formdata->mimetype = $sharedresource_entry->mimetype;
    
                    if (!sharedresource_copy_file($tempfile, $sharedresource_entry->tempfilename, true)) {
                        error("Error - can't copy upload file to temp");
                    }
                }
            } 
            // page step 2 - get it from the hidden fields
            else {
                // is this a local resource or a remote one?
                if (!empty($formdata->url)) {
                    $sharedresource_entry->url = $formdata->url;
                    $sharedresource_entry->identifier = sha1($sharedresource_entry->url);
                    $sharedresource_entry->mimetype = mimeinfo("type", $sharedresource_entry->url);
                } else {
                    // if these values are missing then we have a big problem - blowup appropriately
                    if (empty($formdata->uploadname) || empty($formdata->mimetype) || empty($formdata->identifier) || empty($formdata->file)) {
                        // die a horrible death
                        error("Error - transition hidden fields from step 1 missing in step 2");
                    }
                    $sharedresource_entry->uploadname = $formdata->uploadname;
                    $sharedresource_entry->mimetype = $formdata->mimetype;
                    $sharedresource_entry->identifier = $formdata->identifier;
                    $sharedresource_entry->file = $formdata->file;
                    $sharedresource_entry->tempfilename = $CFG->dataroot.SHAREDRESOURCE_TEMPPATH.$sharedresource_entry->file;
                }
            }
        }
    
        // common update or add tasks
        // now that we know what the identifier will be - add the elements
		//useless
        /*foreach ($formdata as $key => $value) {
            if (!in_array($key, $ignore_list) && !empty($value)) {
                $sharedresource_entry->update_element($key, clean_param($value, PARAM_CLEAN));
            }
        }*/

        // if we need to do step 2 - defer the add/update, and load up the extra form
        // hide all values retrieved so far in hidden
        // put the file into temp for later
        if ($pagestep == 1 && sharedresource_extra_resource_screen()) {
            // setup the new form, and hide away all the values we have so far
            $mform = new mod_sharedresource_entry_extra_form($mode);
            $mform->set_data($sharedresource_entry);
            $hidden = array();
            foreach ($formdata as $key => $value) {
                if (!in_array($key, $ignore_list) && !empty($value)) {
                    $value = clean_param($value, PARAM_CLEAN);
                    $mform->_form->addElement('hidden', $key, $value);
                    $hidden[] = $key;
                }
            }
            $mform->_form->addElement('hidden', 'sharedresource_hidden', join('|', $hidden));
        } else {
			$sr_entry = serialize($sharedresource_entry);
			$SESSION->sr_entry = $sr_entry;
			$error = 'no error';
			$SESSION -> error = $error;
			$plugins = sharedresource_get_plugins();
			$plugin = $plugins[$CFG->{'pluginchoice'}];
			$nameplugin = $plugin->pluginname;
			$fullurl = $CFG->wwwroot."/mod/sharedresource/metadataform.php?course={$course->id}&section={$section}&type={$type}&add=sharedresource&return={$return}&mode={$mode}&insertinpage={$insertinpage}&pluginchoice={$nameplugin}";
			// DEBUG
			redirect($fullurl);
			// print_object($sharedresource_entry);
			// print_continue($fullurl);
        }
    }
    
    // do we have hidden elements that we need to salvage
    if ($hidden = optional_param('sharedresource_hidden', '', PARAM_CLEAN)) {
        $hidden = explode('|', $hidden);
        foreach ($hidden as $field) {
            $value = sharedresource_clean_field($field);
            $mform->_form->addElement('hidden', $field, $value);
        }
        $mform->_form->addElement('hidden', 'sharedresource_hidden', join('|', $hidden));
    }
    
    // build up navigation links
    $navlinks = array();
    $navlinks[] = array('name' => get_string('modulenameplural', 'sharedresource'), 'link' => "{$CFG->wwwroot}/mod/sharedresource/index.php?id=$course->id", 'type' => 'activity');
    $navlinks[] = array('name' => get_string($mode.'sharedresourcetypefile', 'sharedresource'), 'link' => '', 'type' => 'title');
    $navigation = build_navigation($navlinks);

    print_header_simple($pagetitle, '', $navigation, "", "", false);
    print_heading_with_help(get_string($mode.'sharedresourcetypefile', 'sharedresource'), 'addsharedresource', 'sharedresource');
    
    echo '<center>';
    //// ensure static fields are not wrapped
    //echo "<style>.fstatic { white-space: nowrap; }</style>";
    
    //$mform->_form->_attributes['action'] = $CFG->wwwroot.'/mod/sharedresource/edit.php?course='.$course->id.'&add='.$add.'&return='.$return.'&type='.$type.'&section='.$section.'&mode='.$mode;
    
    $mform->_form->addElement('hidden', 'course', $course->id);
    $mform->_form->addElement('hidden', 'add', $add);
    $mform->_form->addElement('hidden', 'return', $return);
    $mform->_form->addElement('hidden', 'type', $type);
    $mform->_form->addElement('hidden', 'section', $section);
    $mform->_form->addElement('hidden', 'mode', $mode);
    if ($mode == 'update') {
        $mform->_form->addElement('hidden', 'entry_id', $entry_id);
    }
    
    
    // display whichever form
    $mform->display();

    echo '</center>';
    print_footer($course);
    
    
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