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
 * Checks access and user's capability
 * @param object $course the course.
 * @return a context instance for the page.
 */
function sharedresource_check_access($course) {
    $systemcontext = context_system::instance();
    $coursecontext = context_course::instance($course->id);
    if ($course->id != SITEID) {
        require_login($course);
        require_capability('moodle/course:manageactivities', $coursecontext);
        return $coursecontext;
    } else {
        require_login();
        $caps = array('repository/sharedresources:create', 'repository/sharedresources:manage');
        if (!has_any_capability($caps, context_system::instance())) {
            if (!sharedresources_has_capability_somewhere('repository/sharedresources:create', false, false, false, CONTEXT_COURSECAT.','.CONTEXT_COURSE)) {
                throw new moodle_exception(get_string('noaccess'));
            }
        }
        return $systemcontext;
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
 * @param string $element the metadata element as an m_n_o_p element identifier.
 * @param string $namespace which plugin is searched for metadata
 * @param string $what if values, get a list of metadata values, else gives a list of sharedresources entries
 * @param string $using the constraint value in metadatas. Using can be a comma separated list of tokens or operator:value
 */
function sharedresource_get_by_metadata($element, $namespace = 'lom', $what = 'values', $using = '') {
    global $DB;

    // Get metadata plugin and restype element name.
    $mtdstandard = sharedresource_get_plugin($namespace);
    $mtdelement = $mtdstandard->getElement($element);

    $params = array();

    if ($what == 'values') {
        $clause = " element LIKE ? ";
        if ($mtdelement->type == 'list') {
            $params[] = $mtdelement->id.':';
        } else {
            $params[] = $mtdelement->id;
        }
        $fields = 'value';
    } else {
        if ($mtdelement->widget == 'treeselect') {
            $clause = '';
            if (preg_match('/^subs:/', $using)) {
                // Search all subpaths of the required category.
                $using = str_replace('subs:', '', $using);
                $clause = " value LIKE ? AND element LIKE ? ";
                $params[] = $using.'%';
                $params[] = $mtdelement->id.':%';
            } else {
                // Search an exact taxon idpath match.
                $clause = "  value = ? AND element LIKE ? ";
                $params[] = $using;
                $params[] = $mtdelement->id.':%';
            }

        } else if (($mtdelement->widget == 'freetext') || ($mtdelement->widget == 'text')) {

            $textoption = substr($using, 0, strpos($using, ':'));
            $using = substr($using, strpos($using, ':') + 1);
            $listsearchoptions = array();

            if (!empty($using)) {
                $listtokens = explode(',', str_replace("'", "''", $using));

                foreach ($listtokens as $token) {

                    switch ($textoption) {

                        case 'includes': {
                            $listsearchoptions[] = ' UPPER(value) LIKE ? ';
                            $params[] = '%'.strtoupper(trim($token)).'%';
                            break;
                        }

                        case 'equals': {
                            $listsearchoptions[] = ' UPPER(value) = ? ';
                            $params[] = strtoupper(trim($token));
                            break;
                        }

                        case 'beginswith': {
                            $listsearchoptions[] = ' UPPER(value) LIKE ? ';
                            $params[] = strtoupper(trim($token)).'%';
                            break;
                        }

                        case 'endswith': {
                            $listsearchoptions[] = ' UPPER(value) LIKE ? ';
                            $params[] = '%'.strtoupper(trim($token));
                            break;
                        }

                        default:
                    }
                }
                $listsearch = implode(' OR ', $listsearchoptions);
                $clause = " ( $listsearch ) AND element LIKE ? ";
                $params[] = $mtdelement->id.':%';
            } else {
                $clause = '';
            }

        } else if ($mtdelement->type == 'date') {

            $datestart = substr($using, 0, strpos($using, ':'));
            $dateend = substr($using, strpos($using, ':') + 1);
            if ($datestart != 'Begin' && $dateend != 'End') {
                $start = mktime(0, 0, 0, substr($datestart, 5, 2),  substr($datestart, 8, 2), substr($datestart, 0, 4));
                $end = mktime(0, 0, 0, substr($dateend, 5, 2),  substr($dateend, 8, 2), substr($dateend, 0, 4));
                $clause = "  value >= ? AND value <= ? AND element LIKE ? ";
                $params[] = $start;
                $params[] = $end;
                $params[] = '{$mtdelement->id}:%';
            } else if ($datestart != 'Begin') {
                $start = mktime(0, 0, 0, substr($datestart, 5, 2),  substr($datestart, 8, 2), substr($datestart, 0, 4));
                $clause = "  value >= $start AND element LIKE ? ";
                $params[] = '{$mtdelement->id}:%';
            } else if ($dateend != 'End') {
                $end = mktime(0, 0, 0, substr($dateend, 5, 2),  substr($dateend, 8, 2), substr($dateend, 0, 4));
                $clause = "  value <= ? AND element LIKE ? ";
                $params[] = $end;
                $params[] = '{$mtdelement->id}:%';
            }

        } else if ($mtdelement->type == 'numeric') {

            $symbol = substr($using, 0, strpos($using, ':'));
            $value = substr($using, strpos($using, ':') + 1);
            $clause = " value $symbol $value AND element LIKE ? ";
            $params[] = '{$mtdelement->id}:%';

        } else if ($mtdelement->type == 'duration') {

            $symbol = substr($using, 0, strpos($using, ':'));
            $value = substr($using, strpos($using, ':') + 1);
            $clause = "  value $symbol $value AND element LIKE ? ";
            $params[] = '{$mtdelement->id}:%';

        } else {

            // Case selectmultiple and select.
            if (!empty($using)) {
                $listtokens = explode(',', $using);
                foreach ($listtokens as $token) {
                    $listsearchoptions[] = ' value = ? ';
                    $params[] = trim($token);
                }
                $listsearch = implode(' OR ', $listsearchoptions);
                $clause = " ( $listsearch ) AND element LIKE ? ";
                $params[] = "{$mtdelement->id}:%";
            } else {
                $clause = '';
            }
        }
        $fields = 'entryid';
    }

    // Search in all possible sources for this metadata namespace.
    // list($insql, $params) = $DB->get_in_or_equal($mtdstandard->ALLSOURCES); // For future polystandard hypothesis.
    list($insql, $nsparams) = $DB->get_in_or_equal(array($namespace));
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

    foreach ($nsparams as $p) {
        // Add namespace params to all params.
        $params[] = $p;
    }

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

/**
 * Makes a new course module record.
 */
function sharedresource_build_cm($courseid, $section, $modulename, $shrentry, $instance = null) {
    global $DB;

    $sectionid = $DB->get_field('course_sections', 'id', array('course' => $courseid, 'section' => $section));

    // Make a new course module.
    $module = $DB->get_record('modules', array('name' => $modulename));
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

/**
 * Get physically a remote file and prepare a local draft file with it.
 * @param string $url
 */
function sharedresource_get_remote_file($url, $filename) {
    global $USER;

    $fs = get_file_storage();

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // Set it to pretty big files.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // Set it to retrieve any content type.
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Important.
    curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    if ($rawresponse = curl_exec($ch)) {
        $filename = preg_replace('/[0-9a-f]+-/i', '', basename($filename));  // Removes the unique shacode.

        $filerecord = new StdClass;
        $filerecord->contextid = context_user::instance($USER->id)->id;
        $filerecord->component = 'user';
        $filerecord->filearea = 'draft';
        $filerecord->itemid = file_get_unused_draft_itemid();
        $filerecord->filepath = '/';
        $filerecord->filename = $filename;

        $file = $fs->create_file_from_string($filerecord, $rawresponse);
    } else {
        print_error('unreachablefile', 'sharedresource');
    }

    return $file;
}

/**
 * deploys a scorm from a local or remote resource.
 * @param objectref &$shrentry
 * @param objectref &$course the course where to deploy
 * @param int $section
 * @param int $draftid If empty, the sharedresource is local and we get a new draft file from that entry. If not empty, denotes
 * a remote resource which file was remotely retrieved.
 */
function sharedresource_deploy_scorm(&$shrentry, &$course, $section, $draftid = false) {
    global $CFG, $DB, $USER;

    include_once($CFG->dirroot.'/course/modlib.php');

    $config = get_config('sharedresource');

    // Check for completion setup.
    $completion = new completion_info($course);
    if ($completion->is_enabled()) {
        $trackingdefault = COMPLETION_TRACKING_NONE;
        // If system and activity default is on, set it.
        $defaultcompletion = plugin_supports('mod', 'scorm', FEATURE_MODEDIT_DEFAULT_COMPLETION, true);
        if ($CFG->completiondefault && $defaultcompletion) {
            $trackingdefault = COMPLETION_TRACKING_MANUAL;
        }

        $completionview = false;
        if (plugin_supports('mod', 'scorm', FEATURE_COMPLETION_TRACKS_VIEWS, false)) {
            $trackingdefault = COMPLETION_TRACKING_AUTOMATIC;
            $completionview = false;
        }
    }

    $moduleinfo = new StdClass;
    $moduleinfo->modulename = 'scorm';
    $moduleinfo->module = $DB->get_field('modules', 'id', array('name' => 'scorm'));
    $moduleinfo->visible = true;
    $moduleinfo->visibleoncoursepage = true;
    $moduleinfo->cmidnumber = '';
    $moduleinfo->section = $section;
    $moduleinfo->sr = $section;
    $moduleinfo->groupmode = $course->groupmode;
    $moduleinfo->groupingid = $course->defaultgroupingid;
    $moduleinfo->completion = $trackingdefault;
    $moduleinfo->completionview = $completionview;

    $moduleinfo->name = $shrentry->title;
    $moduleinfo->intro = $shrentry->description;

    $moduleinfo->scormtype = $config->scormintegration;
    switch ($config->scormintegration) {
        case SCORM_TYPE_LOCAL : {

            if (empty($draftid)) {
                // We need fake a draft area at it would come back from form.
                $fs = get_file_storage();

                $scormfile = $fs->get_file_by_id($shrentry->file);

                $draftid = file_get_unused_draft_itemid();
                $filerecord = new StdClass;
                $usercontext = context_user::instance($USER->id);
                $filerecord->contextid = $usercontext->id;
                $filerecord->component = 'user';
                $filerecord->filearea = 'draft';
                $filerecord->itemid = $draftid;
                $filerecord->filepath = '/';
                $filerecord->filename = $scormfile->get_filename();

                $fs->delete_area_files($filerecord->contextid, 'user', 'draft', $draftid);
                $draftfile = $fs->create_file_from_storedfile($filerecord, $shrentry->file);
            }
            $moduleinfo->packagefile = $draftid;
            break;
        }

        default :
            // All other cases.
            $moduleinfo->packageurl = $shrentry->url;
    }

    $moduleinfo->width = 100;
    $moduleinfo->height = 100;

    /*
     * We cannot use directly scorm_add_intance() as it needs the coursemodule preexists
     * the instance, but we can simulate a course add_moduleinfo call.
     */
    $moduleinfo = add_moduleinfo($moduleinfo, $course, null);
    $cm = $DB->get_record('course_modules', array('id' => $moduleinfo->coursemodule));
    $instance = $DB->get_record('scorm', array('id' => $moduleinfo->instance));
    $modulename = 'scorm';

    if ($course->format == 'page') {
        require_once($CFG->dirroot.'/course/format/page/classes/page.class.php');
        require_once($CFG->dirroot.'/course/format/page/lib.php');
        $coursepage = \format\page\course_page::get_current_page($course->id);
        $coursepage->add_cm_to_page($cm->id);
    }

    return array($cm, $instance, $modulename);
}

function sharedresource_deploy_lti($shrentry, $courseid, $section, $url) {
    global $CFG, $OUTPUT;

    // We build an LTI Tool instance.
    include_once($CFG->dirroot.'/mod/sharedresource/forms/lti_mod_form.php');
    include_once($CFG->dirroot.'/mod/lti/lib.php');

    $instance = new StdClass();
    $instance->name = $shrentry->title;
    $instance->intro = $shrentry->description;
    $instance->introformat = FORMAT_MOODLE;
    $time = time();
    $instance->timecreated = $time;
    $instance->timemodified = $time;
    $instance->typeid = 0;
    if (preg_match('#^https://#', $shrentry->url)) {
        $instance->toolurl = '';
        $instance->securetoolurl = $shrentry->url;
    } else {
        $instance->toolurl = $shrentry->url;
        $instance->securetoolurl = '';
    }
    $instance->instructorchoicesendname = 1; // Default lti form value.
    $instance->instructorchoicesendemailaddr = 1;
    $instance->instructorchoiceallowroster = 1;
    $instance->instructorchoiceallowsetting = 1;
    $instance->instructorcustomparameters = '';
    $instance->instructorchoiceacceptgrades = 1;
    $instance->grade = 0;
    $instance->launchcontainer = LTI_LAUNCH_CONTAINER_DEFAULT;
    $instance->resourcekey = ''; // Client identification key for remote service.
    $instance->password = ''; // Server password for accessing the service.
    $instance->debuglaunch = 0;
    $instance->showtitlelaunch = 0;
    $instance->showdescriptionlaunch = 0;
    $instance->servicesalt = ''; // Unique salt autocalculated.
    $instance->icon = '';
    $instance->secureicon = '';
    $instance->coursemodule = ''; // New module.

    $mform = new lti_mod_form();
    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
    }
    if ($data = $mform->get_data()) {
        $intancearr = (array)$instance;
        $data->intro = $data->introeditor['text'];
        $data->introformat = $data->introeditor['format'];

        // Report changes from form.
        foreach (array_keys($intancearr) as $key) {
            if (isset($data->$key)) {
                $instance->$key = $data->$key;
            }
        }
        $instance->course = $courseid;
        $instance->id = lti_add_instance($instance, null);
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('addltiinstall', 'sharedresource'));
        $instance->identifier = $shrentry->identifier;
        $instance->mode = 'ltiinstall';
        $instance->id = $courseid;
        $instance->section = $section;
        $instance->title = $shrentry->title;
        $instance->description = $shrentry->description;
        $instance->provider = $shrentry->provider;

        $mform->set_data($instance);
        $mform->display();
        echo $OUTPUT->footer();
        die;
    }

    return $instance;
}

/**
 * Deploys a mplayer instance using best fit settings. If the resource is remote, forces to
 * flowplayer technology. If the resource is a youtube url, forces to jwplayer < 8 with youtube support.
 * @param object $shrentry
 * @param int $courseid
 * @param bool $isremote
 * @return completed instance record with insert id
 */
function sharedresource_add_mplayer($shrentry, $courseid, $isremote = false) {
    global $CFG;

    $config = get_config('mplayer');

    // We build a MPlayer instance.
    include_once($CFG->dirroot.'/mod/mplayer/lib.php');

    $instance = new StdClass();
    // General instance attributes.
    $instance->name = $shrentry->title;
    $instance->intro = $shrentry->description;
    $instance->introformat = FORMAT_MOODLE;
    $time = time();
    $instance->timecreated = $time;
    $instance->timemodified = $time;

    $instance->type = 'url'; // Common to both technologies, jwplayer and flowplayer.
    $instance->external = $shrentry->url;

    // Determine best fit technology.
    $instance->technology = $config->default_player;
    if ($isremote) {
        // JW player external url does'nt work well with foreign urls.
        $instance->technology = 'flowlayer';
    }

    if (preg_match('/youtube/', $shrentry->url)) {
        $instance->technology = 'jw712';
        $instance->type = 'url'; // Common to both technologies, jwplayer and flowplayer.
    }

    // Specific instance attributes and assets.
    $instance->width = $config->default_width;
    $instance->height = $config->default_height;
    $instance->controlbar = $config->default_controlbar;
    $instance->frontcolor = $config->default_frontcolor;
    $instance->backcolor = $config->default_backcolor;
    $instance->lightcolor = $config->default_lightcolor;
    $instance->screencolor = $config->default_screencolor;
    $instance->autostart = $config->default_autostart;
    $instance->fullscreen = $config->default_fullscreen;
    $instance->streching = $config->default_stretching;

    $instance->coursemodule = ''; // New module.

    $instance->course = $courseid;
    $instance->id = mplayer_add_instance($instance, null);

    return $instance;
}

/**
 * deploys a moodle activity backup using the activity publisher.
 * @param objectref &$shrentry
 * @param objectref &$course the course where to deploy
 * @param int $section
 * @param int $draftfile If empty, the sharedresource is local and we get the file from that entry. If not empty, denotes
 * a remote resource which file was remotely retrieved as a new draft.
 */
function sharedresource_deploy_activity(&$shrentry, &$course, $section, $draftfile) {
    global $CFG;

    include_once($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php');

    $fs = get_file_storage();

    if (empty($draftid)) {
        // Internal resource.
        $draftfile = $fs->get_file_by_id($shrentry->file);
    } else {
        // Resource is remote and has been localized as a draft temporary file.
        assert(1);
    }
    activity_publisher::restore_single_module($course->id, $draftfile);
}

/**
 * Tries to find a suitable activity or course icon for a sharedresource entry mapping an MBZ archive.
 */
function sharedresource_extract_activity_icon(&$resourcedesc) {
    global $OUTPUT;

    if (preg_match('/-activity-(\d+)-/', $resourcedesc['file_filename'], $matches)) {
        // Extract item id.
        $itemid = $matches[1];

        if (preg_match("/-activity-(\d+)-([a-z]+)/", $resourcedesc['file_filename'], $matches)) {
            $modulename = $matches[2];
            $resourcedesc['mpduletype'] = $modulename;
            $iconurl = $OUTPUT->image_url('icon', 'mod_'.$modulename);
        }
    }

    if (empty($iconurl)) {
        $iconurl = $OUTPUT->image_url('moodlebackup', 'sharedresource');
    }

    return ''.$iconurl;
}