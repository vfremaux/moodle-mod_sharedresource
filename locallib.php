<?php

require_once $CFG->dirroot.'/mod/sharedresource/sharedresource_entry.class.php';
require_once $CFG->dirroot.'/mod/sharedresource/lib.php';

/**
* loads the usable version of plugins local strings
* @param array $string
* @param string $lang
* @todo can be optimized for not loading unused strings elsewhere than in admin
* setting forms. 
*/
function sharedresource_load_plugins_lang(&$string, $lang=''){
    global $CFG;
    
    if ($lang == ''){
        $lang = current_language();
    }
    
    $plugins = get_list_of_plugins('mod/sharedresource/plugins');
    foreach($plugins as $plugin){
        $pluginlang = $CFG->dirroot."/mod/sharedresource/plugins/{$plugin}/lang/".$lang."/{$plugin}.php";
        include_once($pluginlang);
    }
}

/**
* converts a given resource into sharedresource, activating required indexing plugins
* @param object $resource
*/
function sharedresource_convertto(&$resource){
    global $CFG;

    /// first get the course module the resource is attached to
    if (!$module = get_record('modules', 'name', 'sharedresource')){
        error('sharedresource module not installed !!');
    }

    $cm = get_coursemodule_from_instance('resource', $resource->id, $resource->course);

    /// recognizes the "file" type, discard other types    
    if ($resource->type != 'file'){
        return false;
    }

    /// make a sharedresource_entry record
    $sharedresource_entry = new sharedresource_entry(false); 
    $sharedresource_entry->title = addslashes($resource->name);
    $sharedresource_entry->description = addslashes($resource->summary);
    $sharedresource_entry->keywords = '';
    $sharedresource_entry->type = 'file';
    if (preg_match("/^(http|https|ftp)?:\/\//", $resource->reference)){
        $sharedresource_entry->url = $resource->reference;
        $sharedresource_entry->sharedresourcefile = '';
        $sharedresource_entry->identifier = sha1($sharedresource_entry->url);
    } else {
        $tempfile = $CFG->dataroot.'/'.$resource->course.'/'.$resource->reference;
        $hash = sharedresource_sha1file($tempfile);
        $sharedresource_entry->identifier = $hash;
        $sharedresource_entry->file = $hash.'-'.basename($resource->reference);
        $sharedresource_entry->tempfilename = $CFG->dataroot.'/'.$resource->course.'/'.$resource->reference;
        if (function_exists('mime_content_type')){
            $sharedresource_entry->mimetype = mime_content_type($sharedresource_entry->tempfilename);
        }
        $sharedresource_entry->url = '';
    }
    if (!record_exists('sharedresource_entry', 'identifier', $sharedresource_entry->identifier)){
        $sharedresource_entry->add_instance();
    }

    /// move the physical resource (made by add_instance() above)

    /// give some trace
    if (debugging()){
        echo "Constructed resource entry : {$sharedresource_entry->identifier}<br/>";
    }
    
    /// attach the new resource to a course module
    $sharedresource = new sharedresource_base(0, $sharedresource_entry->identifier);
    $sharedresource->options = $resource->options;
    $sharedresource->popup = $resource->popup;
    $sharedresource->type = 'file';
    $sharedresource->identifier = $sharedresource_entry->identifier;
    $sharedresource->cm = $cm->id;
    $sharedresource->name = addslashes($resource->name);
    $sharedresource->course = $resource->course;
    $sharedresource->description = addslashes($resource->summary);
    $sharedresource->alltext = '';
    if (!$sharedresourceid = $sharedresource->add_instance($sharedresource)){
        error("sharedresource instance creation error");
    }

    /// rebind the existing module to the new sharedresource
    $cm->instance = $sharedresourceid;
    $cm->module = $module->id;

    /// remoteid was obtained by $sharedresource_entry->add_instance() plugin hooking !! ;
    $cm->idnumber = @$sharedresource_entry->remoteid;
    if (!update_record('course_modules', $cm)){
        error("Could not update course module");
    }

    /// discard the old resource
    delete_records('resource', 'id', $resource->id);

    /// cleanup logs and anything that points to this resource...
}

/**
* back converts a given sharedresource into independant resource.
* This WILL NOT remove the shared resource from repository
* @param reference $sharedresource
* @param boolean $makecm if true, the produced resource is bound to a priorly existing course module. If false, all the coursemodule concerns are ignored.
*/
function sharedresource_convertfrom(&$sharedresource, $makecm = true){
    global $CFG;

    if (!$sharedmodule = get_record('modules', 'name', 'sharedresource')){
        error('sharedresource module not installed !!');
    }
    $module = get_record('modules', 'name', 'resource');

    /// get the sharedresource_entry that is represented by the sharedresource
    if (!$sharedresource_entry = get_record('sharedresource_entry', 'identifier', $sharedresource->identifier)){
        error("Could not find the associated resource");
    }

    /// recognizes the "file" type, discard other types    
    if ($sharedresource->type != 'file'){
        return false;
    }

    /// calculate physical locations and reference
    if (!empty($sharedresource_entry->file)){
        $source = $CFG->dataroot.'/sharedresources/'.$sharedresource_entry->file;
        // filters out md5 identifer and replace with simple timed stamp
        $destname = preg_replace("/^[0-9abcdef]+-/", time().'_', $sharedresource_entry->file);
        if (!is_dir($CFG->dataroot.'/'.$sharedresource->course)){
            mkdir($CFG->dataroot.'/'.$sharedresource->course);
        }
        $dest = $CFG->dataroot.'/'.$sharedresource->course.'/'.$destname;
        $resource->reference = $destname;
    } else {
        $resource->reference = $sharedresource_entry->url;
    }

    /// complete a resource record
    $resource->course = $sharedresource->course;
    $resource->type = 'file';
    $resource->name = addslashes($sharedresource->name);
    $resource->summary = addslashes($sharedresource->description);
    $resource->alltext = addslashes($sharedresource->alltext);
    $resource->popup = $sharedresource->popup;
    $resource->options = $sharedresource->options;
    $resource->timemodified = $sharedresource->timemodified;

    $resourceid = insert_record('resource', $resource);

    /// give some trace
    if (debugging()){
        echo "Constructed ressource : {$resourceid}<br/>";
    }
    
    print_string('localizeadvice', 'sharedresource');

    /// copy the physical resource back to course space
    if (!empty($sharedresource_entry->file)){
        copy($source, $dest);
    }
    
    if (!$makecm){
        return $resourceid;
    }
    
    /// rebind the existing module to the new resource
    $cm = get_coursemodule_from_instance('sharedresource', $sharedresource->id, $sharedresource->course);
    $cm->instance = $resourceid;
    $cm->module = $module->id;
    if (!update_record('course_modules', $cm)){
        error('Could not update course module');
    }

    /// discard the old sharedresource module
    delete_records('sharedresource', 'id', $sharedresource->id);
    
    /// Original resource stays in repository.
    // TODO : examinate librarian case that may want to fully discard the resource.

    /// cleanup logs and anything that points to this resource...
}

/**
* Used by : provider interface (worker function)
* retrieve the remote categorisation of resources using LOM taxonomy or any local strategy
* @param string $element the metadata element
* @param string $namespace which plugin is searched for metadata
* @param string $what if values, get a list of metadata values, else gives a list of sharedresources entries
* @param string $using the constraint value in metadatas. Using can be a comma separated list of tokens
*/
function sharedresource_get_by_metadata($element, $namespace = "lom", $what = 'values', $using = '') {
    global $CFG;
    // get metadata plugin and restype element name
    
    include_once $CFG->dirroot.'/mod/sharedresource/plugins/'.$namespace.'/plugin.class.php';
    $classname = "sharedresource_plugin_{$namespace}";
    $mtdplugin = new $classname();
	$mtdelement = $mtdplugin->getElement($element);
	if ($what == 'values'){
		$clause = ($mtdelement->type == 'list') ? " element LIKE '{$mtdelement->id}:' " : " element = '{$mtdelement->id}' ";		
		$fields = 'value';
	} else {
		if ($mtdelement->type == 'freetext'){
			$textoption = substr($using, 0, strpos($using, ':'));
			$using = substr($using, strpos($using,':') + 1);
			if (!empty($using)){
				$listtokens = explode(',', $using);
				foreach($listtokens as $token){
					switch($textoption){
						case 'includes' :
						$listsearchoptions[] = ' value LIKE \'%'.trim($token).'%\' ';
						break;
						case 'equals' :
						$listsearchoptions[] = ' value = \''.trim($token).'\' ';
						break;
						case 'beginswith' :
						$listsearchoptions[] = ' value LIKE \''.trim($token).'%\' ';
						break;
						case 'endswith' :
						$listsearchoptions[] = ' value LIKE \'%'.trim($token).'\' ';
						break;
						default : 
						break;
					}
				}
				$listsearch = implode(' OR ', $listsearchoptions);
				$clause = " ( $listsearch ) AND element LIKE '{$mtdelement->id}:%' ";
			} else {
				$clause = '';
			}

		}		
		elseif($mtdelement->type == 'date'){
			$datestart = substr($using, 0, strpos($using,':'));
			$dateend = substr($using, strpos($using,':') + 1);
			if($datestart != 'Begin' && $dateend != 'End'){
				$start = mktime(0, 0, 0, substr($datestart, 5, 2),  substr($datestart, 8, 2), substr($datestart, 0, 4));
				$end = mktime(0, 0, 0, substr($dateend, 5, 2),  substr($dateend, 8, 2), substr($dateend, 0, 4));
				$clause = "  value >= $start AND value <= $end AND element LIKE '{$mtdelement->id}:%' ";
			}
			elseif($datestart != 'Begin'){
				$start = mktime(0, 0, 0, substr($datestart, 5, 2),  substr($datestart, 8, 2), substr($datestart, 0, 4));
				$clause = "  value >= $start AND element LIKE '{$mtdelement->id}:%' ";
			}
			elseif($dateend != 'End'){
				$end = mktime(0, 0, 0, substr($dateend, 5, 2),  substr($dateend, 8, 2), substr($dateend, 0, 4));
				$clause = "  value <= $end AND element LIKE '{$mtdelement->id}:%' ";
			}
		}
		
		elseif($mtdelement->type == 'numeric'){
			$symbol = substr($using, 0, strpos($using,':'));
			$value = substr($using, strpos($using,':') + 1);
			$clause = "  value $symbol $value AND element LIKE '{$mtdelement->id}:%' ";
		}
		
		elseif($mtdelement->type == 'duration'){
			$symbol = substr($using, 0, strpos($using,':'));
			$value = substr($using, strpos($using,':') + 1);
			$clause = "  value $symbol $value AND element LIKE '{$mtdelement->id}:%' ";
		}
		
		elseif($mtdelement->type == 'treeselect'){
			$clause = "  value LIKE '{$using}%' AND element LIKE '{$mtdelement->id}:%' ";
		}
		
		else { //case selectmultiple and select
			if (!empty($using)){
				$listtokens = explode(',', $using);
				foreach($listtokens as $token){
					$listsearchoptions[] = ' value = \''.trim($token).'\' ';
				}
				$listsearch = implode(' OR ', $listsearchoptions);
				$clause = " ( $listsearch ) AND element LIKE '{$mtdelement->id}:%' ";
			} else {
				$clause = '';
			}
		}
		$fields = 'entry_id';
	}
	
	if (!empty($clause)) $clause = $clause . ' AND' ;

    $sql = "
        SELECT DISTINCT
            $fields
        FROM
            {$CFG->prefix}sharedresource_metadata
        WHERE
            $clause
            namespace = '$namespace'
        ORDER BY
            value
     ";
    
    $items = array();
    // debug_trace('localsearch : '.$sql);
    if($recs = get_records_sql($sql)){
        foreach($recs as $rec){
            $items[] = $rec->$fields;
        }
    }
    return $items;
}

/**
* a call back function for autoloading classes when unserializing the widgets
*
*/
function resources_load_searchwidgets($classname) {
	global $CFG;
	
	require_once($CFG->dirroot."/mod/sharedresource/searchwidgets/{$classname}.class.php");
}

// prepare autoloader of missing search widgets
ini_set('unserialize_callback_func', 'resources_load_searchwidgets');

?>