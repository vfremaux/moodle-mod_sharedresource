<?php
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
* Find active plugins, load the class files, and instantiate
* the appropriate plugin object.
*/
function sharedresource_get_plugins($entryid = 0) {
    global $CFG;
    
    $plugins = array();

    /// fetch all plugins

    $sharedentryplugins = get_list_of_plugins('mod/sharedresource/plugins');

    foreach ($sharedentryplugins as $sharedentryplugin) {
        if (!empty($CFG->{'sharedresource_plugin_hide_'.$sharedentryplugin})) {  // discard hidden plugins in configuration
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
* Check all the plugins to see if they specify the extra screen
*/
function sharedresource_extra_resource_screen() {
    $plugins = sharedresource_get_plugins();
    $extra = false;
    foreach ($plugins as $plugin) {
        $extra = $plugin->sharedresource_entry_extra_form_required();
        if ($extra) {
            break;
        }
    }
    return $extra;
}


/**
* callback method from modedit.php for adding a new sharedresource instance
*/
function sharedresource_add_instance($sharedresource) {
    global $CFG;

    $sharedresource->type = clean_param($sharedresource->type, PARAM_SAFEDIR);   // Just to be safe

    require_once($CFG->dirroot."/mod/sharedresource/type/{$sharedresource->type}/resource.class.php");
    $sharedresourceclass = "sharedresource_{$sharedresource->type}";
    $res = new $sharedresourceclass();

    return $res->add_instance($sharedresource);
}


/**
* callback method from modedit.php for updating a sharedresource instance
*/
function sharedresource_update_instance($sharedresource) {
    global $CFG;

    $sharedresource->type = clean_param($sharedresource->type, PARAM_SAFEDIR);   // Just to be safe

    require_once($CFG->dirroot."/mod/sharedresource/type/{$sharedresource->type}/resource.class.php");
    $sharedresourceclass = "sharedresource_{$sharedresource->type}";
    $res = new $sharedresourceclass();

    return $res->update_instance($sharedresource);
}


/**
* callback method from modedit.php for deleting a sharedresource instance
*/
function sharedresource_delete_instance($id) {
    global $CFG;
    if (! $sharedresource = get_record('sharedresource', 'id', $id)) {
        return false;
    }
    $sharedresource->type = clean_param($sharedresource->type, PARAM_SAFEDIR);   // Just to be safe
    require_once("$CFG->dirroot/mod/sharedresource/type/$sharedresource->type/resource.class.php");
    $sharedresourceclass = "sharedresource_$sharedresource->type";
    $res = new $sharedresourceclass();
    return $res->delete_instance($sharedresource);
}

/**
 * What does this do?
 */
function sharedresource_user_outline($course, $user, $mod, $sharedresource) {
    if ($logs = get_records_select("log", "userid='$user->id' AND module='sharedresource'
                                           AND action='view' AND info='$sharedresource->id'", "time ASC")) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new object();
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
    global $CFG;

    if ($logs = get_records_select('log', "userid='$user->id' AND module='sharedresource'
                                           AND action='view' AND info='$sharedresource->id'", "time ASC")) {
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
/// Given a course_module object, this function returns any
/// "extra" information that may be needed when printing
/// this activity in a course listing.
///
/// See get_array_of_activities() in course/lib.php
///

   global $CFG;

   $info = NULL;

   if ($sharedresource = get_record('sharedresource', 'id', $coursemodule->instance, '', '', '', '', 'id, popup, identifier, type, name')) {
       $sharedresource_entry = sharedresource_entry::read($sharedresource->identifier);
       $info = new object();
       $info->name = $sharedresource->name;
       if (!empty($sharedresource->popup)) {
           $info->extra =  urlencode("onclick=\"this.target='sharedresource$sharedresource->id'; return ".
                            "openpopup('/mod/sharedresource/view.php?inpopup=true&amp;id=".
                            $coursemodule->id.
                            "','sharedresource$sharedresource->id','$sharedresource->popup');\"");
       }

       require_once($CFG->libdir.'/filelib.php');

       if ($sharedresource->type == 'file') {
           if (!$sharedresource_entry) {
               $icon = 'unknown.gif';
           }
           else {
               $icon = mimeinfo('icon', $sharedresource_entry->url);
           }
           if ($icon != 'unknown.gif') {
               $info->icon = "f/$icon";
           } else {
               $info->icon = 'f/web.gif';
           }
       } else if ($sharedresource->type == 'directory') {
           $info->icon = 'f/folder.gif';
       }
   }

   return $info;
}

function sharedresource_fetch_remote_file ($cm, $url, $headers = '' ) {
/// Snoopy is an HTTP client in PHP

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
            $client->results = sharedresource_redirect_tags($client->results, $url, $tag, $key,$prefix);
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

function sharedresource_redirect_tags($text, $url, $tagtoparse, $keytoparse,$prefix = "" ) {
    $valid = 1;
    if ( strpos($url,'?') == FALSE ) {
        $valid = 1;
    }
    if ( $valid ) {
        $lastpoint = strrpos($url,'.');
        $lastslash = strrpos($url,'/');
        if ( $lastpoint > $lastslash ) {
            $root = substr($url,0,$lastslash+1);
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
            $mainroot = substr($mainroot,0,$lastslash);

            $lastslash = strrpos($mainroot,"/");
        }

        $regex = "/<$tagtoparse (.+?)>/is";
        $count = preg_match_all($regex, $text, $hrefs);
        for ( $i = 0; $i < $count; $i++) {
            $tag = $hrefs[1][$i];

            $poshref = strpos(strtolower($tag),strtolower($keytoparse));
            $start = $poshref + strlen($keytoparse);
            $left = substr($tag,0,$start);
            if ( $tag[$start] == '"' ) {
                $left .= '"';
                $start++;
            }
            $posspace   = strpos($tag, ' ', $start+1);
            $right = "";
            if ( $posspace != FALSE) {
                $right = substr($tag, $posspace);
            }
            $end = strlen($tag)-1;
            if ( $tag[$end] == '"' ) {
                $right = '"' . $right;
            }
            $finalurl = substr($tag,$start,$end-$start+$diff);
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

            if ( strpos($finalurl,"?") == FALSE ) {
                $valid = 1;
            }
            if ( $valid ) {
                if ( $finalurl[0] == '/' ) {
                    $finalurl = $mainroot . $finalurl;
                } elseif ( strtolower(substr($finalurl,0,7)) != 'http://' and
                           strtolower(substr($finalurl,0,8)) != 'https://') {
                     if ( $finalurl[0] == '/') {
                        $finalurl = $mainroot . $finalurl;
                     } else {
                        $finalurl = "$root/$finalurl";
                     }
                }

                $text = str_replace($tag,"$left$prefix$finalurl$right",$text);
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

/**
 * Get the list of supported types compatible with mod/resource.
 * 
 * @return array, resource type objects
 */
function sharedresource_get_types() {
    global $CFG;

    $types = array();

    $standardsharedresources = array('file');
    foreach ($standardsharedresources as $sharedresourcetype) {
        $type = new StdClass;
        $type->modclass = MOD_CLASS_RESOURCE;
        $type->name = $sharedresourcetype;
        $type->type = "sharedresource&amp;type=$sharedresourcetype";
        $type->typestr = get_string("sharedresourcetype$sharedresourcetype", 'sharedresource');
        $types[] = $type;
    }

    /// Drop-in extra sharedresource types
    $sharedresourcetypes = get_list_of_plugins('mod/sharedresource/type');
    foreach ($sharedresourcetypes as $sharedresourcetype) {
        if (!empty($CFG->{'sharedresource_hide_'.$sharedresourcetype})) {  // Not wanted
            continue;
        }
        if (!in_array($sharedresourcetype, $standardsharedresources)) {
            $type = new StdClass;
            $type->modclass = MOD_CLASS_RESOURCE;
            $type->name = $sharedresourcetype;
            $type->type = "sharedresource&amp;type=$sharedresourcetype&amp;identifier=abc";
            $type->typestr = get_string("sharedresourcetype$sharedresourcetype", 'sharedresource');
            $types[] = $type;
        }
    }

    return $types;
}

function sharedresource_get_view_actions() {
    return array('view','view all');
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
    $status = check_dir_exists($CFG->dataroot.SHAREDRESOURCE_TEMPPATH,true);
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
    $status = check_dir_exists($CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH,true);
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
 */
function sharedresource_copy_file($from_file,$to_file,$log_clam=false) {

    global $CFG;

    if (is_file($from_file)) {
        //echo "<br />Copying ".$from_file." to ".$to_file;              //Debug
        //$perms=fileperms($from_file);
        //return copy($from_file,$to_file) && chmod($to_file,$perms);
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
        //echo "<br />Error: not file or dir ".$from_file;               //Debug
        return false;
    }
}


/**
 *  delete file - most likely removing temp file
 * 
 * @param $file string, location of file to delete
 * @return bool, true = succesful delete
 */
function sharedresource_delete_file($file) {

    if (is_file($file)) {
        chmod($file, 0777);
        if (((unlink($file))) == FALSE) {
            return false;
        }
        return true;
    }
    else {
        return false;
    }
}

/**
 * generate key/unique name of file
 * 
 * @param $file string, location of file
 * @return string, sha1 hash of file contents  
 */
function sharedresource_sha1file($file) {
     return sha1(file_get_contents($file));
}

/**
 * format the URL correctly for local files    
 * 
 * @param $path string, the physical resource location
 * @param $options array, query string parameters to be passed along
 * @return string, formated URL.
 */
function sharedresource_get_file_url($path, $options=null) {
    global $CFG, $HTTPSPAGEREQUIRED;

    $path = str_replace('//', '/', $path);  
    $path = trim($path, '/'); // no leading and trailing slashes

    $url = $CFG->wwwroot."/mod/sharedresource/file.php";
    if ($CFG->slasharguments) {
        $parts = explode('/', $path);
        $parts = array_map('rawurlencode', $parts);
        $path  = implode('/', $parts);
        $ffurl = $url.'/'.$path;
        $separator = '?';
    } else {
        $path = rawurlencode('/'.$path);
        $ffurl = $url.'?file='.$path;
        $separator = '&amp;';
    }

    if ($options) {
        foreach ($options as $name=>$value) {
            $ffurl = $ffurl.$separator.$name.'='.$value;
            $separator = '&amp;';
        }
    }

    return $ffurl;
}


/**
 * return a 404 if a shared Resource is not found
 * 
 * @param courseid int, the current context course    
 */
function sharedresource_not_found($courseid=0) {
    global $CFG;
    header('HTTP/1.0 404 not found');
    $url = $CFG->wwwroot;
    if ($courseid != 0) {
        $url = $CFG->wwwroot.'/course/view.php?id='.$courseid;
    }
    error('filenotfound', 'sharedresource', $url); //this is not displayed on IIS??
}


/**
 * on the install we still need to index the text description
 * which the install.xml syntax does not let us do in a database
 * dependent fashion
 */
function sharedresource_install() {
    global $CFG;

    $result = true;
    
    if (preg_match('/^postgres/', $CFG->dbtype)) {
        $idx_field = 'description';
    } else {
        $idx_field = 'description(250)';
    }
    $table = new XMLDBTable('sharedresource_entry');
    $index = new XMLDBIndex('description');
    $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array($idx_field));
    if (!index_exists($table, $index)) {
        $result = add_index($table, $index, false, false);
    }

    if (!get_record('mnet_service', 'name', 'resource_provider')){
        $service->name = 'resource_provider';
        $service->description = get_string('resource_provider_name', 'sharedresource');
        $service->apiversion = 1;
        $service->offer = 1;
        if (!$serviceid = insert_record('mnet_service', $service)){
            notify('Error installing resource provider service.');
            $result = false;
        }
        
        // retreive remote categorisation
        $rpc->function_name = 'sharedresource_rpc_get_categories';
        $rpc->xmlrpc_path = 'mod/sharedresource/rpclib.php/sharedresource_rpc_get_categories';
        $rpc->parent_type = 'mod';  
        $rpc->parent = 'sharedresource';
        $rpc->enabled = 0; 
        $rpc->help = 'Get remote categorisation of exposed resource.';
        $rpc->profile = '';
        if (!$rpcid = insert_record('mnet_rpc', $rpc)){
            notify('Error installing resource_provider RPC calls.');
            $result = false;
        }
        $rpcmap->serviceid = $serviceid;
        $rpcmap->rpcid = $rpcid;
        insert_record('mnet_service2rpc', $rpcmap);

        // retreive remote categorisation
        $rpc->function_name = 'sharedresource_rpc_get_list';
        $rpc->xmlrpc_path = 'mod/sharedresource/rpclib.php/sharedresource_rpc_get_list';
        $rpc->parent_type = 'mod';  
        $rpc->parent = 'sharedresource';
        $rpc->enabled = 0; 
        $rpc->help = 'Get remote list of exposed resources, by page or category.';
        $rpc->profile = '';
        if (!$rpcid = insert_record('mnet_rpc', $rpc)){
            notify('Error installing resource_provider RPC calls.');
            $result = false;
        }
        $rpcmap->serviceid = $serviceid;
        $rpcmap->rpcid = $rpcid;
        insert_record('mnet_service2rpc', $rpcmap);

        // retreive accept a resource submission 
        $rpc->function_name = 'sharedresource_rpc_submit';
        $rpc->xmlrpc_path = 'mod/sharedresource/rpclib.php/sharedresource_rpc_submit';
        $rpc->parent_type = 'mod';  
        $rpc->parent = 'sharedresource';
        $rpc->enabled = 0; 
        $rpc->help = 'Accepts a resource submission for appending to local repository.';
        $rpc->profile = '';
        if (!$rpcid = insert_record('mnet_rpc', $rpc)){
            notify('Error installing resource_provider RPC calls.');
            $result = false;
        }
        $rpcmap->serviceid = $serviceid;
        $rpcmap->rpcid = $rpcid;
        insert_record('mnet_service2rpc', $rpcmap);

        // retreive accept a resource submission 
        $rpc->function_name = 'sharedresource_rpc_get_metadata';
        $rpc->xmlrpc_path = 'mod/sharedresource/rpclib.php/sharedresource_rpc_get_metadata';
        $rpc->parent_type = 'mod';  
        $rpc->parent = 'sharedresource';
        $rpc->enabled = 0; 
        $rpc->help = 'Get remote metadata values.';
        $rpc->profile = '';
        if (!$rpcid = insert_record('mnet_rpc', $rpc)){
            notify('Error installing resource_provider RPC calls.');
            $result = false;
        }
        $rpcmap->serviceid = $serviceid;
        $rpcmap->rpcid = $rpcid;
        insert_record('mnet_service2rpc', $rpcmap);
    }        

    if (!get_record('mnet_service', 'name', 'resource_consumer')){
        $service->name = 'resource_consumer';
        $service->description = get_string('resource_consumer_name', 'sharedresource');
        $service->apiversion = 1;
        $service->offer = 1;
        if (!$serviceid = insert_record('mnet_service', $service)){
            notify('Error installing resource consumer service.');
            $result = false;
        }
        
        // retreive remote categorisation
        $rpc->function_name = 'sharedresource_rpc_check';
        $rpc->xmlrpc_path = 'mod/sharedresource/rpclib.php/sharedresource_rpc_check';
        $rpc->parent_type = 'mod';  
        $rpc->parent = 'sharedresource';
        $rpc->enabled = 0; 
        $rpc->help = 'Get remote categorisation of exposed resource.';
        $rpc->profile = '';
        if (!$rpcid = insert_record('mnet_rpc', $rpc)){
            notify('Error installing resource_consumer RPC calls.');
            $result = false;
        }
        $rpcmap->serviceid = $serviceid;
        $rpcmap->rpcid = $rpcid;
        insert_record('mnet_service2rpc', $rpcmap);

        // retreive remote categorisation
        $rpc->function_name = 'sharedresource_rpc_move';
        $rpc->xmlrpc_path = 'mod/sharedresource/rpclib.php/sharedresource_rpc_move';
        $rpc->parent_type = 'mod';  
        $rpc->parent = 'sharedresource';
        $rpc->enabled = 0; 
        $rpc->help = 'Ask to move a resource to another provider.';
        $rpc->profile = '';
        if (!$rpcid = insert_record('mnet_rpc', $rpc)){
            notify('Error installing resource_consumer RPC calls.');
            $result = false;
        }
        $rpcmap->serviceid = $serviceid;
        $rpcmap->rpcid = $rpcid;
        insert_record('mnet_service2rpc', $rpcmap);

    }        


    return $result;
}


?>