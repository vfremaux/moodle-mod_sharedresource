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
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package mod-sharedresource
 *
 */
namespace mod_sharedresource;

use \StdClass;
use \moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * shrentryrec defines a sharedresource including the metadata
 *
 * This class provides all the functionality for a sharedresource
 * defined locally or remotely
 *
 * A locally defined resource is essentially one where the user has uploaded
 * a file, therefore this local moodle has to serve it.
 *
 * A remote resource is one that has a fully qualified URI that does not rely
 * on this local Moodle instance to serve the physical data eg. PDF, PNG etc.
 *
 * mod/sharedresource uses the presence of a $shrentryrec->file attribute 
 * to determine if this resource is hosted locally (the physical file must
 * also exist in the course independent repository).
 *
 * mod/sharedresource uses a course independent file repository. Files are stored into
 * a system context, course independant, special filearea.
 *
 * TODO : see to protect some fields
 */
class entry {

    // a sharedresource entry is a combination of:

    // a DB record
    protected $shrentryrec;

    // A set of metadata
    public $metadataelements;

    // An eventual physical file stored somewhere in the Moodle filesystem
    public $storedfile;

    protected $config;

    protected $mtdstandard;

    /**
     * Internal method that processes the plugins for the search
     * interface.
     * 
     * @param criteria   object, reference to Moodle Forms populated
     *        values.
     * @return results, return an array of shrentryrec objects that
     *         will be formated and displayed in the search results screen.
     */
    static public function search (&$criteria) {
        // Get the plugins.
        $plugins = sharedresource_get_plugins();
        $results = array();

        // Process each plugins search function - there is a default called local.
        foreach ($plugins as $plugin) {
            /* If we get a positive return then we don't use any more plugins 
             * $results is passed by reference so plugins can doctor the incremental results
             */
            $plugin->search($criteria, $results);
        }
        return $results;
    }

    /**
     * Hydrate a shrentryrec object reading by identifier
     * 
     * @param identifier   hash, sha1 hash identifier
     * @return shrentryrec object
     */
    static public function read($identifier) {
        global $CFG, $DB;

        if (! $shrentryrec = $DB->get_record('sharedresource_entry', array('identifier' => $identifier))) {
            return false;
        }

        $shrentryrec = new entry($shrentryrec);
        return $shrentryrec;
    }

    /**
     * Hydrate a shrentryrec object reading by id
     * 
     * @param id   int, internal id of shrentryrec object
     * @return shrentryrec object
     */
    static public function read_by_id($entryid) {
        global  $DB;
        if (! $shrentryrec =  $DB->get_record('sharedresource_entry', array('id'=> $entryid))) {
            return false;
        }

        $shrentryrec = new entry($shrentryrec);
        return $shrentryrec;
    }

    /**
     * Same as read_by_id(). Hydrate a shrentryrec object reading by id
     * 
     * @param id   int, internal id of shrentryrec object
     * @return shrentryrec object
     */
    static public function get_by_id($entryid) {
        return entry::read_by_id($entryid);
    }

    /**
     * Same as read(). Hydrate a shrentryrec object reading by identifier
     * 
     * @param identifier   hash, sha1 hash identifier
     * @return shrentryrec object
     */
    static public function get_by_identifier($identifier) {
        return $shrentryrec = entry::read($identifier);
    }

    /**
     * Constructor for the base sharedresource class.
     * Loads metadata from DB if any.
     *
     * @param object $shrentryrec shrentryrec table row
     *
     */
    public function __construct($shrentryrec = false) {
        global $DB, $CFG;

        $this->config = get_config('sharedresource');

        require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$this->config->schema.'/plugin.class.php');
        $mtdclass = '\\mod_sharedresource\\plugin_'.$this->config->schema;
        $this->mtdstandard = new $mtdclass();

        if (is_object($shrentryrec)) {
            $this->shrentryrec = $shrentryrec;

            if ($this->file) {
                $fs = get_file_storage();
                $this->storedfile = $fs->get_file_by_id($shrentryrec->file);
            }
        } else {
            $this->shrentryrec = new StdClass();
            $this->id = 0;
            $this->provider = 'local';
            $this->isvalid = 1;
            $this->displayed = 1;
            $this->context = 1;
            $this->scoreview = 0;
            $this->scorelike = 0;
        }

        $this->metadataelements = array();

        if ($this->id) {
            if ($elements =  $DB->get_records('sharedresource_metadata', array('entryid' => $this->id))) {
                foreach ($elements as $element) {
                    $this->add_element($element->element, $element->value, $element->namespace);
                }
            }
        }
    }

    /**
     * magic getter
     *
     */
    public function __get($attr) {

        if ($attr == 'id') {
            return 0 + @$this->shrentryrec->$attr;
        }

        if ($attr == 'metadataelements') {
            return $this->metadataelements;
        }

        if (in_array($attr, array('id', 'title', 'type', 'mimetype', 'identifier', 'remoteid', 'file', 'url', 'lang', 'description',
                                  'keywords', 'timemodified', 'provider', 'isvalid', 'displayed', 'context', 'scoreview', 'scorelike',
                                  'thumbnail'))) {
            return $this->shrentryrec->$attr;
        } else {
            mtrace ("Bad attr ".$attr);
            print_error('errormemberwrongaccess', 'mod_sharedresource', $attr);
        }
    }

    /**
     * magic setter
     *
     */
    public function __set($attr, $value) {
        if (in_array($attr, array('id', 'title', 'type', 'mimetype', 'identifier', 'remoteid', 'file', 'url',
                                  'lang', 'description', 'keywords', 'timemodified', 'provider', 'isvalid',
                                  'displayed', 'context', 'scoreview', 'scorelike', 'thumbnail'))) {
            if ($attr == 'description') {
                if (is_array($value)) {
                    if (preg_match('/^<p>?(.*)<\/p>$/', $value['text'])) {
                        $this->shrentryrec->$attr = preg_replace('/^<p>?(.*)<\/p>$/', "$1", format_string($value['text'], $value['format']));
                    } else {
                        $valuestr = format_string($value['text'], $value['format']);
                        $this->shrentryrec->$attr = $valuestr;
                    }
                } else {
                    $this->shrentryrec->$attr = $value;
                }
            } else if ($attr == 'url') {
                // Ensure stringify.
                $this->shrentryrec->$attr = ''.$value;
            } else {
                $this->shrentryrec->$attr = $value;
            }
        } else {
            $this->$attr = $value;
        }
    }

    public function get_record() {
        return $this->shrentryrec;
    }

    /**
     * Internal method that processes the plugins for the before save
     * interface.
     * 
     * @return bool, returns true.
     */
    public function before_save() {
        global $CFG;

        // Get the plugins.
        $plugins = sharedresource_get_plugins($this->id);

        // Process each plugins before_save function - there is a default called local.
        foreach ($plugins as $plugin) {
            // If we get a positive return then we don't use any more plugins.
            $rc = $plugin->before_save($this);
            if (!$rc) {
                break;
            }
        }
        return true;
    }

    /**
     * Internal method that processes the plugins for the after save
     * interface.
     * 
     * @return bool, returns true.
     */
    public function after_save() {

        // Get the plugins.
        $plugins = sharedresource_get_plugins($this->id);

        // Process each plugins before_save function - there is a default called local.
        foreach ($plugins as $plugin) {
            // If we get a positive return then we don't use any more plugins.
            $rc = $plugin->after_save($this);
            if (!$rc) {
                break;
            }
        }
        return true;
    }
    
    /**
     * Internal method that processes the plugins for the before update
     * interface.
     * 
     * @return bool, returns true.
     */
    public function before_update() {

        // Get the plugins.
        $plugins = sharedresource_get_plugins($this->id);

        // Process each plugins before_save function - there is a default called local.
        foreach ($plugins as $plugin) {
            // If we get a positive return then we don't use any more plugins.
            $rc = $plugin->before_update($this);
            if (!$rc) {
                break;
            }
        }
        return true;
    }

    /**
     * Internal method that processes the plugins for the after update
     * interface.
     * 
     * @return bool, returns true.
     */
    public function after_update() {

        // Get the plugins.
        $plugins = sharedresource_get_plugins($this->id);

        // Process each plugins after_update function - there is a default called local.
        foreach ($plugins as $plugin) {
            // If we get a positive return then we don't use any more plugins.
            $rc = $plugin->after_update($this);
            if (!$rc) {
                break;
            }
        }
        return true;
    }

    /**
     * set a core shrentryrec attribute, or add a metadata element (always appended)
     *
     * @param element   string, name of shrentryrec attribute or metadata element
     * @param value     string, value of shrentryrec attribute or metadata element
     * @param namespace string, namespace of metadata element only
     */
    public function add_element($element, $value, $namespace = '') {
        global $SHAREDRESOURCE_CORE_ELEMENTS;

        $this->update_element($element, $value, $namespace);
    }

    /**
     * access the value of a core shrentryrec attribute or metadata element
     *
     * @param element   string, name of shrentryrec attribute or metadata element
     * @param namespace string, namespace of metadata element only
     * @return string, value of attribute or metadata element
     */
    public function element($element, $namespace = '') {
        global $SHAREDRESOURCE_CORE_ELEMENTS;

        if (in_array($element, $SHAREDRESOURCE_CORE_ELEMENTS) && empty($namespace) && isset($this->$element)) {
            return $this->$element;
        } else {
            if (!empty($this->metadataelements)) {
                foreach ($this->metadataelements as $el) {
                    if ($el->element == $element) {
                        return $el->value;
                    }
                }
            }
        }
        return false;
    }

    /**
     * amend a core shrentryrec attribute, or metadata element - if metadata element
     * is not found then it is appended.
     *
     * @param element   string, name of shrentryrec attribute or metadata element
     * @param value     string, value of shrentryrec attribute or metadata element
     * @param namespace string, namespace of metadata element only
     */
    public function update_element($element, $value, $namespace = '') {
        global $SHAREDRESOURCE_CORE_ELEMENTS;

        // add the core ones to the main table entry - everything else goes in the metadata table.
        if (in_array($element, $SHAREDRESOURCE_CORE_ELEMENTS) && empty($namespace) && !empty($value)) {
            $this->$element = $value;
        } else {
            $location = false;
            $i = 0;
            foreach ($this->metadataelements as $el) {
                if ($el->element == $element) {
                    $location = $i;
                    break;
                }
                $i++;
            }
            if ($location === false) {
                $this->metadataelements[] = new metadata($this->id, $element, $value, $namespace);
            } else {
                $this->metadataelements[$location] = new metadata($this->id, $element, $value, $namespace);
            }
        }
    }

    /**
     * check if resource is local or not
     * 
     * @return bool, true = local
     */
    public function is_local_resource() {
        global $CFG;

        if (isset($this->file) && $this->file) {
            $filename = $CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH.$this->file;
            if (is_file($filename)) {
                return true;
            }
        }
        return false;
    }

    /**
     * check if resource is remote or not
     *
     * @return bool, true = remote
     */
    public function is_remote_resource() {
        return ! $this->is_local_resource();
    }

    /**
     * check if resource has been provided from internal operation or
     * has been retrieved from an externaly bound resources source.
     *
     * @return bool, true = remote
     */
    public function has_local_provider() {
        return empty($this->provider) || $this->provider == 'local';
    }

    /**
     * Commit the new Shared resource to the database
     *
     * @return bool, true = success
     */
    public function add_instance() {
        global $CFG, $DB;

        /*
         * Given an object containing all the necessary data,
         * (defined by the form in mod.html) this function
         * will create a new instance and return the id number
         * of the new instance.
         */

        // Is this a local resource or a remote one?
        if (empty($this->identifier) && !empty($this->url) && empty($this->file)) {
            $this->identifier = sha1($this->url);
            $this->mimetype = mimeinfo("type", $this->url);
        }

        $url = ''.$this->url; // Force stringify if a moodle_url instance.
        $file = $this->file;

        if (!empty($url) && !$this->is_local_resource()) {
            $this->file = '';
        } else if ((empty($url)) && (!empty($file))) {
            if (empty($this->config->foreignurl)) {
                $this->url = ''.new moodle_url('/local/sharedresources/view.php', array('identifier' => $this->identifier));
            } else {
                $this->url = str_replace('<%%ID%%>', $this->identifier, $this->config->foreignurl);
            }
        } else {
            print_error("bad case ");
        }

        // One way or another we must have a URL by now.
        if (!$this->url) {
            print_error('erroremptyurl', 'sharedresource');
        }

        // Localise resource to this node for a resource network.
        if (empty($this->provider)) {
            $this->provider = 'local';
        }

        // Trigger the before save plugins.
        $this->before_save();

        $this->timemodified = time();

        // Add a proxy for keyword values.
        require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$this->config->schema.'/plugin.class.php');
        $mtdclass = '\\mod_sharedresource\\plugin_'.$this->config->schema;
        $mtdstandard = new $mtdclass();

        // Remap metadata elements array to cope with setKeywordValues expected format.
        $metadataelements = array();
        foreach ($this->metadataelements as $elm) {
            $metadataelements[$elm->element] = $elm;
        }
        $this->shrentryrec->keywords = $mtdstandard->getKeywordValues($metadataelements);

        // Fix description from tinymce.
        if (is_array(@$this->description)) {
            $this->shrentryrec->description = @$this->description['text'];
        }

        if ($oldres = $DB->get_record('sharedresource_entry', array('identifier' => $this->identifier))) {
            $this->id = $oldres->id;
            $DB->update_record('sharedresource_entry', $this->shrentryrec);
        } else {
            try {
                $this->id = $DB->insert_record('sharedresource_entry', $this->shrentryrec);
            } catch (Exception $ex) {
                if (debugging()) {
                    mtrace($ex->getMessage());
                    print_object($this->shrentryrec);
                }
            }
        }

        /*
         * now we know the itemid for this resource, if it has a real file 
         * $this->file still holds the draft area file record at this time
         */
        $fs = get_file_storage();
        if (!empty($file)) {

            $filerec = new StdClass();
            $systemcontext = \context_system::instance();
            $filerec->contextid = $systemcontext->id;
            $filerec->component = 'mod_sharedresource';
            $filerec->filearea = 'sharedresource';
            $filerec->itemid = $this->id;
            $filerec->path = '/';

            $definitive = $fs->create_file_from_storedfile($filerec, $file);

            // Now we post udate the shrentryrec record to reflect changes.
            $this->shrentryrec->file = $definitive->get_id();

            $DB->set_field('sharedresource_entry', 'file', $definitive->get_id(), array('id' => $this->id));
        }

        // Process eventual thumbnail.
        if (!empty($this->thumbnail)) {

            $filerec = new StdClass();
            $systemcontext = \context_system::instance();
            $filerec->contextid = $systemcontext->id;
            $filerec->component = 'mod_sharedresource';
            $filerec->filearea = 'thumbnail';
            $filerec->itemid = $this->id;
            $filerec->path = '/';

            $fs->create_file_from_storedfile($filerec, $this->thumbnail);
            // Thumbnail ID is NOT stored into sharedresource record.
        }

        // Clean up any prexisting elements (in case of bounces).
        $DB->delete_records('sharedresource_metadata', array('entryid' => $this->id));

        // Now do the LOM metadata elements.
        foreach ($this->metadataelements as $element) {
            $element->entryid = $this->id;
            if (! $element->add_instance()) {
                return false;
            }
        }

        // Trigger the after save plugins.
        $this->after_save();

        return true;
    }

    /**
     * Commit the updated Shared resource to the database
     *
     * @return bool, true = success
     */
    public function update_instance() {
        global $CFG, $DB;

        $this->timemodified = time();

        // Trigger the before save plugins.
        $this->before_update();

        // Remove and recreate metadata records.
        $DB->delete_records('sharedresource_metadata', array('entryid' => $this->id));

        foreach ($this->metadataelements as $element) {
            $element->entryid = $this->id;

            // Todo recheck this. this is a pass through quick fix
            if (empty($element->namespace)) {
                $element->namespace = 'lom';
            }

            $element->add_instance();
        }

        if (is_array(@$this->description)) {
            $this->shrentryrec->description = @$this->description['text'];
        }
        $this->title = $this->title;

        // Remap metadata elements array to cope with setKeywordValues expected format.
        $metadataelements = array();
        foreach($this->metadataelements as $elm) {
            $metadataelements[$elm->element] = $elm;
        }
        $this->shrentryrec->keywords = $this->mtdstandard->getKeywordValues($metadataelements);

        if (! $DB->update_record('sharedresource_entry', $this->shrentryrec)) {
            return false;
        }

        $fs = get_file_storage();
        $systemcontext = \context_system::instance();

        // Process eventual thumbnail.
        $fs->delete_area_files($systemcontext->id, 'mod_sharedresource', 'thumbnail', $this->shrentryrec->id);
        $thumbnail = @$this->thumbnail; // Care with magic __get and empty().
        if (!empty($thumbnail)) {
            $filerec = new StdClass();
            $filerec->contextid = $systemcontext->id;
            $filerec->component = 'mod_sharedresource';
            $filerec->filearea = 'thumbnail';
            $filerec->itemid = $this->id;
            $filerec->path = '/';

            $definitive = $fs->create_file_from_storedfile($filerec, $this->thumbnail);
        }

        // Trigger the after save plugins.
        $this->after_update();

        return true;
    }

    /**
     * delete the current Shared resource from the database, and
     * any locally attached files.
     * 
     * @return bool, true = success
     */
    public function delete_instance() {
        global $DB;

        /* Given an object containing the sharedresource data
         * this function will permanently delete the instance
         * and any data that depends on it, including local file.
         */

        if (! $DB->delete_records('sharedresource_metadata', array('entryid' => $this->id))) {
            return false;
        }

        if ($this->is_local_resource()) {
            $context = \context_system::instance();
            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'mod_sharedresource', 'sharedresource', $this->id);
            $fs->delete_area_files($context->id, 'mod_sharedresource', 'thumbnail', $this->id);
        }

        if (! $DB->delete_records('sharedresource_entry', array('id' => $this->id))) {
            return false;
        }
        return true;
    }

    public function unset_metadata($key) {
        if (isset($this->metadataelements[$key])) {
            unset($this->metadataelements[$key]);
        }
    }

    public function exists() {
        global $DB;

        return $DB->record_exists('sharedresource_entry', array('identifier' => $this->identifier));
    }
}
