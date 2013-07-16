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

    $courseid = optional_param('id', '', PARAM_INT);
    $section = optional_param('section', '', PARAM_INT);
    $identifier = required_param('identifier', PARAM_TEXT);
    $mode = optional_param('mode', 'shared', PARAM_ALPHA);
    $course = $DB->get_record('course', array('id' => "$courseid"));

    if (empty($course)){
        pinrt_error('coursemisconf');
    }

    $course_context = context_course::instance($course->id);
    $strtitle = get_string('addlocal', 'sharedresource');

    $url = new moodle_url('/mod/sharedresource/addlocaltocourse.php',array('id' => $courseid, 'identifier' => $identifier, 'mode' => $mode));
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_context($course_context);
    $PAGE->set_title($strtitle);
    $PAGE->set_heading($SITE->fullname);

    /* navigation */
    $PAGE->navbar->add($strtitle,'addlocaltocourse.php', 'misc');

    $PAGE->set_focuscontrol('');
    $PAGE->set_cacheable(false);
    $PAGE->set_button('');
    $PAGE->set_headingmenu('');

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('add'.$mode, 'sharedresource'));

    $sharedresource_entry = sharedresource_entry::read($identifier);

    if ($mode != 'file'){
        // add a sharedresource
        // make a shared resource on the sharedresource_entry
        $sharedresource = new sharedresource_base(0, $sharedresource_entry->identifier);
        $sharedresource->options = 0;
        $sharedresource->popup = 0;
        $sharedresource->type = 'file';
        $sharedresource->identifier = $sharedresource_entry->identifier;
        $sharedresource->name = $sharedresource_entry->title;
        $sharedresource->course = $courseid;
        $sharedresource->description = $sharedresource_entry->description;
        $sharedresource->alltext = '';
        $sharedresource->timemodified = time();

        if ($mode == 'local'){
            // we make a standard resource from the sharedresource
            $resourceid = sharedresource_convertfrom($sharedresource, false);
            $modulename = 'resource';
        } elseif ($mode == 'shared') {
            if (!$resourceid = $sharedresource->add_instance($sharedresource)){
                print_error('erroraddinstance', 'sharedresource');
            }
            $modulename = 'sharedresource';
        }

        // make a new course module
        $module = $DB->get_record('modules', array('name'=> $modulename));
        $cm = new StdClass;
        $cm->instance = $resourceid;
        $cm->module = $module->id;
        $cm->course = $courseid;
        $cm->section = 1;

        /// remoteid may be obtained by $sharedresource_entry->add_instance() plugin hooking !! ;
        if (!empty($sharedresource_entry->remoteid)){
            $cm->idnumber = $sharedresource_entry->remoteid;
        }

        // insert the course module in course
        if (!$cm->coursemoduleid = add_course_module($cm)){
            print_error('errorcmaddition', 'sharedresource');
        }

        // reset the course modinfo cache
        $course->modinfo = null;
        $DB->update_record('course', $course);

        if (!$section){
        	// when we add directly from library without course action
	        $section = sharedresource_get_course_section_to_add($COURSE);
	    }

        if (!$sectionid = course_add_cm_to_section($course, $cm->coursemoduleid, $section)){
            print_error('errorsectionaddition', 'sharedresource');
        }

        echo "added cm $cm->id in section $sectionid for $lastsection ";

        if (!$DB->set_field('course_modules', 'section', $sectionid, array('id' => $cm->coursemoduleid))) {
            print_error('errorcmsectionbinding', 'sharedresource');
        }

    } else {
        // this is the simple "file" mode that gets back the resource file into course file scope
        print_string('fileadvice', 'sharedresource');
    }

    // finish
    if ($mode != 'file'){
        $return = $CFG->wwwroot."/course/view.php?id={$courseid}";
    } else {
        $return = $CFG->wwwroot."/files/index.php?id={$courseid}";
    }

    echo $OUTPUT->continue_button($return);
    echo $OUTPUT->footer($course);
    die;
?>
