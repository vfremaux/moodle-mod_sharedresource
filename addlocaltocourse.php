<?php
    /**
    * this action screen allows adding a sharedresource from an external browse or search result
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
    require_once($CFG->dirroot . '/course/lib.php');

    $courseid = optional_param('id', '', PARAM_INT);
    $section = optional_param('section', '', PARAM_INT);
    $identifier = required_param('identifier', PARAM_TEXT);
    $mode = optional_param('mode', 'shared', PARAM_ALPHA);
    $course = $DB->get_record('course', array('id' => "$courseid"));

    if (empty($course)){
        print_error('coursemisconf');
    }

    $context = context_course::instance($course->id);
    $strtitle = get_string('addlocal', 'sharedresource');

    $url = new moodle_url('/mod/sharedresource/addlocaltocourse.php',array('id' => $courseid, 'identifier' => $identifier, 'mode' => $mode));
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_context($context);
    $PAGE->set_title($strtitle);
    $PAGE->set_heading($SITE->fullname);

    /* navigation */
    $PAGE->navbar->add($strtitle,'addlocaltocourse.php', 'misc');

    $PAGE->set_focuscontrol('');
    $PAGE->set_cacheable(false);
    $PAGE->set_button('');
    $PAGE->set_headingmenu('');

    $sharedresource_entry = sharedresource_entry::read($identifier);

    if ($mode == 'file'){
	    echo $OUTPUT->header();
	    echo $OUTPUT->heading(get_string('add'.$mode, 'sharedresource'));
        // this is the simple "file" mode that gets back the resource file into course file scope
        print_string('fileadvice', 'sharedresource');
        $return = $CFG->wwwroot."/files/index.php?id={$courseid}";
	    echo $OUTPUT->continue_button($return);
	    echo $OUTPUT->footer($course);
	    die;
    }


	// the sharedresource has been recognized as a deployable backup. 
	// take the physical file and deploy it with the activity publisher utiliy
	if ($mode == 'deploy'){		
		require_capability('moodle/course:manageactivities', $context);
		
		if (file_exists($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php')){
			include_once($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php');

			$fs = get_file_storage();
			
			$sharedresource_entry = $DB->get_record('sharedresource_entry', array('identifier' => required_param('identifier', PARAM_TEXT)));

		    $file = $fs->get_file_by_id($sharedresource_entry->file);
		    activity_publisher::restore_single_module($courseid, $file);
		    
		    // TODO : Terminate procedure and return to course silently
		    redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
		    die;
		}
		
		// no one should be here....		
	}

    if ($mode == 'ltiinstall'){
    	
    	// we build an LTI Tool instance.
    	require_once $CFG->dirroot.'/mod/sharedresource/lti_mod_form.php';	        	
    	require_once $CFG->dirroot.'/mod/lti/lib.php';
    	
    	$instance = new StdClass;
    	$instance->name = $sharedresource_entry->title;
		$instance->intro = $sharedresource_entry->description;
		$instance->introformat = FORMAT_MOODLE;
		$time = time();
		$instance->timecreated = $time;
		$instance->timemodified = $time;
		$instance->typeid = 0;
		if (preg_match('#^https://#', $sharedresource_entry->url)){
			$instance->toolurl = ''; 
			$instance->securetoolurl = $sharedresource_entry->url;
		} else {
			$instance->toolurl = $sharedresource_entry->url; 
			$instance->securetoolurl = '';
		}
		$instance->instructorchoicesendname = 1; // default lti form value
		$instance->instructorchoicesendemailaddr = 1;
		$instance->instructorchoiceallowroster = 1;
		$instance->instructorchoiceallowsetting = 1;
		$instance->instructorcustomparameters = '';
		$instance->instructorchoiceacceptgrades = 1;
		$instance->grade = 0;
		$instance->launchcontainer = LTI_LAUNCH_CONTAINER_DEFAULT;
		$instance->resourcekey = ''; // client identification key for remote service
		$instance->password = ''; // server password for accessing the service
		$instance->debuglaunch = 0;
		$instance->showtitlelaunch = 0;
		$instance->showdescriptionlaunch = 0;
		$instance->servicesalt = ''; // Unique salt autocalculated
		$instance->icon = '';
		$instance->secureicon = '';

    	$mform = new lti_mod_form();
    	if ($mform->is_cancelled()){
    		redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
    	}
    	if ($data = $mform->get_data()){
		    echo $OUTPUT->header();
		    echo $OUTPUT->heading(get_string('add'.$mode, 'sharedresource'));

    		$intancearr = (array)$instance;
    		$data->intro = $data->introeditor['text'];
    		$data->introformat = $data->introeditor['format'];
    		// report changes from form
    		foreach(array_keys($intancearr) as $key){
    			if (isset($data->$key)){
	    			$instance->$key = $data->$key;
	    		}
    		}
			$instance->course = $courseid;
        	$instance->id = lti_add_instance($instance, null);
        } else {
		    echo $OUTPUT->header();
		    echo $OUTPUT->heading(get_string('add'.$mode, 'sharedresource'));
        	$instance->identifier = $identifier;
        	$instance->mode = $mode;
        	$instance->id = $courseid;
        	$instance->section = $section;
    		$mform->set_data($instance);
        	$mform->display();
        	echo $OUTPUT->footer();
        	die;
        }

        $modulename = 'lti';
    } else {

        // elsewhere add a sharedresource instance
        // make a shared resource on the sharedresource_entry
        $instance = new sharedresource_base(0, $sharedresource_entry->identifier);
        $instance->options = 0;
        $instance->popup = 0;
        $instance->type = 'file';
        $instance->identifier = $sharedresource_entry->identifier;
        $instance->name = $sharedresource_entry->title;
        $instance->course = $courseid;
        $instance->description = $sharedresource_entry->description;
        $instance->alltext = '';
        $instance->timemodified = time();

        if (!$instance->id = $instance->add_instance($instance)){
            print_error('erroraddinstance', 'sharedresource');
        }
        
        $modulename = 'sharedresource';
    }

    // make a new course module
    $module = $DB->get_record('modules', array('name'=> $modulename));
    $cm = new StdClass;
    $cm->instance = $instance->id;
    $cm->module = $module->id;
    $cm->course = $courseid;
    $cm->section = 1;

    /// remoteid may be obtained by $sharedresource_entry->add_instance() plugin hooking !! ;
    // valid also if LTI tool
    if (!empty($sharedresource_entry->remoteid)){
        $cm->idnumber = $sharedresource_entry->remoteid;
    }

    // insert the course module in course
    if (!$cm->id = add_course_module($cm)){
        print_error('errorcmaddition', 'sharedresource');
    }
    
    // reset the course modinfo cache
    $course->modinfo = null;
    $DB->update_record('course', $course);

    if (!$section){
    	// when we add directly from library without course action
        $section = sharedresource_get_course_section_to_add($COURSE);
    }

    if (!$sectionid = course_add_cm_to_section($course, $cm->id, $section)){
        print_error('errorsectionaddition', 'sharedresource');
    }
    
    // echo "added cm $cm->id in section $sectionid";

    if (!$DB->set_field('course_modules', 'section', $sectionid, array('id' => $cm->id))) {
        print_error('errorcmsectionbinding', 'sharedresource');
    }

	// finally if localization was asked, transform the sharedresource in real resource.
	if ($mode == 'local'){
        // we make a standard resource from the sharedresource
        $instance->id = sharedresource_convertfrom($instance);
    }
    
/// finish
 

    // TODO : Terminate procedure and return to course silently
    redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);

    // $return = $CFG->wwwroot."/course/view.php?id={$courseid}";
    // echo $OUTPUT->continue_button($return);
    // echo $OUTPUT->footer($course);
    die;
