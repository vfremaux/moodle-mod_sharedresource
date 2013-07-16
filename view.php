<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/taoresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */

    require_once("../../config.php");
    require_once($CFG->dirroot.'/mod/sharedresource/lib.php');

    $id         = optional_param('id', 0, PARAM_INT);    // Course Module ID
    $identifier = optional_param('identifier', 0, PARAM_BASE64);    // SHA1 resource identifier
    $inpopup    = optional_param('inpopup', 0, PARAM_BOOL);

    $cm_id = 0;

    $system_context = context_system::instance();
    $strtitle = get_string('sharedresourcedetails', 'sharedresource');
    $PAGE->set_pagelayout('standard');
    $PAGE->set_context($system_context);
    $PAGE->set_title($strtitle);
    $PAGE->set_heading($SITE->fullname);
    /* SCANMSG: may be additional work required for $navigation variable */
    $PAGE->navbar->add($strtitle, 'view.php', 'misc');

    $PAGE->set_focuscontrol('');
    $PAGE->set_cacheable(false);
    $PAGE->set_button('');
    $PAGE->set_headingmenu('');

    $url = new moodle_url('/mod/sharedresource/view.php');
    $PAGE->set_url($url);
    // echo $OUTPUT->header(); // will be done by sharedresource::display();

    if ($identifier) {
        if (!$resource = $DB->get_record('sharedresource_entry', array('identifier' => $identifier))) {
            sharedresource_not_found();
            //error('Resource Identifier was incorrect');
        }
        $cmid = 0;
    } else {
        if ($id) {
            if (!$cm = get_coursemodule_from_id('sharedresource', $id)) {
                sharedresource_not_found();
                //error('Course Module ID was incorrect');
            }

            if (!$resource =  $DB->get_record('sharedresource', array('id'=> $cm->instance))) {
                sharedresource_not_found($cm->course);
                //error('Resource ID was incorrect');
            }
        } else {
            sharedresource_not_found();
            //error('No valid parameters!!');
        }

        if (!$course =  $DB->get_record('course', array('id'=> $cm->course))) {
            print_error('badcourseid', 'sharedresource');
        }

        require_course_login($course, true, $cm);
        $cmid = $cm->id;
    }

    /*
    require ($CFG->dirroot.'/mod/sharedresource/type/'.$resource->type.'/resource.class.php');

    $resourceclass = 'sharedresource_'.$resource->type;
    $resourceinstance = new $resourceclass($cmid, $identifier);
    */
    require_once ($CFG->dirroot.'/mod/sharedresource/sharedresource_base.class.php');
    $resourceinstance = new sharedresource_base($cmid, $identifier);

    if ($inpopup) {
     //   $resourceinstance->inpopup();
    }

    $resourceinstance->display();

    echo $OUTPUT->footer();
