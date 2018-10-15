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
 * @author  Valery Fremaux  valery.fremaux@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package    mod_sharedresource
 * @category   mod
 */
defined('MOODLE_INTERNAL') || die();

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

    $config = get_config('sharedresource');

    if ($lang == '') {
        $lang = current_language();
    }

    if ($plugin = @$config->schema) {
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
    global $DB;

    $report = '';

    if (!$module = $DB->get_record('modules', array('name' => 'sharedresource'))) {
        print_error('errornotinstalled', 'sharedresource');
    }

    // First get the course module the resource is attached to.
    $cm = get_coursemodule_from_instance($type, $resource->id, $resource->course);

    $context = context_module::instance($cm->id);

    // Make a sharedresource_entry record.
    $entryclass = \mod_sharedresource\entry_factory::get_entry_class();
    $shrentry = new $entryclass(null, null);
    $shrentry->title = $resource->name;
    $shrentry->description = $resource->intro;
    $shrentry->keywords = '';
    $shrentry->type = 'file';
    $shrentry->file = 0; // Not knwown yet.
    if ($type == 'url') {
        // External url case do not process file records.
        $shrentry->url = $resource->externalurl;
        $shrentry->file = '';
        $shrentry->identifier = sha1($shrentry->url);
    } else {
        // We convert filestorage reference just moving the records.
        $fs = get_file_storage();
        $resourcefiles = $fs->get_area_files($context->id, 'mod_resource', 'content', 0);
        $storedfile = array_pop($resourcefiles);

        $shrentry->identifier = $storedfile->get_contenthash();
        $shrentry->url = new moodle_url('/mod/sharedresource/view.php', array('identifier' => $storedfile->get_contenthash()));
    }

    if (!$DB->record_exists('sharedresource_entry', array('identifier' => $shrentry->identifier))) {
        $shrentry->add_instance();
    }

    // Move the physical resource (made by add_instance() above).

    // Give some trace.
    if (debugging()) {
        $report .= "Constructed resource entry : {$shrentry->identifier}\n";
    }

    // Attach the new resource to a sharedresource instance.
    if (debugging()) {
        $report .= "Making new instance of sharedresource...\n";
    }
    $sharedresource = new \mod_sharedresource\base(0, $shrentry->identifier);
    $sharedresource->options = $resource->displayoptions;
    $sharedresource->popup = ''; // No more used.
    $sharedresource->type = 'file'; // Useless but backcompatibility tracking.
    $sharedresource->identifier = $shrentry->identifier;
    $sharedresource->cm = $cm->id;
    $sharedresource->name = $resource->name;
    $sharedresource->course = $resource->course;
    $sharedresource->intro = $resource->intro;
    $sharedresource->introformat = $resource->introformat;
    $sharedresource->alltext = '';
    if (!$sharedresourceid = $sharedresource->add_instance($sharedresource)) {
        print_error('erroraddinstance', 'sharedresource');
    }

    // If type is 'file', move the physical storage, now we have an instance id.
    if ($type != 'url') {
        $newfile = new StdClass;
        $newfile->contextid = context_system::instance()->id;
        $newfile->component = 'mod_sharedresource';
        $newfile->filearea = 'sharedresource';
        $newfile->itemid = $shrentry->id;
        // Cleanup eventual older file in the way.
        $fs->delete_area_files($newfile->contextid, $newfile->component, $newfile->filearea, $newfile->itemid);

        $finalrec = $fs->create_file_from_storedfile($newfile, $storedfile);
        $DB->set_field('sharedresource_entry', 'file', $finalrec->get_id(), array('id' => $shrentry->id));
    }

    // Rebind the existing module to the new sharedresource.
    if (debugging()) {
        $report .= "Bind course module $cm->id to new instance\n";
    }
    $cm->instance = $sharedresourceid;
    $cm->module = $module->id;
    // Remoteid was obtained by $shrentry->add_instance() plugin hooking !!
    $cm->idnumber = @$shrentry->remoteid;
    $DB->update_record('course_modules', $cm);

    // Discard the old resource or url.
    if (debugging()) {
        $report .= "Delete old instance $resource->id of $type ";
    }
    $DB->delete_records($type, array('id' => $resource->id));

    // Cleanup logs and anything that points to this resource...

    return $report;
}

/**
 * back converts a given sharedresource into independant local resource.
 * This WILL NOT remove the shared resource from repository
 * @param reference $sharedresource
 */
function sharedresource_convertfrom(&$sharedresource, &$report) {
    global $DB;

    $report = '';

    if (!$sharedmodule = $DB->get_record('modules', array('name' => 'sharedresource'))) {
        print_error('errornotinstalled', 'sharedresource');
    }
    $module = $DB->get_record('modules', array('name' => 'resource'));

    // Get the sharedresource_entry that is represented by the sharedresource.
    if (!$shrentry = $DB->get_record('sharedresource_entry', array('identifier' => $sharedresource->identifier))) {
        print_error('errorinvalididentifier', 'sharedresource');
    }

    // Calculate physical locations and reference.
    if (!empty($shrentry->file)) {
        $module = $DB->get_record('modules', array('name' => 'resource'));

        // Complete and add a resource record.
        if (debugging()) {
            $report .= "Building a resource instance... \n";
        }
        $instance = new StdClass;
        $instance->course = $sharedresource->course;
        $instance->name = $sharedresource->name;
        $instance->intro = $sharedresource->intro;
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
        if (debugging()) {
            $report .= "Building an url instance... \n";
        }
        $module = $DB->get_record('modules', array('name' => 'url'));
        $instance = new StdClass();
        $instance->name = $sharedresource->name;
        $instance->intro = $sharedresource->intro;
        $instance->introformat = FORMAT_MOODLE;
        $instance->externalurl = $shrentry->url;
        $instance->display = 0;
        $instance->displayoptions = $sharedresource->options;
        $instance->parameters = '';
        $instance->timemodified = time();

        $instance->id = $DB->insert_record('url', $instance);
    }

    // Rebind the existing course_module if exists to the new resource.
    if (debugging()) {
        $report .= "Binding cm to new instance... \n";
    }
    if ($cm = get_coursemodule_from_instance('sharedresource', $sharedresource->id, $sharedresource->course)) {
        $cm->instance = $instance->id;
        $cm->module = $module->id;
        $DB->update_record('course_modules', $cm);
    }

    $report .= get_string('localizeadvice', 'sharedresource');

    // Duplicate files record and relocate filearea to resource.
    if (debugging()) {
        $report .= "Duplicates file storage... \n";
    }
    if (!empty($shrentry->file)) {
        $fs = get_file_storage();
        $filerecord = $fs->get_file_by_id($shrentry->file);
        $newfile = new StdClass;
        $newfile->component = 'mod_resource';
        $newfile->filearea = 'content';
        $newfile->itemid = 0;
        $newfile->contextid = context_module::instance($cm->id)->id;
        $newfile = $fs->create_file_from_storedfile($newfile, $filerecord);
    }

    // Discard the old sharedresource module.
    if (debugging()) {
        $report .= "Remove old sharedresource module... \n";
    }
    $DB->delete_records('sharedresource', array('id' => $sharedresource->id));

    // Original resource stays in repository.
    // TODO : examinate librarian case that may want to fully discard the resource.
    // TODO : cleanup logs and anything that points to this sharedresource...

    return $instance->id;
}

/**
 * Used by : provider interface (worker function)
 * retrieve the remote categorisation of resources using LOM taxonomy or any local strategy
 *
 * Get either entries that match a single metadata element value, or retrieves all values present for 
 * a single metadata element.
 *
 * @param string $element the metadata element
 * @param string $namespace which plugin is searched for metadata
 * @param string $what if values, get a list of metadata values, else gives a list of sharedresources entries
 * @param string $using the constraint value in metadatas. Using can be a comma separated list of tokens or operator:value
 */
function sharedresource_get_by_metadata($element, $namespace = 'lom', $what = 'values', $using = '') {
    global $CFG, $DB;

    // Get metadata plugin and restype element name.
    $mtdstandard = sharedresource_get_plugin($namespace);
    $mtdelement = $mtdstandard->getElement($element);

    if ($what == 'values') {
        $clause = ($mtdelement->type == 'list') ? " element LIKE '{$mtdelement->id}:' " : " element = '{$mtdelement->id}' ";
        $fields = 'value';
    } else {
        if ($mtdelement->widget == 'treeselect') {
            $clause = '';
            if (preg_match('/^subs:/', $using)) {
                // Search all subpaths of the required category.
                $using = str_replace('subs:', '', $using);
                $clause = "  value LIKE '{$using}%' AND element LIKE '{$mtdelement->id}:%' ";
            } else {
                // Search an exact taxon idpath match.
                $clause = "  value = '{$using}' AND element LIKE '{$mtdelement->id}:%' ";
            }

        } else if ($mtdelement->type == 'freetext' || $mtdelement->type == 'text') {

            $textoption = substr($using, 0, strpos($using, ':'));
            $using = substr($using, strpos($using,':') + 1);
            $listsearchoptions = array();

            if (!empty($using)) {
                $listtokens = explode(',', str_replace("'", "''", $using));

                foreach ($listtokens as $token) {

                    switch ($textoption) {

                        case 'includes': {
                            $listsearchoptions[] = ' UPPER(value) LIKE \'%'.strtoupper(trim($token)).'%\' ';
                            break;
                        }

                        case 'equals': {
                            $listsearchoptions[] = ' UPPER(value) = \''.strtoupper(trim($token)).'\' ';
                            break;
                        }

                        case 'beginswith': {
                            $listsearchoptions[] = ' UPPER(value) LIKE \''.strtoupper(trim($token)).'%\' ';
                            break;
                        }

                        case 'endswith': {
                            $listsearchoptions[] = ' UPPER(value) LIKE \'%'.strtoupper(trim($token)).'\' ';
                            break;
                        }

                        default:
                    }
                }
                $listsearch = implode(' OR ', $listsearchoptions);
                $clause = " ( $listsearch ) AND element LIKE '{$mtdelement->id}:%' ";
            } else {
                $clause = '';
            }

        } else if ($mtdelement->type == 'date') {

            $datestart = substr($using, 0, strpos($using,':'));
            $dateend = substr($using, strpos($using,':') + 1);
            if ($datestart != 'Begin' && $dateend != 'End') {
                $start = mktime(0, 0, 0, substr($datestart, 5, 2),  substr($datestart, 8, 2), substr($datestart, 0, 4));
                $end = mktime(0, 0, 0, substr($dateend, 5, 2),  substr($dateend, 8, 2), substr($dateend, 0, 4));
                $clause = "  value >= $start AND value <= $end AND element LIKE '{$mtdelement->id}:%' ";
            } else if ($datestart != 'Begin') {
                $start = mktime(0, 0, 0, substr($datestart, 5, 2),  substr($datestart, 8, 2), substr($datestart, 0, 4));
                $clause = "  value >= $start AND element LIKE '{$mtdelement->id}:%' ";
            } else if ($dateend != 'End') {
                $end = mktime(0, 0, 0, substr($dateend, 5, 2),  substr($dateend, 8, 2), substr($dateend, 0, 4));
                $clause = "  value <= $end AND element LIKE '{$mtdelement->id}:%' ";
            }

        } else if ($mtdelement->type == 'numeric') {

            $symbol = substr($using, 0, strpos($using,':'));
            $value = substr($using, strpos($using,':') + 1);
            $clause = "  value $symbol $value AND element LIKE '{$mtdelement->id}:%' ";

        } else if ($mtdelement->type == 'duration') {

            $symbol = substr($using, 0, strpos($using,':'));
            $value = substr($using, strpos($using,':') + 1);
            $clause = "  value $symbol $value AND element LIKE '{$mtdelement->id}:%' ";

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
        $fields = 'entryid';
    }

    // Search in all possible sources for this metadata namespace.
    // list($insql, $params) = $DB->get_in_or_equal($mtdstandard->ALLSOURCES); // For future polystandard hypothesis.
    list($insql, $params) = $DB->get_in_or_equal(array($namespace));
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

function sharedresource_add_accessible_contexts(&$contextopts, $course = null) {
    global $COURSE, $DB;

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
            foreach ($cats as $cat) {
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
        foreach ($sections as $s) {
            if (!empty($s->sequence)) {
                break;
            }
        }
    }
    return $s->section;
}

function sharedresource_clean_field($field) {
    switch ($field) {
        case 'identifier': {
            $value = optional_param($field, '', PARAM_BASE64);
            break;
        }

        case 'file': {
            $value = optional_param($field, '', PARAM_PATH);
            break;
        }

        case 'mimetype': {
            $value = optional_param($field, '', PARAM_URL);
            break;
        }

        default:
            $value = optional_param($field, '', PARAM_RAW);
    }
    return $value;
}

function sharedresource_build_cm($courseid, $section, $modulename, $shrentry, $instance = null) {
    $sectionid = $DB->get_field('course_sections', 'id', array('course' => $courseid, 'section' => $section));

    // Make a new course module.
    $module = $DB->get_record('modules', array('name'=> $modulename));
    $cm = new StdClass;
    if (!empty($instance)) {
        $cm->instance = $instance->id;
    }
    $cm->module = $module->id;
    $cm->course = $courseid;
    $cm->section = $sectionid;

    // Remoteid may be obtained by $shrentry->add_instance() plugin hooking !!
    // Valid also if LTI tool.
    if (!empty($shrentry->remoteid)) {
        $cm->idnumber = $shrentry->remoteid;
    }

    // Insert the course module in course.
    if (!$cm->id = add_course_module($cm)) {
        print_error('errorcmaddition', 'sharedresource');
    }

    return $cm;
}