<?php  // $Id: admin_convertall.php,v 1.4 2010/04/30 21:59:32 vf Exp $

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

    $courseid = optional_param('course', '', PARAM_INT);
    $course = get_record('course', 'id', "$courseid");
    $context = get_context_instance(CONTEXT_COURSE, $courseid);

    require_capability('moodle/course:manageactivities', $context);
        
    if (!empty($courseid)){
        $navlinks[] = array('name' => $course->shortname, 'link' => "/course/view.php?id={$course->id}", 'type' => 'url');
        $navlinks[] = array('name' => get_string('resourceconversion', 'sharedresource'), 'link' => '', 'type' => 'title');
        $navlinks[] = array('name' => get_string('resourcetorepository', 'sharedresource'), 'link' => '', 'type' => 'title');
        $navigation = build_navigation($navlinks);
    } else {
        $navigation = build_navigation('');
    }
    print_header_simple('', '', $navigation, '', '', true, '', '');

    print_heading(get_string('resourceconversion', 'sharedresource'), '', 1);
    
    
    /// get courses
    if (empty($courseid)){
        $allcourses = get_records_menu('course', '', '', 'shortname', 'id,fullname');
        $form = new sharedresource_choosecourse_form($allcourses);

        if ($form->is_cancelled()){
            redirect($CFG->wwwroot.'/resources/index.php');
        }

        $form->display();
        print_footer();
        die;
    } else {

        // back to library if cancelled
        $form = new sharedresource_choosecourse_form(null);
        if ($form->is_cancelled()){
            redirect($CFG->wwwroot.'/resources/index.php');
        }

        $resources = get_records_select('resource', " type = 'file' AND course={$courseid} ", 'name');

        if (empty($resources)){
            notify(get_string('noresourcestoconvert', 'sharedresource'));
            print_continue($CFG->wwwroot."/course/view.php?id={$courseid}&amp;action=activities");
            print_footer();
            exit();
        }

        $form2 = new sharedresource_selectresources_form($course, $resources);

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
            $reskeys = preg_grep("/cnv_/" , array_keys(get_object_vars($data)));
            if (!empty($reskeys)){
                foreach($reskeys as $reskey){
                    // convert selected resources.
                    if ($data->$reskey == 1){
                        $resid = str_replace('cnv_', '', $reskey);
                        $resource = get_record('resource', 'id', $resid);
                        mtrace("converting resource {$resource->id} : {$resource->name}<br/>\n");
                        sharedresource_convertto($resource);
                    }
                }
            }
        } else {
            // print form
            $form2->display();
            if ($course){
                print_footer();
            } else {
                print_footer($course);
            }
            die;
        }
    }
    print_continue($CFG->wwwroot."/course/view.php?id=$courseid");
    print_footer();
?>