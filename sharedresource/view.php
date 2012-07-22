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
    
    if ($identifier) {
        if (! $resource = get_record('sharedresource_entry', 'identifier', $identifier)) {
            sharedresource_not_found();
            //error('Resource Identifier was incorrect');
        }
    } else {
        if ($id) {
            if (! $cm = get_coursemodule_from_id('sharedresource', $id)) {
                sharedresource_not_found();
//                error('Course Module ID was incorrect');
            }
    
            if (! $resource = get_record('sharedresource', 'id', $cm->instance)) {
                sharedresource_not_found($cm->course);
//                error('Resource ID was incorrect');
            }
        } else {
            sharedresource_not_found();
//            error('No valid parameters!!');
        }
    
        if (! $course = get_record('course', 'id', $cm->course)) {
            print_error('badcourseid', 'sharedresource');
        }
    
        require_course_login($course, true, $cm);
        $cm_id = $cm->id;
    }
    require ($CFG->dirroot.'/mod/sharedresource/type/'.$resource->type.'/resource.class.php');
    $resourceclass = 'sharedresource_'.$resource->type;
    $resourceinstance = new $resourceclass($cm_id, $identifier);
    if ($inpopup) {
        $resourceinstance->inpopup();
    }
    
    $resourceinstance->display();

?>