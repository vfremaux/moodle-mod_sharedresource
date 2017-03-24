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
<<<<<<< HEAD
* Implements MNET cross moodle strategy
*
*/
require_once $CFG->dirroot.'/mnet/xmlrpc/client.php';
require_once $CFG->dirroot.'/local/sharedresources/lib.php';
require_once $CFG->dirroot.'/mod/sharedresource/lib.php';
require_once $CFG->dirroot.'/mod/sharedresource/locallib.php';
require_once $CFG->libdir.'/filelib.php';
=======
 * Implements MNET cross moodle strategy
 *
 */
require_once($CFG->dirroot.'/mnet/xmlrpc/client.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->libdir.'/filelib.php');
>>>>>>> MOODLE_32_STABLE

if (!defined('RPC_SUCCESS')) {
    define('RPC_TEST', 100);
    define('RPC_SUCCESS', 200);
    define('RPC_FAILURE', 500);
    define('RPC_FAILURE_USER', 501);
    define('RPC_FAILURE_CONFIG', 502);
    define('RPC_FAILURE_DATA', 503);
    define('RPC_FAILURE_CAPABILITY', 510);
}

/**
 * Interface : provider
 * retrieve all values from a metadata element
 * @param string $remoteuser the username of the remote user
 * @param string $remoteuserhost the MNET hostname of the remote user
 * @param string $element the metadata element name
 * @param string $namespace the metadata plugin name
 */
function sharedresource_rpc_get_metadata($remoteuser, $remoteuserhost, $element, $namespace = 'lom') {
    global $CFG, $DB;
<<<<<<< HEAD
    
=======

>>>>>>> MOODLE_32_STABLE
    $response->status = RPC_SUCCESS;
    // Get local identity.
    $userhost = $DB->get_record('mnet_host', array('wwwroot' => $remoteuserhost));
    if (!$localuser = $DB->get_record('user', array('username' => $remoteuser, 'mnethostid' => $userhost->id))) {
        $response->status = RPC_FAILURE_USER;
        $response->error = "Calling user has no local account. Register remote user first";
        return json_encode($response);
    }
    $response->items = sharedresource_get_by_metadata($element, $namespace, $what = 'values');
    return json_encode($response);
}

/**
 * Interface : provider
 * retrieve the remote categorisation of resources using LOM taxonomy or any local strategy
 * this may have to be a very special case from previous function call, as classification
 * is a tree shaped taxonomy.
 * @param string $remoteuser the username of the remote user
 * @param string $remoteuserhost the MNET hostname of the remote user
 * @param string $rootcategory the root category from where to start
 * @param string $namespace the metadata plugin name
 */
function sharedresource_rpc_get_categories($remoteuser, $remoteuserhost, $rootcategory, $namespace = 'lom') {
    global $CFG, $DB;
<<<<<<< HEAD
    
=======

>>>>>>> MOODLE_32_STABLE
    $response->status = RPC_SUCCESS;
    // Get local identity.
    $userhost = $DB->get_record('mnet_host', array('wwwroot' => $remoteuserhost));
    if (!$localuser = $DB->get_record('user', array('username' => $remoteuser, 'mnethostid' => $userhost->id))) {
        $response->status = RPC_FAILURE_USER;
        $response->error = "Calling user has no local account. Register remote user first";
        return json_encode($response);
    }
    // TODO : browse metadata classification using $rootcategory and getting direct childs only ? or rebuild a category 
    // Exposure strategy (flat list of all categories).
    $response->items = sharedresource_get_by_metadata('Taxum', $namespace, $what = 'values');
    return json_encode($response);
}

/**
 * Interface : provider
 * retrieve the remote list of resources
 * @param string $remoteuser the username of the remote user
 * @param string $remoteuserhost the MNET hostname of the remote user
 * @param string $metadatafilters 
 * @param string $offset
 * @param int $page
 */
function sharedresource_rpc_get_list($remoteuser, $remoteuserhost, $metadatafilters = '', $offset = 0, $page = 20) {
    global $CFG, $DB;

<<<<<<< HEAD
	$systemcontext = context_system::instance();
	
	$response = new StdClass();
    $response->status = RPC_SUCCESS;
    // Get local identity

    $userhost = $DB->get_record('mnet_host', array('wwwroot' => $remoteuserhost));

    if (!$localuser = $DB->get_record('user', array('username' => $remoteuser, 'mnethostid' => $userhost->id))){
=======
    $systemcontext = context_system::instance();

    $response = new StdClass();
    $response->status = RPC_SUCCESS;
    // Get local identity.

    $userhost = $DB->get_record('mnet_host', array('wwwroot' => $remoteuserhost));

    if (!$localuser = $DB->get_record('user', array('username' => $remoteuser, 'mnethostid' => $userhost->id))) {
>>>>>>> MOODLE_32_STABLE
        $response->status = RPC_FAILURE_USER;
        $response->error = "Calling user has no local account. Register remote user first";
        return json_encode($response);
    }

<<<<<<< HEAD
    if (empty($metadatafilters)){
		debug_trace(" Getting without filters ");
=======
    if (empty($metadatafilters)) {
        debug_trace(" Getting without filters ");
>>>>>>> MOODLE_32_STABLE
        $sql = "
            SELECT 
                *
            FROM
                {sharedresource_entry}
            WHERE
                provider = 'local' AND
                isvalid = 1 AND
<<<<<<< HEAD
                context = $systemcontext->id
=======
                context = ?
>>>>>>> MOODLE_32_STABLE
        ";
        $sqlcount = "
            SELECT 
                COUNT(*)
            FROM
                {sharedresource_entry}
            WHERE
                provider = 'local' AND
                isvalid = 1 AND
                context = $systemcontext->id
        ";
        $response->resources['offset'] = $offset;
        $response->resources['page'] = $page;
        $consumers = get_consumers();
        $entrycount = $DB->count_records_sql($sqlcount);
        $response->resources['maxobjects'] = $entrycount;
<<<<<<< HEAD
        debug_trace('without filters. >> '.$sql);
        $entries = $DB->get_records_sql($sql, array(), $offset, $page);
    } else {
    	// we have filters
		// debug_trace(" Getting by filters ");
	    $mtdfiltersarr = (array)$metadatafilters;
	    $sqlclauses = array();
	    $mtdrecs = array();
	    $hasfilter = false;
	    foreach($mtdfiltersarr as $filterkey => $filtervalue){
	    	if (!empty($filtervalue)){
	    		// debug_trace(" Getting local entries with $filterkey as $filtervalue in lomfr ");
		    	$entrysets = sharedresource_get_by_metadata($filterkey, 'lomfr', 'entries', $filtervalue);
				if (!empty($mtdrecs)){
			    	$mtdrecs = array_intersect($mtdrecs, $entrysets->items);
			    } else {
			    	$mtdrecs = $entrysets;
			    }
	    		$hasfilter = true;
		    }
	    }
		// get sharedresources from that preselection	
    	$entrylist = implode("','", array_values($mtdrecs));
    	$clause = " se.id IN('{$entrylist}') ";
	    $sql = "
	        SELECT
	            se.*
	        FROM
	            {sharedresource_entry} se
	        WHERE
		        $clause
	    ";
	    $response->resources['maxobjects'] = count($mtdrecs);
	    $entries = $DB->get_records_sql($sql, array(), $offset, $page);
=======
        $entries = $DB->get_records_sql($sql, array($systemcontext->id), $offset, $page);
    } else {
        // We have filters.
        $mtdfiltersarr = (array)$metadatafilters;
        $sqlclauses = array();
        $mtdrecs = array();
        $hasfilter = false;
        foreach ($mtdfiltersarr as $filterkey => $filtervalue) {
            if (!empty($filtervalue)) {
                // debug_trace(" Getting local entries with $filterkey as $filtervalue in lomfr ");
                $entrysets = sharedresource_get_by_metadata($filterkey, 'lomfr', 'entries', $filtervalue);
                if (!empty($mtdrecs)) {
                    $mtdrecs = array_intersect($mtdrecs, $entrysets->items);
                } else {
                    $mtdrecs = $entrysets;
                }
                $hasfilter = true;
            }
        }
        // Get sharedresources from that preselection.
        $entrylist = implode("','", array_values($mtdrecs));
        $clause = " se.id IN('{$entrylist}') ";
        $sql = "
            SELECT
                se.*
            FROM
                {sharedresource_entry} se
            WHERE
                $clause
        ";
        $response->resources['maxobjects'] = count($mtdrecs);
        $entries = $DB->get_records_sql($sql, array(), $offset, $page);
>>>>>>> MOODLE_32_STABLE
    }
    if ($entries) {
        foreach ($entries as $entry) {
            // Get usage indicators in the network.
            /* TO CHECK : dynamic interrogation fof the network for usage IS NOT
             * possible :  big performance issues. WE MUST cache this value
             * in providers.
             */
            $uses = sharedresource_get_usages($entry, $response->resources);
            // $uses += sharedresource_get_usages($entry, $response->resources, $consumers); / performance trap !!
            // make a remotely interesting record
            $response->resources['entries'][$entry->identifier] = array('title' => $entry->title,
                                                           'description' => $entry->description,
                                                           'file' => $entry->file,
                                                           'url' => $entry->url,
                                                           'identifier' => $entry->identifier,
                                                           'keywords' => $entry->keywords,
                                                           'lang' => $entry->lang,
                                                           'isurlproxy' => empty($entry->file),
                                                           'scorelike' => $entry->scorelike,
                                                           'scoreview' => $entry->scoreview,
                                                           'id' => empty($entry->id),
                                                           'uses' => $uses);
            // Get all metadata.
            if ($metadata = $DB->get_records('sharedresource_metadata', array('entry_id' => $entry->id), 'element', 'element,namespace,value')) {
                $response->resources['entries'][$entry->identifier]['metadata'] = $metadata;
            }
        }
    } else {
        $response->resources['entries'] = array();
    }
    return json_encode($response);
}

/**
<<<<<<< HEAD
* Interface : provider
* allows a consumer to push a sharedresource in the repository assuming
* he is transferring authority on the resource
* Note that only "physical file counterpart" resources are accepted to be defered.
* @param string $remoteuser the username of the remote user
* @param string $remoteuserhost the MNET hostname of the remote user
* @param mixed $entry if is numeric, only renew metadata, if object or array, create or update a resource
* @param mixed $metadata if not empty, add or update metadata for this record.
*/
function sharedresource_rpc_submit($remoteuser, $remoteuserhost, &$entry, $metadata){
    global $CFG, $DB;
    
    $response->status = RPC_SUCCESS;

    // Get local identity
=======
 * Interface : provider
 * allows a consumer to push a sharedresource in the repository assuming
 * he is transferring authority on the resource
 * Note that only "physical file counterpart" resources are accepted to be defered.
 * @param string $remoteuser the username of the remote user
 * @param string $remoteuserhost the MNET hostname of the remote user
 * @param mixed $entry if is numeric, only renew metadata, if object or array, create or update a resource
 * @param mixed $metadata if not empty, add or update metadata for this record.
 */
function sharedresource_rpc_submit($remoteuser, $remoteuserhost, &$entry, $metadata) {
    global $CFG, $DB;

    $response->status = RPC_SUCCESS;

    // Get local identity.
>>>>>>> MOODLE_32_STABLE

    $userhost = $DB->get_record('mnet_host', array('wwwroot' => $remoteuserhost));
    if (!$localuser = $DB->get_record('user', array('username' => $remoteuser, 'mnethostid' => $userhost->id))) {
        $response->status = RPC_FAILURE_USER;
        $response->error = "Calling user has no local account. Register remote user first";
        return json_encode($response);
    }
    $entry = (object) $entry;
    $oldurl = $entry->url;
    // need to make a local entry
    $entry->provider = 'local';
    $entry->timemodified = time();
    $entry->url = $CFG->wwwroot.'/mod/sharedresource/view.php?identifier='.$entry->identifier;
    if (!is_array($entry) && !is_object($entry)) {
        // We just want to update metadata.
        $response->resourceid = $entry;
        $newid = $entry;
    } else {
        // In case an array or an object, we have to add a new resource, or update an exiting resource.
        $entry = (object) $entry;
        $oldurl = $entry->url;
        // need to make a local entry
        $entry->provider = 'local';
        $entry->timemodified = time();
        // Fetch the file on the consumer side and store it here through a CURL call.
        $ch = curl_init($oldurl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if ($rawresponse = curl_exec($ch)) {
            // Compute a new identifer if not yet given.
            if (empty($entry->identifier)) {
                $entry->identifier = md5($rawresponse);
            }
            // Make a file name if not available.
            if (empty($entry->file)) {
                $basename = substr($oldurl, strrpos($oldurl, '/') + 1);
                $entry->file = $entry->identifier.'_'.$basename;
            }
            // Turnaround some urls that can be a dynamic link do we have a querystring ?
            if (strstr($entry->file, '?') !== false) {
                list($filename, $querystring) = explode('?', $entry->file);
                $parts = pathinfo($filename);
                if (preg_match('/^php/', $parts['extension'])) {
                    $parts['extension'] = 'html';
                }
                // rebinds to html php outputs (might be false)
                // TODO : find a way to get real mimetype
                $entry->file = $parts['filename'].'_'.md5($querystring).'.'.$parts['extension'];
            }
            $filename = $CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH.$entry->file;
            if (!is_dir($CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH)) {
                mkdir($CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH);
            }
            $FILE = fopen($filename, 'w');
            fwrite($FILE, $rawresponse);
            fclose($FILE);
            if (empty($entry->mimetype)) {
                $entry->mimetype = mimeinfo('type', $filename);
            }
            $entry->url = $CFG->wwwroot.'/mod/sharedresource/view.php?identifier='.$entry->identifier;            
            $response->resourceurl = $entry->url;
<<<<<<< HEAD
	        $response->status = RPC_SUCCESS;
	    } else {
	        $response->status = RPC_FAILURE;
	        $response->error = curl_errno($ch) .':'. curl_error($ch);
	        if (!$testmode)
				return json_encode($response);
	    }
		// Check for existance and save db record
	    if(!$localentry = $DB->get_record('sharedresource_entry', array('identifier' => $entry->identifier))){
	        $newid = $DB->insert_record('sharedresource_entry', $entry);
	    } else {
	        $entry->id = $localentry->id;
	        $newid = $DB->update_record('sharedresource_entry', $entry);
	    }
	    $response->resourceid = $newid;
	    // finally, fetch all consumers and ask them to change resource location
	    if($consumers = get_consumers()){    
	        foreach($consumers as $consumer){
	            $client = new mnet_xmlrpc_client();
	            $client->set_method('mod/sharedresource_rpc_move');
	            $client->add_param($remoteuser);
	            $client->add_param($remoteuserhost);
	            $client->add_param($entry->identifier);
	            $client->add_param(resources_repo());
	            $client->add_param($entry->url, 'string');
	        }
	    }
	}
	
=======
            $response->status = RPC_SUCCESS;
        } else {
            $response->status = RPC_FAILURE;
            $response->error = curl_errno($ch) .':'. curl_error($ch);
            if (!$testmode) {
                return json_encode($response);
            }
        }
        // Check for existance and save db record.
        if (!$localentry = $DB->get_record('sharedresource_entry', array('identifier' => $entry->identifier))) {
            $newid = $DB->insert_record('sharedresource_entry', $entry);
        } else {
            $entry->id = $localentry->id;
            $newid = $DB->update_record('sharedresource_entry', $entry);
        }
        $response->resourceid = $newid;
        // Finally, fetch all consumers and ask them to change resource location.
        if ($consumers = get_consumers()) {
            foreach($consumers as $consumer) {
                $client = new mnet_xmlrpc_client();
                $client->set_method('mod/sharedresource_rpc_move');
                $client->add_param($remoteuser);
                $client->add_param($remoteuserhost);
                $client->add_param($entry->identifier);
                $client->add_param(resources_repo());
                $client->add_param($entry->url, 'string');
            }
        }
    }
    
>>>>>>> MOODLE_32_STABLE
    // finally store eventually provided metadata
    if (!empty($metadata)) {
        $DB->delete_records('sharedresource_metadata', array('entry_id' => $newid)); 
        // For replacing old metadata by submitted one. May not have any records in case of a new resource.
        foreach ($metadata as $datum) {
            $datum->entry_id = $newid;
            $DB->insert_record('sharedresource_metadata', $datum);
        }
    }
    return json_encode($response);
}

/**
<<<<<<< HEAD
* Interface : consumer
* @param string $remoteuser the username of the remote user
* @param string $remoteuserhost the MNET hostname of the remote user
* @param string $resourceID the resource Unique Identifier
*/
function sharedresource_rpc_check($remoteuser, $remoteuserhost, $resourceID){
	global $DB;
	
    $response = '';    
    $uses = $DB->count_records('sharedresource', array('identifier' => $resourceID));
    return $uses;        
}

/**
* Interface : consumer
* allows a producer to claim for moving the physical location point of a 
* resource he has obtained. When a producer gets a resource through a submission,
* he will call all his consumers to aske them for moving the resource from old location 
* @param string $remoteuser the username of the remote user
* @param string $remoteuserhost the MNET hostname of the remote user
* @param string $resourceID the resource Unique Identifier
* @param string $provider the new provider
* @param string $url the local url of the provider for the resource
*/
function sharedresource_rpc_move($remoteuser, $remoteuserhost, $resourceID, $provider, $url){
	global $DB;
	
=======
 * Interface : consumer
 * @param string $remoteuser the username of the remote user
 * @param string $remoteuserhost the MNET hostname of the remote user
 * @param string $resourceID the resource Unique Identifier
 */
function sharedresource_rpc_check($remoteuser, $remoteuserhost, $resourceid) {
    global $DB;

    $response = '';
    $uses = $DB->count_records('sharedresource', array('identifier' => $resourceid));
    return $uses;
}

/**
 * Interface : consumer
 * allows a producer to claim for moving the physical location point of a 
 * resource he has obtained. When a producer gets a resource through a submission,
 * he will call all his consumers to aske them for moving the resource from old location 
 * @param string $remoteuser the username of the remote user
 * @param string $remoteuserhost the MNET hostname of the remote user
 * @param string $resourceID the resource Unique Identifier
 * @param string $provider the new provider
 * @param string $url the local url of the provider for the resource
 */
function sharedresource_rpc_move($remoteuser, $remoteuserhost, $resourceid, $provider, $url) {
    global $DB;

>>>>>>> MOODLE_32_STABLE
    $response = '';
    // Get local identity.
    $userhost = $DB->get_record('mnet_host', array('wwwroot' => $remoteuserhost));
    if (!$localuser = $DB->get_record('user', array('username' => $remoteuser, 'mnethostid' => $userhost->id))) {
        $response->status = RPC_FAILURE_USER;
        $response->error = "Calling user has no local account. Register remote user first";
        return json_encode($response);
    }
    if ($resource_entry = $DB->get_record('sharedresource_entry', array('identifier' => $resourceid))) {
        $resource_entry->url = $url;
        $resource_entry->provider = $provider;
        if ($DB->update_record('sharedresource_entry', $resource_entry)) {
            $response->status = RPC_SUCCESS;
        } else {
            $response->status = RPC_FAILURE;
            $response->error = " Resource $resourceID could not be moved to $provider at $url";
        }
    } else {
        $response->status = RPC_FAILURE;
        $response->error = " Resource $resourceID could not be found ";
    }
    return json_encode($response);
}
