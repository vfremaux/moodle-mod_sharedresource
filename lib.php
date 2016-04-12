<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */

define('SHAREDRESOURCE_LOCALPATH', 'LOCALPATH');
define('SHAREDRESOURCE_TEMPPATH', '/temp/sharedresources/');
define('SHAREDRESOURCE_RESOURCEPATH', '/sharedresources/');
define('SHAREDRESOURCE_SEARCH_LIMIT', '200');
define('SHAREDRESOURCE_RESULTS_PER_PAGE', '20');


global $SHAREDRESOURCE_WINDOW_OPTIONS;
global $SHAREDRESOURCE_CORE_ELEMENTS;
global $SHAREDRESOURCE_METADATA_ELEMENTS; // must be global because it might be included from a function!

$SHAREDRESOURCE_WINDOW_OPTIONS = array('resizable', 
                                    'scrollbars', 
                                    'directories', 
                                    'location',
                                    'menubar', 
                                    'toolbar', 
                                    'status', 
                                    'width', 
                                    'height');

$SHAREDRESOURCE_CORE_ELEMENTS = array('id', 
                                    'identifier', 
                                    'title', 
                                    'description', 
                                    'url', 
                                    'file', 
                                    'type', 
                                    'remoteid', 
                                    'mimetype',
                                    'timemodified');

$SHAREDRESOURCE_METADATA_ELEMENTS = array(array('name' => 'Contributor',
                                                'datatype' => 'text'),
                                          array('name' => 'IssueDate',
                                                'datatype' => 'lomdate'),
                                          array('name' => 'TypicalAgeRange',
                                                'datatype' => 'lomagerange'),
                                          array('name' => 'LearningResourceType',
                                                'datatype' => 'vocab'),
                                          array('name' => 'Rights',
                                                'datatype' => 'yesno'),
                                          array('name' => 'Format',
                                                'datatype' => 'text'),
                                          array('name' => 'RightsDescription',
                                                'datatype' => 'plaintext'),
                                          array('name' => 'ClassificationPurpose',
                                                'datatype' => 'plaintext'),
                                          array('name' => 'ClassificationTaxonPath',
                                                'datatype' => 'vocab'));

require_once('sharedresource_base.class.php');
require_once('sharedresource_plugin_base.class.php');
require_once('sharedresource_entry.class.php');
require_once('sharedresource_metadata.class.php');

/**
 * List of features supported in Resource module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function sharedresource_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}


/**
* Find active plugins, load the class files, and instantiate
* the appropriate plugin object.
*/
function sharedresource_get_plugins($entryid = 0) {
    global $CFG;

    $plugins = array();

    // Fetch all plugins.

    $sharedentryplugins = core_component::get_plugin_list('sharedmetadata');

    foreach (array_keys($sharedentryplugins) as $sharedentryplugin) {
        if (!empty($CFG->{'sharedresource_plugin_hide_'.$sharedentryplugin})) {
            // Discard hidden plugins in configuration.
            continue;
        }
        require_once("{$CFG->dirroot}/mod/sharedresource/plugins/{$sharedentryplugin}/plugin.class.php");
        $sharedresourceclass = "sharedresource_plugin_{$sharedentryplugin}";
        $plugin = new $sharedresourceclass($entryid);
        $plugins[$sharedentryplugin] = $plugin;
    }
    return $plugins;
}

/**
 * callback method from modedit.php for adding a new sharedresource instance
 * we receive a record, but need an object to call add_instance on.
 * @param object $sharedresource data comming from form
 */
function sharedresource_add_instance($sharedresource) {
    global $CFG;

    $sharedresource->type = 'file';
    $instance = new sharedresource_base();
    $instance->sharedresource = $sharedresource;
    return $instance->add_instance();
}

/**
 * callback method from modedit.php for updating a sharedresource instance
 * we receive a record, but need an object to call add_instance on.
 */
function sharedresource_update_instance($sharedresource) {
    global $CFG;

    $sharedresource->type = 'file'; // Just to be sure
    $instance = new sharedresource_base();
    $instance->sharedresource = $sharedresource;
    return $instance->update_instance();
}


/**
 * callback method from modedit.php for deleting a sharedresource instance
 * This will NOT delete sharedresource entries in the library.
 */
function sharedresource_delete_instance($id) {
    global $CFG, $DB;

    if (!$sharedresource = $DB->get_record('sharedresource', array('id' => $id))) {
        return false;
    }

    $instance = new sharedresource_base();
    $instance->sharedresource = $sharedresource;
    return $instance->delete_instance();
}

/**
 * What does this do?
 */
function sharedresource_user_outline($course, $user, $mod, $sharedresource) {
    global $DB;

    $select = "
        userid = ? AND 
        module = 'sharedresource' AND 
        action = 'view' AND 
        info = ?
    ";

    if ($logs = $DB->get_records_select('log', $select, array($user->id, $sharedresource->id), "time ASC")) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new StdClass;
        $result->info = get_string("numviews", "", $numviews);
        $result->time = $lastlog->time;

        return $result;
    }
    return NULL;
}


/**
 * What does this do?
 */
function sharedresource_user_complete($course, $user, $mod, $sharedresource) {
    global $CFG,$DB;

    $select = "
        userid = ? AND
        module='sharedresource' AND
        action='view' AND 
        info= ?
    ";
    if ($logs = $DB->get_records_select('log', $select, array($user->id, $sharedresource->id), 'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);

    } else {
        print_string('neverseen', 'sharedresource');
    }
}

/**
 * What does this do?
 */
function sharedresource_get_participants($sharedresourceid) {
//Returns the users with data in one sharedresource
//(NONE, byt must exists on EVERY mod !!)

    return false;
}

/**
 * This constructs the course module information the gets used
 * in the course module list.
 */
function sharedresource_get_coursemodule_info($coursemodule) {
    global $CFG,$DB;

    $info = null;

    if ($sharedresource = $DB->get_record('sharedresource',array('id'=> $coursemodule->instance), 'id, popup, identifier, type, name')) {

       $sharedresource_entry = sharedresource_entry::read($sharedresource->identifier);

       $info = new StdClass;
       $info->name = $sharedresource->name;
       if (!empty($sharedresource->popup)) {
           $info->extra =  urlencode("onclick=\"this.target='sharedresource$sharedresource->id'; return ".
                    "openpopup('/mod/sharedresource/view.php?inpopup=true&amp;id=".
                    $coursemodule->id.
                    "','sharedresource$sharedresource->id','$sharedresource->popup');\"");
       }

       require_once($CFG->libdir.'/filelib.php');

       if (!$sharedresource_entry) {
           $icon = 'unknown';
       } else {
           if ($sharedresource_entry->file) {
               $fs = get_file_storage();
               if ($filerec = $fs->get_file_by_id($sharedresource_entry->file)) {
                   $mimetype = $filerec->get_mimetype();
                  $icon = file_mimetype_icon($mimetype);
               } else {
                   $icon = 'icon';
               }
           } else {
               $icon = 'icon';
           }
       }

       if ($icon != 'unknown') {
           $info->icon = $icon;
       } else {
           $info->icon = 'icon';
       }
   }

   return $info;
}

function sharedresource_fetch_remote_file ($cm, $url, $headers = '' ) {
    global $CFG;

    require_once("$CFG->libdir/snoopy/Snoopy.class.inc");

    $client = new Snoopy();
    $ua = 'Moodle/'. $CFG->release . ' (+http://moodle.org';
    if ( $CFG->sharedresource_usecache ) {
        $ua = $ua . ')';
    } else {
        $ua = $ua . '; No cache)';
    }
    $client->agent = $ua;
    $client->read_timeout = 5;
    $client->use_gzip = true;
    if (is_array($headers) ) {
        $client->rawheaders = $headers;
    }

    @$client->fetch($url);
    if ( $client->status >= 200 && $client->status < 300 ) {
        $tags = array('A'      => 'href=',
                      'IMG'    => 'src=',
                      'LINK'   => 'href=',
                      'AREA'   => 'href=',
                      'FRAME'  => 'src=',
                      'IFRAME' => 'src=',
                      'FORM'   => 'action=');

        foreach ($tags as $tag => $key) {
            $prefix = "fetch.php?id=$cm->id&amp;url=";
            if ( $tag == 'IMG' or $tag == 'LINK' or $tag == 'FORM') {
                $prefix = "";
            }
            $client->results = sharedresource_redirect_tags($client->results, $url, $tag, $key, $prefix);
        }
    } else {
        if ( $client->status >= 400 && $client->status < 500) {
            $client->results = get_string('fetchclienterror', 'sharedresource');  // Client error
        } elseif ( $client->status >= 500 && $client->status < 600) {
            $client->results = get_string('fetchservererror', 'sharedresource');  // Server error
        } else {
            $client->results = get_string('fetcherror', 'sharedresource');     // Redirection? HEAD? Unknown error.
        }
    }
    return $client;
}

function sharedresource_redirect_tags($text, $url, $tagtoparse, $keytoparse, $prefix = "" ) {
    $valid = 1;
    if ( strpos($url,'?') == FALSE ) {
        $valid = 1;
    }
    if ( $valid ) {
        $lastpoint = strrpos($url,'.');
        $lastslash = strrpos($url,'/');
        if ( $lastpoint > $lastslash ) {
            $root = substr($url, 0, $lastslash + 1);
        } else {
            $root = $url;
        }
        if ( $root == 'http://' or $root == 'https://') {
            $root = $url;
        }
        if ( substr($root,strlen($root)-1) == '/' ) {
            $root = substr($root, 0, -1);
        }

        $mainroot = $root;
        $lastslash = strrpos($mainroot,"/");
        while ( $lastslash > 9) {
            $mainroot = substr($mainroot, 0, $lastslash);

            $lastslash = strrpos($mainroot, "/");
        }

        $regex = "/<$tagtoparse (.+?)>/is";
        $count = preg_match_all($regex, $text, $hrefs);
        for ( $i = 0; $i < $count; $i++) {
            $tag = $hrefs[1][$i];

            $poshref = strpos(strtolower($tag),strtolower($keytoparse));
            $start = $poshref + strlen($keytoparse);
            $left = substr($tag, 0, $start);
            if ( $tag[$start] == '"' ) {
                $left .= '"';
                $start++;
            }
            $posspace   = strpos($tag, ' ', $start + 1);
            $right = "";
            if ( $posspace != FALSE) {
                $right = substr($tag, $posspace);
            }
            $end = strlen($tag) - 1;
            if ( $tag[$end] == '"' ) {
                $right = '"' . $right;
            }
            $finalurl = substr($tag, $start, $end - $start + $diff);
            // Here, we could have these possible values for $finalurl:
            //     file.ext                             Add current root dir
            //     http://(domain)                      don't care
            //     http://(domain)/                     don't care
            //     http://(domain)/folder               don't care
            //     http://(domain)/folder/              don't care
            //     http://(domain)/folder/file.ext      don't care
            //     folder/                              Add current root dir
            //     folder/file.ext                      Add current root dir
            //     /folder/                             Add main root dir
            //     /folder/file.ext                     Add main root dir

            // Special case: If finalurl contains a ?, it won't be parsed
            $valid = 1;

            if ( strpos($finalurl, "?") == FALSE ) {
                $valid = 1;
            }
            if ( $valid ) {
                if ( $finalurl[0] == '/' ) {
                    $finalurl = $mainroot . $finalurl;
                } elseif ( strtolower(substr($finalurl, 0, 7)) != 'http://' and
                           strtolower(substr($finalurl, 0, 8)) != 'https://') {
                     if ( $finalurl[0] == '/') {
                        $finalurl = $mainroot . $finalurl;
                     } else {
                        $finalurl = "$root/$finalurl";
                     }
                }

                $text = str_replace($tag,"$left$prefix$finalurl$right", $text);
            }
        }
    }
    return $text;
}

/**
 * Check to see if a given URI is a URL.
 * 
 * @param $path  string, URI.
 * 
 * @return bool, true = is URL
 */
function sharedresource_is_url($path) {
    if (strpos($path, '://')) {     // eg http:// https:// ftp://  etc
        return true;
    }
    if (strpos($path, '/') === 0) { // Starts with slash
        return true;
    }
    return false;
}

function sharedresource_get_view_actions() {
    return array('view', 'view all');
}

function sharedresource_get_post_actions() {
    return array();
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * 
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function sharedresource_reset_userdata($data) {
    return array();
}

/**
 * Returns all other caps used in module
 * 
 * @return array, of capabilities
 */ 
function sharedresource_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * Function to check and create the needed moddata dir to
 * save all the mod backup files. We always name it moddata
 * to be able to restore it, but in restore we check for
 * $CFG->moddata !!
 * 
 * @return bool, true = dir exists
 */
function sharedresource_check_and_create_moddata_temp_dir() {
    global $CFG;

    $status = check_dir_exists($CFG->dataroot.SHAREDRESOURCE_TEMPPATH, true);
    return $status;
}

/**
 * Function to check and create the needed moddata dir to
 * save all the mod backup files. We always name it moddata
 * to be able to restore it, but in restore we check for
 * $CFG->moddata !!
 * 
 * @return bool, true = dir exists
 */
function sharedresource_check_and_create_moddata_sharedresource_dir() {
    global $CFG;

    $status = check_dir_exists($CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH, true);
    return $status;
}

/**
 *  copy file - most likely tmp file to temp, while resource details are 
 *  sorted out
 * 
 * @param $from_file string, source file for copy
 * @param $to_file string, destination location for file
 * @param $log_clam bool, shall we log this?
 * @return bool, true = success
 *
 * TODO : reimplement file copy using the files indexes
 */
function sharedresource_copy_file($from_file, $to_file, $log_clam = false) {
    global $CFG;

    if (is_file($from_file)) {
        umask(0000);
        if (copy($from_file,$to_file)) {
            chmod($to_file,$CFG->directorypermissions);
            if (!empty($log_clam) && function_exists('clam_log_upload')) {
                clam_log_upload($to_file,null,true);
            }
            return true;
        }
        return false;
    } else {
        return false;
    }
}

/**
 *  delete file - most likely removing temp file
 * 
 * @param $file string, location of file to delete
 * @return bool, true = succesful delete
 *
 * TODO : obsolete, should rely on new file storage
 */
function sharedresource_delete_file($file) {

    if (is_file($file)) {
        chmod($file, 0777);
        if (((unlink($file))) == FALSE) {
            return false;
        }
        return true;
    } else {
        return false;
    }
}

/**
 * generate key/unique name of file
 * 
 * @param $file string, location of file
 * @return string, sha1 hash of file contents  
 * Obsolete : Relies on file storage now
 */
function sharedresource_sha1file($file) {
    return sha1(file_get_contents($file));
}

/**
 * format the URL correctly for local files    
 * 
 * @param $sharedresourceentry object, actual sharedresource object
 * @param $options array, query string parameters to be passed along
 * @return string, formated URL.
 */
function sharedresource_get_file_url($sharedresource, $sharedresourceentry, $options = null) {
    global $CFG, $HTTPSPAGEREQUIRED;


    $fs = get_file_storage();
    $file = $fs->get_file_by_id($sharedresourceentry->file);
    
    if(!$file){
        return false;
    }

    if ($sharedresource->cm) {
        $viewcontextid = context_module::instance($sharedresource->cm->id)->id;
    } else {
        $viewcontextid = $file->get_contextid(); // should be system context
    }

    $ffurl = $CFG->wwwroot."/pluginfile.php/".$viewcontextid."/".$file->get_component()."/".$file->get_filearea()."/".$file->get_itemid().$file->get_filepath().$file->get_filename();

    return $ffurl;
}

/**
 * return a 404 if a shared Resource is not found
 * 
 * @param courseid int, the current context course
 */
function sharedresource_not_found($courseid = 0, $reason = '') {
    global $CFG;

    header('HTTP/1.0 404 not found');
    $url = $CFG->wwwroot;
    if ($courseid != 0) {
        $url = $CFG->wwwroot.'/course/view.php?id='.$courseid;
    }
    print_error('filenotfound', 'sharedresource', $url, $reason);
}

/**
 * Sends the files for sharedresources
 * @param object $course the course in context of the call. Can be null 
 * when acceding from a system context
 * @param object $cm when call comes from a sharedresource instance. denotes the corresponding course module
 * @param object $context can be a system level context or category context level when addressed from the library, or a course module ocntext
 * when accessed from the sharedresource instance in the course.
 */
function mod_sharedresource_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;

    $config = get_config('local_sharedresources');
    $fs = get_file_storage();

    // find the file record
    if ($filearea === 'sharedresource') {
        $itemid = array_shift($args);
        $relativepath = implode('/', $args);
        $fullpath = "/1/mod_sharedresource/sharedresource/$itemid/$relativepath";
        // TODO: add any other access restrictions here if needed!

    } elseif ($filearea === 'package') {
        // packages are stored instances of activites ready for publishing.
        require_login($course);

        if (!has_capability('moodle/course:manageactivities', $context)) {
            return false;
        }
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_scorm/package/$itemid/$relativepath";
        $lifetime = 0; // No caching here.

        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

        // Finally send the file.
        send_stored_file($file, $lifetime, 0, false);
        return;
    } else {
        // Invalid filearea.
        return false;
    }

    // Now finish with sharedresource area.
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    $sharedresourceentry = $DB->get_record('sharedresource_entry', array('file' => $file->get_id()));

    // Control course module integrity.
    if ($filearea && !empty($cm)) {
        $itemid = $cm->instance;
        $identifier = $DB->get_field('sharedresource', 'identifier', array('id' => $cm->instance));
        $entryfilefromcm = $DB->get_field('sharedresource_entry', 'file', array('identifier' => $identifier));
        if ($entryfilefromcm != $file->get_id()) {
            // echo "cm check failed : {$identifier} > $entryfilefromcm <> ".$file->get_id();
            return false;
        }
    }

    $systemcontext = context_system::instance();
    if ($sharedresourceentry->context == $systemcontext->id) {
        // System resources are naturally publically exposed unless global privacy is required.
       if (!empty($config->privatecatalog)) {
            require_login();
       }
    } else {
        require_login();
    }

    $lifetime = isset($CFG->filelifetime) ? $CFG->filelifetime : 86400;

    // Finally send the file.
    send_stored_file($file, $lifetime, 0, false);
}
