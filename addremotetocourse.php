<?php

    /**
    * this action screen allows adding a sharedresource from an external search result
    * directly in the current course. This possibility will only be available when
    * external resource repositories are queried from a course starting context
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
    $identifier = optional_param('identifier', '', PARAM_TEXT);
    $mode = optional_param('mode', 'shared', PARAM_ALPHA);

    $course = get_record('course', 'id', "$courseid");
    
    if (empty($course)){
        error("Course invalid.");
    }

    $navlinks[] = array('name' => $course->shortname, 'link' => "/course/view.php?id={$course->id}", 'type' => 'url');
    $navlinks[] = array('name' => get_string('addremote', 'sharedresource'), 'link' => '', 'type' => 'title');
    $navigation = build_navigation($navlinks);

    print_header_simple('', '', $navigation, '', '', true, '', '');

    print_heading(get_string('addremote', 'sharedresource'));

    $url = required_param('url', PARAM_URL);
    $filename = required_param('file', PARAM_TEXT);

    // if we have a physical file to get, get it.
    if ($mode == 'file' || ($mode == 'local' && !empty($filename))){        
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // set it to pretty big files
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // set it to retrieve any content type
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // important
        curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
        if ($rawresponse = curl_exec($ch)){
                        
            $filename = preg_replace('/[0-9a-f]+-/i', '', basename($filename));  // removes the unique shacode
            $path = $CFG->dataroot.'/'.$course->id.'/'.$filename;
            $FILE = fopen($path, 'wb');
            fwrite($FILE, $rawresponse);
            fclose($FILE);
        }

        // if we are just getting the file, that's enough
        if ($mode == 'file'){
            redirect($CFG->wwwroot.'/files/index.php?id='.$course->id);
        }
    }

    if ($mode != 'file'){

        // The resource IS NOT known in the local repository but we may have the identifier and the provider
        // if identifier is empty the resource is submitted from an external search interface.
        // if not empty, the resource comes from another MNET shared repository
        $title = required_param('title', PARAM_TEXT);
        $desc = required_param('description', PARAM_TEXT);
        $provider = required_param('provider', PARAM_TEXT);
        $keywords = required_param('keywords', PARAM_TEXT);
        
        // make a sharedresource_entry
        $sharedresource_entry = new sharedresource_entry(false); 
        $sharedresource_entry->title = addslashes($title);
        $sharedresource_entry->description = addslashes($desc);
        $sharedresource_entry->keywords = $keywords;
        $sharedresource_entry->url = $url;
        $sharedresource_entry->sharedresourcefile = '';
        if (!empty($identifier)){
            $sharedresource_entry->identifier = $identifier;
        } else {
            $sharedresource_entry->identifier = sha1($url);
        }
        $sharedresource_entry->provider = $provider;
    
        if (!record_exists('sharedresource_entry', 'identifier', $sharedresource_entry->identifier)){
            $sharedresource_entry->add_instance();
        } else {
            if (!$sharedresource_entry = sharedresource_entry::read($identifier)){
                error("Ressource Identifier does not match any resource");
            }
        }
    
        // add a sharedresource
        $sharedresource = new sharedresource_base(0, $sharedresource_entry->identifier);
        $sharedresource->options = 0;
        $sharedresource->popup = 0;
        $sharedresource->type = 'file';
        $sharedresource->identifier = $sharedresource_entry->identifier;
        $sharedresource->name = addslashes($title);
        $sharedresource->course = $courseid;
        $sharedresource->description = addslashes($desc);
        $sharedresource->alltext = '';
        $sharedresource->timemodified = time();

        if ($mode == 'local'){
            // we make a standard resource from the sharedresource
            $resourceid = sharedresource_convertfrom($sharedresource, false);
            $modulename = 'resource';
            
            // if we have a physical file we have to bind it to the resource
            if (!empty($filename)){
                $resource = get_record('resource', 'id', $resourceid);
                $resource->reference = basename($filename);
                update_record('resource', $resource);
            }            
            
        } else {
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
            
    // finish
    print_continue($CFG->wwwroot."/course/view.php?id={$courseid}");
    
    print_footer($course);
    die;
?>