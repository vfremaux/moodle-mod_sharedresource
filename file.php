<?php 
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */

      // remove dependence on course id

    require_once('../../config.php');
    require_once('../../lib/filelib.php');
    require_once('lib.php');
    
    
    if (!isset($CFG->filelifetime)) {
        $lifetime = 86400;     // Seconds for files to remain in caches
    } else {
        $lifetime = $CFG->filelifetime;
    }

    // disable moodle specific debug messages
    disable_debugging();

    $relativepath = get_file_argument('file.php');
    $forcedownload = optional_param('forcedownload', 0, PARAM_BOOL);
    
    // relative path must start with '/', because of backup/restore!!!
    if (!$relativepath) {
        error('No valid arguments supplied or incorrect server configuration');
    } else if ($relativepath{0} != '/') {
        error('No valid arguments supplied, path does not start with slash!');
    }

    $pathname = $CFG->dataroot.$relativepath;

    // extract relative path components
    $args = explode('/', trim($relativepath, '/'));
    if (count($args) == 0) { // always at least courseid, may search for index.html in course root
        error('No valid arguments supplied');
    }
  
    // might pass in the real course id
    $id = SITEID;
    // security: limit access to existing course subdirectories
    if (!$course = get_record_sql("SELECT * FROM {$CFG->prefix}course WHERE id='".(int)$id."'")) {
        error('Invalid course ID');
    }

    if ($course->id != SITEID) {
        require_login($course->id, true, null, false);
    } else if ($CFG->forcelogin) {
        if (!empty($CFG->sitepolicy)
            and ($CFG->sitepolicy == $CFG->wwwroot.'/file.php'.$relativepath
                 or $CFG->sitepolicy == $CFG->wwwroot.'/file.php?file='.$relativepath)) {
            //do not require login for policy file
        } else {
            require_login(0, true, null, false);
        }
    }

    if (is_dir($pathname)) {
        if (file_exists($pathname.'/index.html')) {
            $pathname = rtrim($pathname, '/').'/index.html';
            $args[] = 'index.html';
        } else if (file_exists($pathname.'/index.htm')) {
            $pathname = rtrim($pathname, '/').'/index.htm';
            $args[] = 'index.htm';
        } else if (file_exists($pathname.'/Default.htm')) {
            $pathname = rtrim($pathname, '/').'/Default.htm';
            $args[] = 'Default.htm';
        } else {
            // security: do not return directory node!
            sharedresource_not_found($course->id);
        }
    }

    // security: teachers can view all assignments, students only their own
    if ((count($args) >= 3)
        and (strtolower($args[1]) == 'moddata')
        and (strtolower($args[2]) == 'assignment')) {

        $lifetime = 0;  // do not cache assignments, students may reupload them
        if ($args[4] == $USER->id) {
            //can view own assignemnt submissions
        } else {
            $instance = (int)$args[3];
            if (!$cm = get_coursemodule_from_instance('assignment', $instance, $course->id)) {
                sharedresource_not_found($course->id);
            }
            if (!has_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
                error('Access not allowed');
            }
        } 
    }

    // check that file exists
    if (!file_exists($pathname)) {
        sharedresource_not_found($course->id);
    }

    // ========================================
    // finally send the file
    // ========================================
    session_write_close(); // unlock session during fileserving
    $filename = $args[count($args)-1];
    send_file($pathname, $filename, $lifetime, $CFG->filteruploadedfiles, false, $forcedownload);
?>
