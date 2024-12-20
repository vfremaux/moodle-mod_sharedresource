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
 * General library for sharedresource module.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/*
 * phpcs:disable moodle.Commenting.ValidTags.Invalid
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_entry_factory.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_base.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_plugin_base.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_entry.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_metadata.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');

define('SHAREDRESOURCE_LOCALPATH', 'LOCALPATH');
define('SHAREDRESOURCE_TEMPPATH', '/temp/sharedresources/');
define('SHAREDRESOURCE_RESOURCEPATH', '/sharedresources/');
define('SHAREDRESOURCE_SEARCH_LIMIT', '200');
define('SHAREDRESOURCE_RESULTS_PER_PAGE', '20');

global $shrwindowoptions;
global $shrcoreelements;
// Must be global because it might be included from a function!
global $shrmetadataelements;

$shrwindowoptions = [
    'resizable',
    'scrollbars',
    'directories',
    'location',
    'menubar',
    'toolbar',
    'status',
    'width',
    'height',
];

$shrcoreelements = [
    'id',
    'identifier',
    'title',
    'description',
    'url',
    'file',
    'type',
    'score',
    'remoteid',
    'mimetype',
    'timemodified',
];

$shrmetadataelements = [
    [
        'name' => 'Contributor',
        'datatype' => 'text',
    ],
    [
        'name' => 'IssueDate',
        'datatype' => 'lomdate',
    ],
    [
        'name' => 'TypicalAgeRange',
        'datatype' => 'lomagerange',
    ],
    [
        'name' => 'LearningResourceType',
        'datatype' => 'vocab',
    ],
    [
        'name' => 'Rights',
        'datatype' => 'yesno',
    ],
    [
        'name' => 'Format',
        'datatype' => 'text',
    ],
    [
        'name' => 'RightsDescription',
        'datatype' => 'plaintext',
    ],
    [
        'name' => 'ClassificationPurpose',
        'datatype' => 'plaintext',
    ],
    [
        'name' => 'ClassificationTaxonPath',
        'datatype' => 'vocab',
    ],
];

/**
 * List of features supported in Resource module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function sharedresource_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE: {
            return MOD_ARCHETYPE_RESOURCE;
        }
        case FEATURE_GROUPS: {
            return false;
        }
        case FEATURE_GROUPINGS: {
            return false;
        }
        case FEATURE_GROUPMEMBERSONLY: {
            return true;
        }
        case FEATURE_MOD_INTRO: {
            return true;
        }
        case FEATURE_COMPLETION_TRACKS_VIEWS: {
            return true;
        }
        case FEATURE_GRADE_HAS_GRADE: {
            return false;
        }
        case FEATURE_GRADE_OUTCOMES: {
            return false;
        }
        case FEATURE_BACKUP_MOODLE2: {
            return true;
        }
        case FEATURE_SHOW_DESCRIPTION: {
            return true;
        }

        // 4.0
        case FEATURE_MOD_PURPOSE: {
            return MOD_PURPOSE_CONTENT;
        }

        default:
            return null;
    }
}

/**
 * Implements the generic community/pro packaging switch.
 * Tells wether a feature is supported or not. Gives back the
 * implementation path where to fetch resources.
 * @param string $feature a feature key to be tested.
 * @param bool $getsupported
 */
function sharedresource_supports_feature($feature = null, $getsupported = null) {
    global $CFG;
    static $supports;

    if (!during_initial_install()) {
        $config = get_config('sharedresource');
    }

    if (!isset($supports)) {
        $supports = [
            'pro' => [
                'taxonomy' => ['accessctl', 'fineselect'],
                'entry' => ['extended', 'accessctl', 'remote', 'customicon', 'scorable', 'foreignurl'],
                'emulate' => 'community',
                'export/rss',
            ],
            'community' => [
            ],
        ];
        $prefer = [];
    }

    if ($getsupported) {
        return $supports;
    }

    // Check existance of the 'pro' dir in plugin.
    if (is_dir(__DIR__.'/pro')) {
        if ($feature == 'emulate/community') {
            return 'pro';
        }
        if (empty($config->emulatecommunity)) {
            $versionkey = 'pro';
        } else {
            $versionkey = 'community';
        }
    } else {
        $versionkey = 'community';
    }

    if (empty($feature)) {
        // Just return version.
        return $versionkey;
    }

    list($feat, $subfeat) = explode('/', $feature);

    if (!array_key_exists($feat, $supports[$versionkey])) {
        return false;
    }

    if (!in_array($subfeat, $supports[$versionkey][$feat])) {
        return false;
    }

    // Special condition for pdf dependencies.
    if (($feature == 'format/pdf') && !is_dir($CFG->dirroot.'/local/vflibs')) {
        return false;
    }

    if (array_key_exists($feat, $supports['community'])) {
        if (in_array($subfeat, $supports['community'][$feat])) {
            // If community exists, default path points community code.
            if (isset($prefer[$feat][$subfeat])) {
                // Configuration tells which location to prefer if explicit.
                $versionkey = $prefer[$feat][$subfeat];
            } else {
                $versionkey = 'community';
            }
        }
    }

    return $versionkey;
}

/**
 * Returns an object representing this metadata schema
 * @param string $pluginname the schema name.
 * @return a plugin_base descendant concrete class instance.
 */
function sharedresource_get_plugin($namespace, $entryid = 0) {
    global $CFG;
    static $mtdstandards = [];

    $config = get_config('sharedresource');

    if (!empty($config->{'plugin_hide_'.$namespace})) {
        // Discard hidden plugins in configuration.
        return null;
    }

    if (array_key_exists($namespace, $mtdstandards)) {
        return $mtdstandards[$namespace];
    }

    if (file_exists($CFG->dirroot."/mod/sharedresource/plugins/{$namespace}/plugin.class.php")) {
        require_once($CFG->dirroot."/mod/sharedresource/plugins/{$namespace}/plugin.class.php");
        $mtdclass = '\\mod_sharedresource\\plugin_'.$namespace;

        $mtdstandards[$namespace] = new $mtdclass($entryid);
        if (!empty($CFG->metadatatreedefaults)) {
            $mtdstandards[$namespace]->load_defaults($CFG->metadatatreedefaults);
        }
        return $mtdstandards[$namespace];
    }

    return null;
}

/**
 * Find active plugins, load the class files, and instantiate
 * the appropriate plugin object.
 */
function sharedresource_get_plugins($entryid = 0) {
    global $CFG;

    $plugins = [];
    $config = get_config('sharedresource');

    // Fetch all plugins.

    $sharedentryplugins = core_component::get_plugin_list('sharedmetadata');

    foreach (array_keys($sharedentryplugins) as $sharedentryplugin) {
        if (!empty($config->{'plugin_hide_'.$sharedentryplugin})) {
            // Discard hidden plugins in configuration.
            continue;
        }
        require_once($CFG->dirroot."/mod/sharedresource/plugins/{$sharedentryplugin}/plugin.class.php");
        $mtdclass = '\\mod_sharedresource\\plugin_'.$sharedentryplugin;
        $mtdstandard = new $mtdclass($entryid);
        $plugins[$sharedentryplugin] = $mtdstandard;
    }
    return $plugins;
}

/**
 * callback method from modedit.php for adding a new sharedresource instance
 * we receive a record, but need an object to call add_instance on.
 * @param object $sharedresource data comming from form
 */
function sharedresource_add_instance($sharedresource) {

    $sharedresource->type = 'file';
    $instance = new \mod_sharedresource\base();
    $instance->sharedresource = $sharedresource;
    return $instance->add_instance();
}

/**
 * callback method from modedit.php for updating a sharedresource instance
 * we receive a record, but need an object to call add_instance on.
 */
function sharedresource_update_instance($sharedresource) {

    $sharedresource->type = 'file'; // Just to be sure.
    $instance = new \mod_sharedresource\base();
    $instance->sharedresource = $sharedresource;
    return $instance->update_instance();
}

/**
 * callback method from modedit.php for deleting a sharedresource instance
 * This will NOT delete sharedresource entries in the library.
 * @param int $id
 */
function sharedresource_delete_instance($id) {
    global $DB;

    if (!$sharedresource = $DB->get_record('sharedresource', ['id' => $id])) {
        return false;
    }

    $instance = new \mod_sharedresource\base();
    $instance->sharedresource = $sharedresource;
    return $instance->delete_instance();
}

/**
 * What does this do ?
 * @param object $course
 * @param object $user
 * @param object $mod course module object
 * @param object $sharedresource instance object
 */
function sharedresource_user_outline($course, $user, $mod, $sharedresource) {
    global $DB;

    $select = "
        userid = ? AND
        module = 'sharedresource' AND
        action = 'view' AND
        info = ?
    ";

    if ($logs = $DB->get_records_select('log', $select, [$user->id, $sharedresource->id], "time ASC")) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new StdClass;
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;

        return $result;
    }
    return null;
}


/**
 * Has the user completed this activity ?
 * @param object $course
 * @param object $user
 * @param object $mod course module record.
 * @param object $sharedresource instance record.
 */
function sharedresource_user_complete($course, $user, $mod, $sharedresource) {
    global $DB;

    $select = "
        userid = ? AND
        module = 'sharedresource' AND
        action = 'view' AND
        info = ?
    ";
    if ($logs = $DB->get_records_select('log', $select, [$user->id, $sharedresource->id], 'time ASC')) {
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
 * Returns the users with data in one sharedresource.
 * Here does nothing, as sharedresources have no end user bound data.
 * @param int $sharedresourceid
 */
function sharedresource_get_participants($sharedresourceid) {
    return false;
}

/**
 * This constructs the course module information the gets used
 * in the course module list.
 * @param array $modinfo
 */
function sharedresource_cm_info_dynamic(& $modinfo) {
    global $CFG, $DB;

    $info = null;

    $fields = 'id, popup, identifier, type, name';
    if ($sharedresource = $DB->get_record('sharedresource', ['id' => $modinfo->instance], $fields)) {

        $shrentry = \mod_sharedresource\entry::read($sharedresource->identifier);
    }

    require_once($CFG->libdir.'/filelib.php');
}

/**
 * Fetch a remotely stored fil for the resource (when the resource
 * comes from a remote repository)
 * @param object $cm
 * @param string $url
 * @param string $headers
 * @todo consider turn this into a CURL call rather that Snoopy.
 */
function sharedresource_fetch_remote_file ($cm, $url, $headers = '') {
    global $CFG;

    require_once($CFG->libdir.'/snoopy/Snoopy.class.inc');

    $client = new Snoopy();
    $ua = 'Moodle/'. $CFG->release . ' (+http://moodle.org';
    if ($config->usecache) {
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
        $tags = ['A'      => 'href=',
                      'IMG'    => 'src=',
                      'LINK'   => 'href=',
                      'AREA'   => 'href=',
                      'FRAME'  => 'src=',
                      'IFRAME' => 'src=',
                      'FORM'   => 'action='];

        foreach ($tags as $tag => $key) {
            $prefix = "fetch.php?id=$cm->id&url=";
            if ( $tag == 'IMG' || $tag == 'LINK' || $tag == 'FORM') {
                $prefix = "";
            }
            $client->results = sharedresource_redirect_tags($client->results, $url, $tag, $key, $prefix);
        }
    } else {
        if ( $client->status >= 400 && $client->status < 500) {
            $client->results = get_string('fetchclienterror', 'sharedresource');  // Client error.
        } else if ( $client->status >= 500 && $client->status < 600) {
            $client->results = get_string('fetchservererror', 'sharedresource');  // Server error.
        } else {
            $client->results = get_string('fetcherror', 'sharedresource');     // Redirection? HEAD? Unknown error.
        }
    }
    return $client;
}

/**
 * Get redirect tags
 * @todo is this function still used ?
 * @param string $text
 * @param string $url
 * @param string $tagtoparse
 * @param string $keytoparse
 * @param string $prefix
 */
function sharedresource_redirect_tags($text, $url, $tagtoparse, $keytoparse, $prefix = "" ) {
    $valid = 1;
    if (strpos($url, '?') == false) {
        $valid = 1;
    }
    if ($valid) {
        $lastpoint = strrpos($url, '.');
        $lastslash = strrpos($url, '/');
        if ($lastpoint > $lastslash) {
            $root = substr($url, 0, $lastslash + 1);
        } else {
            $root = $url;
        }
        if ($root == 'http://' || $root == 'https://') {
            $root = $url;
        }
        if (substr($root, strlen($root) - 1) == '/') {
            $root = substr($root, 0, - 1);
        }

        $mainroot = $root;
        $lastslash = strrpos($mainroot, "/");
        while ($lastslash > 9) {
            $mainroot = substr($mainroot, 0, $lastslash);

            $lastslash = strrpos($mainroot, "/");
        }

        $regex = "/<$tagtoparse (.+?)>/is";
        $count = preg_match_all($regex, $text, $hrefs);
        for ($i = 0; $i < $count; $i++) {
            $tag = $hrefs[1][$i];

            $poshref = strpos(core_text::strtolower($tag), core_text::strtolower($keytoparse));
            $start = $poshref + strlen($keytoparse);
            $left = substr($tag, 0, $start);
            if ($tag[$start] == '"') {
                $left .= '"';
                $start++;
            }
            $posspace = strpos($tag, ' ', $start + 1);
            $right = '';
            if ($posspace != false) {
                $right = substr($tag, $posspace);
            }
            $end = strlen($tag) - 1;
            if ($tag[$end] == '"') {
                $right = '"' . $right;
            }
            $finalurl = substr($tag, $start, $end - $start + $diff);
            /*
             * Here, we could have these possible values for $finalurl:
             * file.ext                             Add current root dir
             * http://(domain)                      don't care
             * http://(domain)/                     don't care
             * http://(domain)/folder               don't care
             * http://(domain)/folder/              don't care
             * http://(domain)/folder/file.ext      don't care
             * folder/                              Add current root dir
             * folder/file.ext                      Add current root dir
             * /folder/                             Add main root dir
             * /folder/file.ext                     Add main root dir
             */

            // Special case: If finalurl contains a ?, it won't be parsed.
            $valid = 1;

            if (strpos($finalurl, "?") == false) {
                $valid = 1;
            }
            if ($valid) {
                if ($finalurl[0] == '/') {
                    $finalurl = $mainroot . $finalurl;
                } else if (strtolower(substr($finalurl, 0, 7)) != 'http://' &&
                           strtolower(substr($finalurl, 0, 8)) != 'https://') {
                    if ($finalurl[0] == '/') {
                        $finalurl = $mainroot . $finalurl;
                    } else {
                        $finalurl = "$root/$finalurl";
                    }
                }

                $text = str_replace($tag, "$left$prefix$finalurl$right", $text);
            }
        }
    }
    return $text;
}

/**
 * Check to see if a given URI is a URL.
 *
 * @param string $path  string, URI.
 * @return bool, true = is URL
 */
function sharedresource_is_url($path) {
    if (strpos($path, '://')) {
        // Eg http:// https:// ftp://  etc.
        return true;
    }
    if (strpos($path, '/') === 0) {
        // Starts with slash.
        return true;
    }
    return false;
}

/**
 * Get view actions
 * @todo possible obsolete, linled to old log model
 */
function sharedresource_get_view_actions() {
    return ['view', 'view all'];
}

/**
 * Get post actions
 * @todo possible obsolete, linled to old log model
 */
function sharedresource_get_post_actions() {
    return [];
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * @param array $data the data submitted from the reset course.
 * @return array status array
 */
function sharedresource_reset_userdata($data) {
    return [];
}

/**
 * Returns all other caps used in module
 *
 * @return array, of capabilities
 */
function sharedresource_get_extra_capabilities() {
    return ['moodle/site:accessallgroups'];
}

/**
 * format the URL correctly for local files
 *
 * @param object $sharedresource  actual sharedresource module instance
 * @param object $sharedresourceentry  sharedresource entry publisehd by this instance
 * @param array $options  query string parameters to be passed along
 * @return moodle_url
 */
function sharedresource_get_file_url($sharedresource, $sharedresourceentry, $options = null) {
    global $CFG;

    $fs = get_file_storage();
    $file = $fs->get_file_by_id($sharedresourceentry->file);

    if (!$file) {
        return false;
    }

    if ($sharedresource->cm) {
        $viewcontextid = context_module::instance($sharedresource->cm->id)->id;
    } else {
        $viewcontextid = $file->get_contextid(); // Should be system context.
    }

    $filepath = $file->get_component().'/'.$file->get_filearea().'/'.$file->get_itemid();
    $filepath .= $file->get_filepath().$file->get_filename();
    $ffurl = new moodle_url("/pluginfile.php/".$viewcontextid."/".$filepath);

    return $ffurl;
}

/**
 * return a 404 if a shared Resource is not found
 *
 * @param int $courseid the current context course
 * @param string $reason tell why it was not found
 */
function sharedresource_not_found($courseid = 0, $reason = '') {
    global $CFG;

    header('HTTP/1.0 404 not found');
    $url = $CFG->wwwroot;
    if ($courseid != 0) {
        $url = new moodle_url('/course/view.php', ['id' => $courseid]);
    }
    throw new moodle_exception('filenotfound', 'sharedresource', $url, $reason);
}

/**
 * Sends the files for sharedresources
 * @param object $course the course in context of the call. Can be null
 * when acceding from a system context
 * @param object $cm when call comes from a sharedresource instance. denotes the corresponding course module
 * @param object $context can be a system level context or category context level when addressed from the library, or a
 * course module context when accessed from the sharedresource instance in the course.
 * @param string $filearea
 * @param string $args other args, such as itemid, path, and name
 * @param bool $forcedownload
 */
function mod_sharedresource_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;

    $config = get_config('local_sharedresources');
    $fs = get_file_storage();

    $system = context_system::instance();

    // Find the file record.
    if ($filearea === 'sharedresource') {
        $itemid = array_shift($args);
        $relativepath = implode('/', $args);
        $fullpath = '/'.$system->id.'/mod_sharedresource/sharedresource/'.$itemid.'/'.$relativepath;
        // TODO: add any other access restrictions here if needed!

    } else if ($filearea === 'package') {
        // Packages are stored instances of activites ready for publishing.
        require_login($course);

        if (!has_capability('moodle/course:manageactivities', $context)) {
            return false;
        }
        $itemid = array_shift($args);
        $relativepath = implode('/', $args);
        $fullpath = '/'.$context->id.'/mod_scorm/package/'.$itemid.'/'.$relativepath;
        $lifetime = 0; // No caching here.

        if ((!$file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
            return false;
        }

        // Finally send the file.
        send_stored_file($file, $lifetime, 0, false);
        return;
    } else if ($filearea === 'thumbnail') {
        $itemid = array_shift($args);
        $relativepath = implode('/', $args);
        $fullpath = '/'.$system->id.'/mod_sharedresource/thumbnail/'.$itemid.'/'.$relativepath;

        if ((!$file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
            return false;
        }

        $lifetime = isset($CFG->filelifetime) ? $CFG->filelifetime : 86400;

        // Finally send the file.
        send_stored_file($file, $lifetime, 0, false);
        return;
    } else {
        // Invalid filearea.
        return false;
    }

    // Now finish with sharedresource area.
    if ((!$file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
        return false;
    }
    $sharedresourceentry = $DB->get_record('sharedresource_entry', ['file' => $file->get_id()]);

    // Control course module integrity.
    if ($filearea && !empty($cm)) {
        $itemid = $cm->instance;
        $identifier = $DB->get_field('sharedresource', 'identifier', ['id' => $cm->instance]);
        $entryfilefromcm = $DB->get_field('sharedresource_entry', 'file', ['identifier' => $identifier]);
        if ($entryfilefromcm != $file->get_id()) {
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

/**
 * This function allows the tool_dbcleaner to register integrity checks
 */
function sharedresource_dbcleaner_add_keys() {
    $keys = [
        ['sharedresource', 'course', 'course', 'id', ''],
        ['sharedresource_metadata', 'entryid', 'sharedresource_entry', 'id', ''],
        ['sharedresource_taxonomy', 'parent', 'sharedresource_taxonomy', 'id', ''],
    ];

    return $keys;
}

/**
 * For viewing resources in iframe, we need do add a base tag to frametop frame links.
 */
function sharedresource_before_standard_html_head() {
    $isframetop = optional_param('frameset', '', PARAM_ALPHA);
    if ($isframetop) {
        return '<base target="_top" />';
    }
}
