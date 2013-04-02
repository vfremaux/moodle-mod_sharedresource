<?php  // $Id: admin_convertall.php,v 1.1 2013-02-13 21:56:40 wa Exp $
    /**
    * this admin screen allows converting massively resources into sharedresources
    * indexable entries.
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
    $courseid = optional_param('course', SITEID, PARAM_INT);

	if ($courseid > SITEID){
	    if (!$course = $DB->get_record('course', array('id'=> "$courseid"))){
	    	print_error('coursemisconf');
	    }

	/// security 
	
	    $context = context_course::instance($courseid);
	    require_login($course);
	    require_capability('moodle/course:manageactivities', $context);
    	$PAGE->set_context($context);
	} else {
	    $systemcontext = context_system::instance();
	    require_login();
	    require_capability('mod/sharedresource:editcatalog', $systemcontext);
    	$PAGE->set_context($systemcontext);
	}

    $PAGE->set_title(get_string('resourceconversion', 'sharedresource'));
    $PAGE->set_heading(get_string('resourceconversion', 'sharedresource'));
    $PAGE->set_url('/mod/sharedresource/admin_convertall.php', array('course' => $courseid));

	// navigation
    $PAGE->navbar->add(get_string('resourceconversion', 'sharedresource'));
    $PAGE->navbar->add(get_string('resourcetorepository', 'sharedresource'));
	
/// get courses

    if (empty($courseid)){
    	// if no course choosen (comming from general sections) make the choice of one.
        $allcourses = $DB->get_records_menu('course', null, 'shortname', 'id,fullname');
        $form = new sharedresource_choosecourse_form($allcourses);
        if ($form->is_cancelled()){
            redirect($CFG->wwwroot.'/resources/index.php');
        }
    	echo $OUTPUT->header();  
        $form->display();
        echo $OUTPUT->footer();
        die;
    } else {
        // back to library if cancelled
        $form = new sharedresource_choosecourse_form(null);
        if ($form->is_cancelled()){
            redirect($CFG->wwwroot.'/resources/index.php');
        }
        $resources = $DB->get_records('resource', array('course' => $courseid), 'name');
        $urls = $DB->get_records('url', array('course' => $courseid),'name');
        if (empty($resources) && empty($urls)){
    		echo $OUTPUT->header();  
            echo $OUTPUT->notification(get_string('noresourcestoconvert', 'sharedresource'));
            echo $OUTPUT->continue_button($CFG->wwwroot."/course/view.php?id={$courseid}&amp;action=activities");
            print ($OUTPUT->footer());
            exit();
        }

        $form2 = new sharedresource_selectresources_form($course, $resources, $urls);

        /// if data submitted, proceed
        if ($data = $form2->get_data()){
            if ($form2->is_cancelled()){
                if ($courseid){
                    print_string('conversioncancelledtocourse', 'sharedresource');
                    redirect($CFG->wwwroot."/course/view.php?id={$courseid}&amp;action=activities");
                } else {
                    print_string('conversioncancelledtolibrary', 'sharedresource');
                    redirect($CFG->wwwroot."/resources/index.php");
                }
            }
            $reskeys = preg_grep("/rcnv_/" , array_keys(get_object_vars($data)));
            if (!empty($reskeys)){
                foreach($reskeys as $reskey){
                    // convert selected resources.
                    if ($data->$reskey == 1){
                        $resid = str_replace('rcnv_', '', $reskey);
                        $resource = $DB->get_record('resource', array('id' => $resid));
                        mtrace("converting resource {$resource->id} : {$resource->name}<br/>\n");
                        sharedresource_convertto($resource, 'resource');
                    }
                }
            }
            $reskeys = preg_grep("/ucnv_/" , array_keys(get_object_vars($data)));
            if (!empty($reskeys)){
                foreach($reskeys as $reskey){
                    // convert selected resources.
                    if ($data->$reskey == 1){
                        $resid = str_replace('ucnv_', '', $reskey);
                        $url = $DB->get_record('url', array('id' => $resid));
                        mtrace("converting url {$url->id} : {$url->name}<br/>\n");
                        sharedresource_convertto($url, 'url');
                    }
                }
            }
        } else {
            // print form
    		echo $OUTPUT->header();  
            $form2->display();
            if ($course){
             	print ($OUTPUT->footer($course));
            } else {
                print ($OUTPUT->footer());
            }
            die;
        }
    }

	echo $OUTPUT->header();  
    echo $OUTPUT->heading(get_string('resourceconversion', 'sharedresource'), 1);

    echo $OUTPUT->continue_button($CFG->wwwroot."/course/view.php?id=$courseid");
    print ($OUTPUT->footer());
?>