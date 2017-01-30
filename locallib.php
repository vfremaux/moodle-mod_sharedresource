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

require_once($CFG->dirroot.'/mod/sharedresource/sharedresource_entry.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');

/**
 * loads the usable version of plugins local strings
 * @param array $string
 * @param string $lang
 * @todo can be optimized for not loading unused strings elsewhere than in admin
 * setting forms. 
 */
function sharedresource_load_plugin_lang(&$string, $lang = '') {
    global $CFG;
    
    if ($lang == '') {
        $lang = current_language();
    }

    if ($plugin = @$CFG->pluginchoice) {
        $pluginlangfile = $CFG->dirroot.'/mod/sharedresource/plugins/'.$plugin.'/lang/'.$lang.'/'.$plugin.'.php';
        include($pluginlangfile);
    }
}

/**
 * loads the minimal strings for plugin management
 * @param array $string
 * @param string $lang
 * @todo can be optimized for not loading unused strings elsewhere than in admin
 * setting forms. 
 */
function sharedresource_load_pluginsmin_lang(&$string, $lang = '') {
    global $CFG;

    if ($lang == '') {
        $lang = current_language();
    }
    
    $plugins = get_list_of_plugins('mod/sharedresource/plugins');
    foreach ($plugins as $plugin) {
        $pluginlang = $CFG->dirroot."/mod/sharedresource/plugins/{$plugin}/lang/".$lang."/{$plugin}-min.php";
        include($pluginlang);
    }
}

/**
 * converts a given resource into sharedresource, activating required indexing plugins
 * @param object $resource
 * @param string $type a resource module type, can be 'resource' or 'url'
 */
function sharedresource_convertto(&$resource, $type = 'resource') {
    global $CFG, $DB;

    if (!$module = $DB->get_record('modules', array('name' => 'sharedresource'))) {
        print_error('errornotinstalled', 'sharedresource');
    }

    // First get the course module the resource is attached to.
    $cm = get_coursemodule_from_instance($type, $resource->id, $resource->course);
    $context = context_module::instance($cm->id);

    // Make a sharedresource_entry record.
    $sharedresource_entry = new sharedresource_entry(false); 
    $sharedresource_entry->title = $resource->name;
    $sharedresource_entry->description = $resource->intro;
    $sharedresource_entry->keywords = '';
    $sharedresource_entry->type = 'file';
    if ($type == 'url'){
        // External url case do not process file records.
        $sharedresource_entry->url = $resource->externalurl;
        $sharedresource_entry->file = '';
        $sharedresource_entry->identifier = sha1($sharedresource_entry->url);
    } else {
        // We convert filestorage reference just moving the records.
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

    if (!$DB->record_exists('sharedresource_entry', array('identifier' => $sharedresource_entry->identifier))) {
        $sharedresource_entry->add_instance();
    }

    // Move the physical resource (made by add_instance() above).

    // Give some trace.
    if (debugging()){
        echo "Constructed resource entry : {$sharedresource_entry->identifier}<br/>";
    }
    
    // Attach the new resource to a course module.
    $sharedresource = new sharedresource_base(0, $sharedresource_entry->identifier);
    $sharedresource->options = $resource->displayoptions;
    $sharedresource->popup = ''; // No more used.
    $sharedresource->type = 'file'; // Useless but backcompatibility tracking.
    $sharedresource->identifier = $sharedresource_entry->identifier;
    $sharedresource->cm = $cm->id;
    $sharedresource->name = $resource->name;
    $sharedresource->course = $resource->course;
    $sharedresource->intro = $resource->intro;
    $sharedresource->introformat = $resource->introformat;
    $sharedresource->alltext = '';
    if (!$sharedresourceid = $sharedresource->add_instance($sharedresource)) {
        print_error('erroraddinstance', 'sharedresource');
    }

    // Rebind the existing module to the new sharedresource.
    $cm->instance = $sharedresourceid;
    $cm->module = $module->id;

    // Remoteid was obtained by $sharedresource_entry->add_instance() plugin hooking !!
    $cm->idnumber = @$sharedresource_entry->remoteid;
    if (!$DB->update_record('course_modules', $cm)) {
        print_error('errorupdatecm', 'sharedresource');
    }

    // Discard the old resource or url.
    $DB->delete_records($type, array('id' => $resource->id));

    // Cleanup logs and anything that points to this resource...
}

/**
 * back converts a given sharedresource into independant local resource.
 * This WILL NOT remove the shared resource from repository
 * @param reference $sharedresource
 */
function sharedresource_convertfrom(&$sharedresource) {
    global $CFG, $DB;

    if (!$sharedmodule = $DB->get_record('modules', array('name' => 'sharedresource'))) {
        print_error('errornotinstalled', 'sharedresource');
    }
    $module = $DB->get_record('modules', array('name' => 'resource'));

    // Get the sharedresource_entry that is represented by the sharedresource.
    if (!$sharedresource_entry = $DB->get_record('sharedresource_entry', array('identifier' => $sharedresource->identifier))) {
        print_error('errorinvalididentifier', 'sharedresource');
    }

    // Calculate physical locations and reference.
    if (!empty($sharedresource_entry->file)) {
        $module = $DB->get_record('modules', array('name' => 'resource'));

        // Complete and add a resource record.
        $instance = new StdClass;
        $instance->course = $sharedresource->course;
        $instance->name = $sharedresource->name;
        $instance->intro = $sharedresource->description;
        $instance->introformat = FORMAT_MOODLE;
        $instance->tobemigrated = 0;
        $instance->legacyfiles = 0;
        $instance->legacyfileslast = null;
        $instance->display = 0;
        $instance->displayoptions = $sharedresource->options;
        $instance->filterfiles = 0;
        $instance->revision = 1;
        $instance->timemodified = time();
    
        $instance->id = $DB->insert_record('resource', $instance);
    } else {
        $module = $DB->get_record('modules', array('name' => 'url'));
        $instance = new StdClass();
        $instance->name = $sharedresource->name;
        $instance->intro = $sharedresource->description;
        $instance->introformat = FORMAT_MOODLE;
        $instance->externalurl = $sharedresource_entry->url;
        $instance->display = 0;
        $instance->displayoptions = $sharedresource->options;
        $instance->parameters = '';
        $instance->timemodified = time();
    
        $instance->id = $DB->insert_record('resource', $instance);
    }
    
    // Rebind the existing course_module if exists to the new resource.
    if ($cm = get_coursemodule_from_instance('sharedresource', $sharedresource->id, $sharedresource->course)) {
        $cm->instance = $instance->id;
        $cm->module = $module->id;
        if (!$DB->update_record('course_modules', $cm)) {
            print_error('errorupdatecm', 'sharedresource');
        }
    }

    print_string('localizeadvice', 'sharedresource');

    // Duplicate files record and relocate filearea to resource.
    if (!empty($sharedresource_entry->file)) {
        $fs = get_file_storage();
        $filerecord = $fs->get_file_by_id($sharedresource_entry->file);
        $newfile = new StdClass;
        $newfile->component = 'mod_resource';
        $newfile->filearea = 'content';
        $newfile->itemid = 0;
        $newfile->contextid = context_module::instance($cm->id)->id;
        $newfile = $fs->create_file_from_storedfile($newfile, $filerecord);
    }

    // Discard the old sharedresource module.
    $DB->delete_records('sharedresource', array('id' => $sharedresource->id));

    // Original resource stays in repository.
    // TODO : examinate librarian case that may want to fully discard the resource.
    // TODO : cleanup logs and anything that points to this sharedresource...

    return $instance->id;
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
    global $CFG, $DB;

    // Get metadata plugin and restype element name.
    include_once $CFG->dirroot.'/mod/sharedresource/plugins/'.$namespace.'/plugin.class.php';
    $classname = "sharedresource_plugin_{$namespace}";
    $mtdplugin = new $classname();
    $mtdelement = $mtdplugin->getElement($element);
    if ($what == 'values') {
        $clause = ($mtdelement->type == 'list') ? " element LIKE '{$mtdelement->id}:' " : " element = '{$mtdelement->id}' ";
        $fields = 'value';
    } else {
        if ($mtdelement->type == 'freetext') {
            $textoption = substr($using, 0, strpos($using, ':'));
            $using = substr($using, strpos($using,':') + 1);
            if (!empty($using)) {
                $listtokens = explode(',', $using);
                foreach ($listtokens as $token) {
                    switch ($textoption) {
                        case 'includes':
                        $listsearchoptions[] = ' value LIKE \'%'.trim($token).'%\' ';
                        break;
                        case 'equals':
                        $listsearchoptions[] = ' value = \''.trim($token).'\' ';
                        break;
                        case 'beginswith':
                        $listsearchoptions[] = ' value LIKE \''.trim($token).'%\' ';
                        break;
                        case 'endswith':
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
        } elseif($mtdelement->type == 'date') {
            $datestart = substr($using, 0, strpos($using,':'));
            $dateend = substr($using, strpos($using,':') + 1);
            if ($datestart != 'Begin' && $dateend != 'End') {
                $start = mktime(0, 0, 0, substr($datestart, 5, 2),  substr($datestart, 8, 2), substr($datestart, 0, 4));
                $end = mktime(0, 0, 0, substr($dateend, 5, 2),  substr($dateend, 8, 2), substr($dateend, 0, 4));
                $clause = "  value >= $start AND value <= $end AND element LIKE '{$mtdelement->id}:%' ";
            } elseif($datestart != 'Begin') {
                $start = mktime(0, 0, 0, substr($datestart, 5, 2),  substr($datestart, 8, 2), substr($datestart, 0, 4));
                $clause = "  value >= $start AND element LIKE '{$mtdelement->id}:%' ";
            } elseif($dateend != 'End') {
                $end = mktime(0, 0, 0, substr($dateend, 5, 2),  substr($dateend, 8, 2), substr($dateend, 0, 4));
                $clause = "  value <= $end AND element LIKE '{$mtdelement->id}:%' ";
            }
        } elseif($mtdelement->type == 'numeric') {
            $symbol = substr($using, 0, strpos($using,':'));
            $value = substr($using, strpos($using,':') + 1);
            $clause = "  value $symbol $value AND element LIKE '{$mtdelement->id}:%' ";
        } elseif($mtdelement->type == 'duration') {
            $symbol = substr($using, 0, strpos($using,':'));
            $value = substr($using, strpos($using,':') + 1);
            $clause = "  value $symbol $value AND element LIKE '{$mtdelement->id}:%' ";
        } elseif($mtdelement->type == 'treeselect') {
            $clause = "  value LIKE '{$using}%' AND element LIKE '{$mtdelement->id}:%' ";
        } else {
            // Case selectmultiple and select.
            if (!empty($using)) {
                $listtokens = explode(',', $using);
                foreach($listtokens as $token) {
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

    // Search in all possible sources for this metadata namespace.
    list($insql, $params) = $DB->get_in_or_equal($mtdplugin->ALLSOURCES);
    $sql = "
        SELECT DISTINCT
            $fields
        FROM
            {sharedresource_metadata}
        WHERE
            $clause AND
            namespace {$insql}
        ORDER BY
            value
     ";

    $items = array();
    // debug_trace('localsearch : '.$sql);
    if ($recs = $DB->get_records_sql($sql, $params)) {
        foreach ($recs as $rec) {
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

// Prepare autoloader of missing search widgets
ini_set('unserialize_callback_func', 'resources_load_searchwidgets');

function sharedresource_add_accessible_contexts(&$contextopts, $course = null) {
    global $COURSE, $USER, $CFG, $DB;

    if (is_null($course)) {
        $course = $COURSE;
    }

    if ($COURSE->id != SITEID) {
        $contextoptsrev = array();
        $ctx = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $course->category));
        $current = $DB->get_record('course_categories', array('id' => $course->category), 'id, name, parent');
        $contextoptsrev[$ctx->id] = $current->name;
        while ($current->parent) {
            if ($current = $DB->get_record('course_categories', array('id' => $current->parent), 'id, name, parent')) {
                $ctx = $DB->get_record('context', array('contextlevel' => CONTEXT_COURSECAT, 'instanceid' => $current->id));
                $contextoptsrev[$ctx->id] = $current->name;
            }
        }
        $contextoptsrev = array_reverse($contextoptsrev, true);
        $contextopts = $contextopts + $contextoptsrev;
    } else {
        if ($cats = $DB->get_records('course_categories', array('visible' => 1), 'parent,sortorder')) {
            // Slow way.
            foreach($cats as $cat) {
                $ctx = context_coursecat::instance($cat->id);
                if ($cat->visible || has_capability('moodle/category:viewhiddencategories', $ctx)) {
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
function sharedresource_get_course_section_to_add($courseorid) {
    global $DB;

    if (!is_int($courseorid)) {
        $courseid = $courseorid->id;
    } else {
        $courseid = $courseorid;
    }

    if ($sections = $DB->get_records('course_sections', array('course' => $courseid, 'visible' => 1), 'section DESC')) {
        foreach($sections as $s) {
            if (!empty($s->sequence)) {
                break;
            }
        }
    }
    return $s->section;
}
