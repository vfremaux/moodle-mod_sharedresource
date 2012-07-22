<?php  // $Id: admin_convertback.php,v 1.3 2010/04/30 21:59:32 vf Exp $

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
        $navlinks[] = array('name' => get_string('repositorytoresource', 'sharedresource'), 'link' => '', 'type' => 'title');
        $navigation = build_navigation($navlinks);
    } else {
        $navigation = build_navigation('');
    }
    print_header_simple('', '', $navigation, '', '', true, '', '');

    
    print_heading(get_string('resourceconversion', 'sharedresource'), '', 1);
    
    
    /// get courses
    if (empty($courseid)){
        $alllps = get_records_menu('course', 'format', 'learning', 'shortname', 'id,id');
        $form = new sharedresource_chooselp_form($alllps);
        $form->display();
    } else {
        $sharedresources = get_records('sharedresource', 'course', $courseid, 'name');

        if (empty($sharedresources)){
            notify(get_string('noresourcestoconvert', 'sharedresource'));
            print_continue($CFG->wwwroot."/course/view.php?id={$courseid}&amp;action=activities");
            print_footer();
            exit();
        }
        
        /// filter convertible resources : 
        // we only can convert back non shared resources
        foreach($sharedresources as $id => $sharedresource){
            if (count_records_select('sharedresource', " course <> {$courseid} AND identifier = '{$sharedresource->identifier}' ") != 0){
                unset($sharedresources[$id]);
            } else {
                $sharedresources[$id]->summary = $sharedresources[$id]->description;
            }
        }

        $form2 = new sharedresource_selectresources_form($course, $sharedresources);

        /// if data submitted, proceed
        if ($data = $form2->get_data()){
            if ($form2->is_cancelled()){
                print_string('conversioncancelled', 'sharedresource');
                redirect($CFG->wwwroot."/course/view.php?id={$courseid}&amp;action=activities");
            }
            $reskeys = preg_grep("/cnv_/" , array_keys(get_object_vars($data)));
            if (!empty($reskeys)){
                foreach($reskeys as $reskey){
                    // convert selected resources.
                    if ($data->$reskey == 1){
                        $resid = str_replace('cnv_', '', $reskey);
                        $sharedresource = get_record('sharedresource', 'id', $resid);
                        mtrace("converting resource {$sharedresource->id} : {$sharedresource->name}<br/>\n");
                        sharedresource_convertfrom($sharedresource);
                    }
                }
            }
        } else {
            // print form
            $form2->display();
        }
    }
    print_continue($CFG->wwwroot."/course/view.php?id=$courseid");
    print_footer();
?>