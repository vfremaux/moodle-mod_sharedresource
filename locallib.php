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
* @param string $type a resource module type, can be 'resource' or 'url'
*/
function sharedresource_convertto(&$resource, $type = 'resource'){
    global $CFG, $DB;

    if (!$module = $DB->get_record('modules', array('name' => 'sharedresource'))){
        print_error('errornotinstalled', 'sharedresource');
    }

    /// first get the course module the resource is attached to
    $cm = get_coursemodule_from_instance($type, $resource->id, $resource->course);
    $context = context_module::instance($cm->id);

    /// make a sharedresource_entry record
    $sharedresource_entry = new sharedresource_entry(false);
    $sharedresource_entry->title = $resource->name;
    $sharedresource_entry->description = $resource->intro;
    $sharedresource_entry->keywords = '';
    $sharedresource_entry->type = 'file';
    if ($type == 'url'){
    	// external url case do not process file records
        $sharedresource_entry->url = $resource->externalurl;
        $sharedresource_entry->file = '';
        $sharedresource_entry->identifier = sha1($sharedresource_entry->url);
    } else {
    	// we convert filestorage reference just moving the records
  		$fs = get_file_storage();
		$resourcefiles = $fs->get_area_files($context->id, 'mod_resource', 'content', 0);
		$stored_file = array_pop($resourcefiles);
		$newfile = new StdClass;
  		$newfile->component = 'mod_sharedresource';
  		$newfile->filearea = 'sharedresource';
  		$newfile->itemid = 0;
  		$newfile->context = context_system::instance();
		$finalrec = $fs->create_file_from_storedfile($newfile, $stored_file);
		$sharedresource_entry->identifier = $stored_file->get_contenthash();
		$sharedresource_entry->url = $CFG->wwwroot.'/mod/sharedresource/view.php?identifier='.$stored_file->get_contenthash();
		$sharedresource_entry->file = $finalrec->get_id();
    }

    if (!$DB->record_exists('sharedresource_entry', array('identifier' => $sharedresource_entry->identifier))){
        $sharedresource_entry->add_instance();
    }

    /// move the physical resource (made by add_instance() above)

    /// give some trace
    if (debugging()){
        echo "Constructed resource entry : {$sharedresource_entry->identifier}<br/>";
    }

    /// attach the new resource to a course module
    $sharedresource = new sharedresource_base(0, $sharedresource_entry->identifier);
    $sharedresource->options = $resource->displayoptions;
    $sharedresource->popup = ''; // no more used
    $sharedresource->type = 'file'; // useless but backcompatibility tracking
    $sharedresource->identifier = $sharedresource_entry->identifier;
    $sharedresource->cm = $cm->id;
    $sharedresource->name = $resource->name;
    $sharedresource->course = $resource->course;
    $sharedresource->intro = $resource->intro;
    $sharedresource->introformat = $resource->introformat;
    $sharedresource->alltext = '';
    if (!$sharedresourceid = $sharedresource->add_instance($sharedresource)){
        print_error('erroraddinstance', 'sharedresource');
    }

    /// rebind the existing module to the new sharedresource
    $cm->instance = $sharedresourceid;
    $cm->module = $module->id;

    /// remoteid was obtained by $sharedresource_entry->add_instance() plugin hooking !! ;
    $cm->idnumber = @$sharedresource_entry->remoteid;
    if (!$DB->update_record('course_modules', $cm)){
        print_error('errorupdatecm', 'sharedresource');
    }

    /// discard the old resource or url
    $DB->delete_records($type, array('id' => $resource->id));

    /// cleanup logs and anything that points to this resource...
}

/**
* back converts a given sharedresource into independant local resource.
* This WILL NOT remove the shared resource from repository
* @param reference $sharedresource
* @param boolean $makecm if true, the produced resource is bound to a priorly existing course module. If false, all the coursemodule concerns are ignored.
*/
function sharedresource_convertfrom(&$sharedresource, $makecm = true){
    global $CFG,$DB;

    if (!$sharedmodule = $DB->get_record('modules', array('name'=> 'sharedresource'))){
        print_error('errornotinstalled', 'sharedresource');
    }
    $module = $DB->get_record('modules', array('name'=> 'resource'));

    /// get the sharedresource_entry that is represented by the sharedresource
    if (!$sharedresource_entry = $DB->get_record('sharedresource_entry',array('identifier' => $sharedresource->identifier))){
        print_error('errorinvalididentifier', 'sharedresource');
    }

    /// calculate physical locations and reference
    if (!empty($sharedresource_entry->file)){
    } else {
        $resource->reference = $sharedresource_entry->url;
    }

    /// complete a resource record
    /*
    $resource->course = $sharedresource->course;
    $resource->type = 'file';
    $resource->name = $sharedresource->name;
    $resource->summary = $sharedresource->description;
    $resource->alltext = $sharedresource->alltext;
    $resource->popup = $sharedresource->popup;
    $resource->options = $sharedresource->options;
    $resource->timemodified = $sharedresource->timemodified;

    */
	$resource->course = $sharedresource->course;
	$resource->name = $sharedresource->name;
	$resource->intro = $sharedresource->description;
	$resource->introformat = FORMAT_MOODLE;
	$resource->tobemigrated = 0;
	$resource->legacyfiles = 0;
	$resource->legacyfileslast = null;
	$resource->display = 0;
	$resource->displayoptions = $sharedresource->options;

    $resourceid = $DB->insert_record('resource', $resource);

    /// give some trace
    /*
    if (debugging()){
        echo "Constructed ressource : {$resourceid}<br/>";
    }
    */

    /// rebind the existing module to the new resource
    $cm = get_coursemodule_from_instance('sharedresource', $sharedresource->id, $sharedresource->course);
    $cm->instance = $resourceid;
    $cm->module = $module->id;
    if (!$DB->update_record('course_modules', $cm)){
        print_error('errorupdatecm', 'sharedresource');
    }

    print_string('localizeadvice', 'sharedresource');

    /// duplicate files record and relocate filearea to resource
  	if (!empty($sharedresource_entry->file)){
  		$fs = get_file_storage();
  		$filerecord = $fs->get_file_by_id($sharedresource_entry->file);
		$newfile = new StdClass;
  		$newfile->component = 'mod_resource';
  		$newfile->filearea = 'content';
  		$newfile->itemid = 0;
  		$newfile->context = context_module::instance($cm->id);
		$fs->create_file_from_storedfile($newfile, $filerecord);
    }

    if (!$makecm){
        return $resourceid;
    }

    /// discard the old sharedresource module
    $DB->delete_records('sharedresource',array('id'=> $sharedresource->id));

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
    global $CFG,$DB;
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

    $sql = "
        SELECT DISTINCT
            $fields
        FROM
            {sharedresource_metadata}
        WHERE
            $clause AND
            namespace = ?
        ORDER BY
            value
     ";

    $items = array();
    // debug_trace('localsearch : '.$sql);
    if($recs = $DB->get_records_sql($sql, array($namespace))){
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


function sharedresource_add_accessible_contexts(&$contextopts, $course = null){
	global $COURSE, $USER, $CFG, $DB;
	
	if (is_null($course)) $course = $COURSE;
	
	if ($COURSE->id != SITEID){
	
		$contextoptsrev = array();
		$ctx = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $course->category));
		$current = $DB->get_record('course_categories', array('id' => $course->category), 'id, name, parent');
		$contextoptsrev[$ctx->id] = $current->name;
		while($current->parent){
			if ($current = $DB->get_record('course_categories', array('id' => $current->parent), 'id, name, parent')){
				$ctx = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $current->id));
				$contextoptsrev[$ctx->id] = $current->name;
			}
		}
		$contextoptsrev = array_reverse($contextoptsrev, true);
		$contextopts = $contextopts + $contextoptsrev;
	} else {
		if ($cats = $DB->get_records('course_categories', array('visible' => 1), 'parent,sortorder')){
			// slow way
			foreach($cats as $cat){
				$ctx = context_coursecat::instance($cat->id);
				if ($cat->visible || has_capability('moodle/category:viewhiddencategories', $ctx)){
					$contextopts[$ctx->id] = $cat->name;
				}
			}
		}
	}	
}

/**
* get the last visible non empty section in the course.
*
*/
function sharedresource_get_course_section_to_add($courseorid){
	global $DB;

	if (!is_int($courseorid)){
		$courseid = $courseorid->id;
	} else {
		$courseid = $courseorid;
	}

	if ($sections = $DB->get_records('course_sections', array('course' => $courseid, 'visible' => 1), 'section DESC')){
		foreach($sections as $s){
			if (!empty($s->sequence)) break;
		}
	}
	return $s->section;
}

function sharedresource_print_stars($stars, $maxstars){
	global $OUTPUT;
	
	$str = '';
	
	for($i = 0 ; $i < $maxstars ; $i++){
		$icon = ($i < $stars) ? 'star' : 'star_shadow';
		$str .= '<img src="'.$OUTPUT->pix_url($icon, 'local_sharedresource').'" />';
	}

	return $str;
}
