<?php

    /**
    * this actgion screen allows adding a sharedresource from an external browse or search result
    * directly in the current course and the resource results being already known as a local proxy, or
    * it is a locally stored resource.
    * This possibility will only be available when
    * external resource repositories are queried from a course starting context.
    * Adding local resource should always provide identifier.
    *
    * @package    mod-sharedresource
    * @category   mod
    * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
    * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
    * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
    */

    require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
    require_once($CFG->libdir . '/adminlib.php');
    require_once($CFG->dirroot . '/mod/sharedresource/lib.php');
    require_once($CFG->dirroot . '/mod/sharedresource/locallib.php');
    require_once($CFG->dirroot . '/mod/sharedresource/admin_convert_form.php');
    
    // admin_externalpage_setup('sharedresource_convertall');
    // admin_externalpage_print_header();

    $courseid = optional_param('id', '', PARAM_INT);
    $identifier = required_param('identifier', PARAM_TEXT);
    $mode = optional_param('mode', 'shared', PARAM_ALPHA);

    $course = get_record('course', 'id', "$courseid");
    $context = get_context_instance(CONTEXT_COURSE, $courseid);
    
    if (empty($course)){
        error("Course invalid.");
    }

    // security
	require_login($course);    

    $navlinks[] = array('name' => $course->shortname, 'link' => "/course/view.php?id={$course->id}", 'type' => 'url');
    $navlinks[] = array('name' => get_string('addremote', 'sharedresource'), 'link' => '', 'type' => 'title');
    $navigation = build_navigation($navlinks);

    print_header_simple('', '', $navigation, '', '', true, '', '');

    print_heading(get_string('add'.$mode, 'sharedresource'));

    $sharedresource_entry = sharedresource_entry::read($identifier);

    if ($mode != 'file'){
    	
    	if ($mode == 'deploy'){
    		
    		require_capability('moodle:course:manageactivities', $context);
    		
    		// take the physical file and deploy it witht the activity publisher utiliy
    		if (file_exists($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php')){
    			include_once($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php');

				$ap = new activity_publisher();
    			notify(get_string('deploying', 'block_activity_publisher'));
			    $file = $CFG->dataroot.'/sharedresources/'.required_param('file', PARAM_TEXT);
			    echo " candidate : ".required_param('identifier', PARAM_TEXT);
			    $ap::restore_single_module($courseid, $file);
    		}    		
    	} else {
    		
	        // add a sharedresource
	        // make a shared resource on the sharedresource_entry
    		require_capability('moodle:course:manageactivities', $context);
	            
	        $sharedresource = new sharedresource_base(0, $sharedresource_entry->identifier);
	        $sharedresource->options = 0;
	        $sharedresource->popup = 0;
	        $sharedresource->type = 'file';
	        $sharedresource->identifier = $sharedresource_entry->identifier;
	        $sharedresource->name = addslashes($sharedresource_entry->title);
	        $sharedresource->course = $courseid;
	        $sharedresource->description = addslashes($sharedresource_entry->description);
	        $sharedresource->alltext = '';
	        $sharedresource->timemodified = time();
	    
	        if ($mode == 'local'){
	            // we make a standard resource from the sharedresource
	            $resourceid = sharedresource_convertfrom($sharedresource, false);
	            $modulename = 'resource';
	        } elseif ($mode == 'shared') {
	            if (!$resourceid = $sharedresource->add_instance($sharedresource)){
	                error("sharedresource instance creation error");
	            }
	            $modulename = 'sharedresource';
	        }
	    
	        // make a new course module
	        
	        $module = get_record('modules', 'name', $modulename);
	        $cm->instance = $resourceid;
	        $cm->module = $module->id;
	        $cm->course = $courseid;
	        $cm->section = 1;
	    
	        /// remoteid may be obtained by $sharedresource_entry->add_instance() plugin hooking !! ;
	        if (!empty($sharedresource_entry->remoteid)){
	            $cm->idnumber = $sharedresource_entry->remoteid;
	        }
	    
	        // insert the course module in course
	        if (!$cm->coursemodule = add_course_module($cm)){
	            error('Could not add the course module');
	        }
	    
	        if (!$sectionid = add_mod_to_section($cm)){
	            error('Could not setup a section');
	        }
	    
	        if (! set_field('course_modules', 'section', $sectionid, 'id', $cm->coursemodule)) {
	            error("Could not update the course module with the correct section");
	        }
	    }
    } else {
        
        // this is the simple "file" mode that gets back the resource file into course file scope
        
        // just copy the file to course file folder...
		require_capability('moodle/course:managefiles', $context);

        if (!empty($sharedresource_entry->file)){
            $source = $CFG->dataroot.'/sharedresources/'.$sharedresource_entry->file;
            // filters out md5 identifer and replace with simple timed stamp
            $destname = preg_replace("/^[0-9abcdef]+-/", time().'_', $sharedresource_entry->file);
            if (!is_dir($CFG->dataroot.'/'.$courseid)){
                mkdir($CFG->dataroot.'/'.$courseid);
            }
            $dest = $CFG->dataroot.'/'.$courseid.'/'.$destname;
            copy($source, $dest);
        }
        
        print_string('fileadvice', 'sharedresource');
    }
        
    // finish

    if ($mode != 'file'){
        $return = $CFG->wwwroot."/course/view.php?id={$courseid}";
    } else {
        $return = $CFG->wwwroot."/files/index.php?id={$courseid}";
    }
    
    print_continue($return);
    // redirect($return);
    
    print_footer($course);
    die;
?>