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
 * This class defines a single sharedresource entry in central library.
 *
 * @author  Piers Harding  <piers@catalyst.net.nz>, Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package mod_sharedresource
 *
 */

/*
 * Because classes preloading (SHAREDRESOURCE_INTERNAL) pertubrates MOODLE_INTERNAL detection.
 * phpcs:disable moodle.Files.MoodleInternal.MoodleInternalGlobalState
 */
namespace mod_sharedresource;

if (!defined('SHAREDRESOURCE_INTERNAL')) {
    defined('MOODLE_INTERNAL') || die();
}

use StdClass;
use moodle_url;
use moodle_exception;
use context_system;

require_once(dirname(__FILE__).'/sharedresource_entry_factory.class.php');

/**
 * This class defines a single sharedresource entry in central library.
 *
 * This class provides all the functionality for a sharedresource
 * defined locally or remotely
 *
 * A locally defined resource is essentially one where the user has uploaded
 * a file, therefore this local moodle has to serve it as reference access point.
 *
 * A remote resource is one that has a fully qualified URI that does not rely
 * on this local Moodle instance to serve the physical data eg. PDF, PNG etc.
 *
 * mod/sharedresource uses the presence of a $shrentryrec->file attribute
 * to determine if this resource is hosted locally (the physical file must
 * also exist in the moodle filedir).
 *
 * mod/sharedresource uses a system level file repository. Files are stored into
 * a system context, course independant, special filearea.
 * phpcs:disable moodle.Commenting.ValidTags.Invalid
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class entry {

    // A sharedresource entry is a combination of.

    /** @var The DB record of the resource entry. */
    protected $shrentryrec;

    /** @var A set of metadata as an array of mod_sharedresource/metadata instances. */
    public $metadataelements;

    /** @var An eventual physical file stored somewhere in the Moodle filesystem. */
    public $storedfile;

    /** @var The current system wide configuration of the sharedresource plugin. */
    protected $config;

    /** @var A reference metadata standard description. */
    protected $mtdstandard;

    /**
     * Internal method that processes the plugins for the search
     * interface.
     *
     * @param object $criteria, reference to Moodle Forms populated
     *        values.
     * @return results, return an array of shrentryrec objects that
     *         will be formated and displayed in the search results screen.
     */
    public static function search(& $criteria) {
        // Get the plugins.
        $plugins = sharedresource_get_plugins();
        $results = [];

        // Process each plugins search function - there is a default called local.
        foreach ($plugins as $plugin) {
            /*
             * If we get a positive return then we don't use any more plugins
             * $results is passed by reference so plugins can doctor the incremental results
             */
            $plugin->search($criteria, $results);
        }
        return $results;
    }

    /**
     * Hydrate a sharedresource_entry object reading by identifier
     *
     * @param string $identifier, sha1 hash identifier
     * @return entry object
     */
    public static function read($identifier) {
        global $DB;

        if (!$shrentryrec = $DB->get_record('sharedresource_entry', ['identifier' => $identifier])) {
            return false;
        }

        // Entry class may upgrade itself to entry_extended in "pro" version.
        $entryclass = entry_factory::get_entry_class();
        $shrentry = new $entryclass($shrentryrec);
        return $shrentry;
    }

    /**
     * Hydrates a shrentryrec object reading by id
     *
     * @param id   int, internal id of shrentryrec object
     * @return shrentryrec object
     */
    public static function read_by_id($entryid) {
        global $DB;

        if (!$shrentryrec = $DB->get_record('sharedresource_entry', ['id' => $entryid])) {
            return false;
        }

        $entryclass = entry_factory::get_entry_class();
        $shrentry = new $entryclass($shrentryrec);
        return $shrentry;
    }

    /**
     * Same as read_by_id(). Hydrate a shrentryrec object reading by id
     *
     * @param id   int, internal id of shrentry record
     * @return entry object
     */
    public static function get_by_id($entryid) {
        return self::read_by_id($entryid);
    }

    /**
     * Same as read(). Hydrate a shrentryrec object reading by identifier
     *
     * @param identifier   hash, sha1 hash identifier
     * @return entry object
     */
    public static function get_by_identifier($identifier) {
        return self::read($identifier);
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
            $this->identifier = '';
            $this->provider = 'local';
            $this->isvalid = 1;
            $this->displayed = 1;
            $this->context = 1;
            $this->scoreview = 0;
            $this->scorelike = 0;
        }

        $this->metadataelements = [];

        if ($this->id) {
            $this->mtdstandard->set_entry($this->id); // Bind the metadata instance to us.
            if ($elements = $DB->get_records('sharedresource_metadata', ['entryid' => $this->id])) {
                foreach ($elements as $element) {
                    $this->add_element($element->element, $element->value, $element->namespace);
                }
            }
        }
    }

    /**
     * magic getter
     * @param string $attr
     */
    public function __get($attr) {

        if ($attr == 'id') {
            return $this->shrentryrec->$attr ?? 0;
        }

        if ($attr == 'metadataelements') {
            return $this->metadataelements;
        }

        if (in_array($attr, ['id', 'title', 'type', 'mimetype', 'identifier', 'remoteid', 'file', 'url', 'lang', 'description',
                          'thumbnail', 'keywords', 'timemodified', 'provider', 'isvalid', 'displayed', 'context',
                          'scoreview', 'scorelike', 'score', 'accessctl'])) {
            return $this->shrentryrec->$attr;
        } else {
            mtrace ("Bad attr ".$attr);
            throw new moodle_exception(get_string('errormemberwrongaccess', 'mod_sharedresource', $attr));
        }
    }

    /**
     * magic setter
     * @param string $attr
     * @param mixed $value
     */
    public function __set($attr, $value) {
        if (in_array($attr, ['id', 'title', 'type', 'mimetype', 'identifier', 'remoteid', 'file', 'url',
                                  'lang', 'description', 'keywords', 'timemodified', 'provider', 'isvalid',
                                  'displayed', 'context', 'scoreview', 'scorelike', 'score', 'thumbnail', 'accessctl'])) {
            if ($attr == 'description') {
                if (is_array($value)) {
                    if (preg_match('/^<p>?(.*)<\/p>$/', $value['text'])) {
                        $pattern = '/^<p>?(.*)<\/p>$/';
                        $this->shrentryrec->$attr = preg_replace($pattern, "$1", format_string($value['text'], $value['format']));
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

    /**
     * Builds an url for the notice.
     */
    public function get_notice_link() {
        return new moodle_url('/mod/sharedresource/metadatanotice.php', ['identifier' => $this->identifier]);
    }

    /**
     * Accessor to the internal record.
     */
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
     * @param string $element name of shrentryrec attribute or metadata element
     * @param string $value value of shrentryrec attribute or metadata element
     * @param string $namespace namespace of metadata element only
     */
    public function add_element($element, $value, $namespace = '') {
        $this->update_element($element, $value, $namespace);
    }

    /**
     * access the value of a core shrentryrec attribute or metadata element
     *
     * @param string $element name of shrentryrec attribute or metadata element
     * @param string $namespace namespace of metadata element only
     * @return value of attribute or metadata element
     */
    public function element($element, $namespace = '') {
        global $shrcoreelements;

        if (in_array($element, $shrcoreelements) && empty($namespace) && isset($this->$element)) {
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
     * @param string $element name of shrentryrec attribute or metadata element
     * @param string $value value of shrentryrec attribute or metadata element
     * @param string $namespace namespace of metadata element only
     */
    public function update_element($element, $value, $namespace = '') {
        global $shrcoreelements;

        // Add the core ones to the main table entry - everything else goes in the metadata table.
        if (in_array($element, $shrcoreelements) && !empty($value)) {
            $this->$element = $value;
        } else {
            if (!array_key_exists($element, $this->metadataelements)) {
                $this->metadataelements[$element] = new metadata($this->id, $element, $value, $namespace);
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
     * Check if resource is remote or not.
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
            // We forge an access url, for foreign references if needed.
            if (empty($this->config->foreignurl)) {
                $this->url = ''.new moodle_url('/local/sharedresources/view.php', ['identifier' => $this->identifier]);
            } else {
                $this->url = str_replace('<%%ID%%>', $this->identifier, $this->config->foreignurl);
            }
        } else {
            throw new coding_exception("This situation should not be possible.");
        }

        // One way or another we must have a URL by now.
        if (!$this->url) {
            throw new moodle_exception(get_string('erroremptyurl', 'sharedresource'));
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
        $metadataelements = [];
        foreach ($this->metadataelements as $elm) {
            $metadataelements[$elm->element] = $elm;
        }
        $this->shrentryrec->keywords = $mtdstandard->get_keyword_values($metadataelements);

        // Fix description from tinymce.
        if (is_array(@$this->description)) {
            $this->shrentryrec->description = @$this->description['text'];
        }

        if ($oldres = $DB->get_record('sharedresource_entry', ['identifier' => $this->identifier])) {
            $msg = "Shared resources cannot collide identifier on creation. ";
            $msg .= "The identifier '{$this->identifier}' exists. This case should not happen.";
            throw new moodle_exception($msg);
        } else {
            $this->id = $DB->insert_record('sharedresource_entry', $this->shrentryrec);
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

            $DB->set_field('sharedresource_entry', 'file', $definitive->get_id(), ['id' => $this->id]);
        }

        // Process eventual thumbnail.
        $thumbnail = @$this->thumbnail; // Care with magic __get and empty().
        if (!empty($thumbnail)) {

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
        $DB->delete_records('sharedresource_metadata', ['entryid' => $this->id]);

        // Now do the LOM metadata elements.
        foreach ($this->metadataelements as $element) {
            $element->entryid = $this->id;
            if (! $element->add_instance()) {
                return false;
            }
        }
        $this->mtdstandard->set_entry($this->id);

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
        global $DB, $SESSION;

        if (!empty($SESSION->sr_must_clone_to)) {
            /*
             * the resource content has changed, Identifier cannot be preserved.
             * We need to clone resource
             */
            $oldidentifier = $this->shrentryrec->identifier;
            $this->shrentryrec->identifier = $SESSION->sr_must_clone_to;
            $this->add_instance();
            $oldshrentry = self::get_by_identifier($oldidentifier);
            $this->set_previous($oldshrentry);
            $oldshrentry->set_next($this);

            // Trigger the after save plugins.
            // TODO : maybe not a good idea. before_add() and after_add() have already been played.
            $this->after_update();

            // Free session marker.
            unset($SESSION->sr_must_clone_to);
            unset($SESSION->sr_no_identifier_change);
            return true;
        }

        $this->timemodified = time();

        // Trigger the before save plugins.
        $this->before_update();

        // Remove and recreate metadata records.
        $DB->delete_records('sharedresource_metadata', ['entryid' => $this->id]);

        $firstdescelmkey = null;
        $desc = $this->mtdstandard->get_description_element();
        if ($desc) {
            $firstdescelmkey = metadata::to_instance($desc->node);
        }

        foreach (array_values($this->metadataelements) as $element) {

            $element->entryid = $this->id;

            // Todo recheck this. this is a pass through quick fix.
            if (empty($element->namespace)) {
                $element->namespace = 'lom';
            }

            // Ensure description is identical to metadata.
            if ($firstdescelmkey == $element->get_element_key()) {
                // We are in a first description.
                $this->shrentryrec->description = $element->get_value();
            }

            $element->add_instance();
        }
        $this->title = $this->title;

        // Fix description from tinymce.
        if (is_array($this->description ?? false)) {
            $this->shrentryrec->description = $this->description['text'] ?? '';
        }

        // Remap metadata elements array to cope with setKeywordValues expected format.
        $metadataelements = [];
        foreach ($this->metadataelements as $elm) {
            $metadataelements[$elm->element] = $elm;
        }
        $this->shrentryrec->keywords = $this->mtdstandard->get_keyword_values($metadataelements);

        $DB->update_record('sharedresource_entry', $this->shrentryrec);

        $fs = get_file_storage();
        $systemcontext = context_system::instance();

        // Process eventual thumbnail, by deleting old stored version and recreate stored file.
        $fs->delete_area_files($systemcontext->id, 'mod_sharedresource', 'thumbnail', $this->shrentryrec->id);
        $thumbnail = $this->thumbnail ?? ''; // Care with magic __get and empty().
        if (!empty($thumbnail)) {
            $filerec = new StdClass();
            $filerec->contextid = $systemcontext->id;
            $filerec->component = 'mod_sharedresource';
            $filerec->filearea = 'thumbnail';
            $filerec->itemid = $this->id;
            $filerec->path = '/';

            $fs->create_file_from_storedfile($filerec, $this->thumbnail);
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

        /*
         * Given an object containing the sharedresource data
         * this function will permanently delete the instance
         * and any data that depends on it, including local file.
         */

        if (! $DB->delete_records('sharedresource_metadata', ['entryid' => $this->id])) {
            return false;
        }

        if ($this->is_local_resource()) {
            $context = context_system::instance();
            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'mod_sharedresource', 'sharedresource', $this->id);
            $fs->delete_area_files($context->id, 'mod_sharedresource', 'thumbnail', $this->id);
        }

        if (! $DB->delete_records('sharedresource_entry', ['id' => $this->id])) {
            return false;
        }
        return true;
    }

    /**
     * Unset an element in metadata.
     * @param string $element
     */
    public function unset_metadata($element) {
        if (isset($this->metadataelements[$element])) {
            unset($this->metadataelements[$element]);
        }
    }

    /**
     * Check if a resource exists and binds the record to represent this instance with
     * new current data.
     */
    public function exists() {
        global $DB;

        if ($oldrec = $DB->get_record('sharedresource_entry', ['identifier' => $this->identifier])) {
            $this->id = $oldrec->id;

            // Update all internal metadata references.
            if (!empty($this->metadataelements)) {
                foreach ($this->metadataelements as $element) {
                    $element->entryid = $this->id;
                }
            }

            return $this->id;
        }

        return false;
    }

    /**
     * Fetches the next resource version reference in the chain. Uses the Relation
     * metadata branch. Returns self if no other version
     * @param bool $fullchain if true, fetch untill new ids are found. Stops when the next is same than last.
     * @return the next sharedresource id in the version daisy chain.
     */
    public function fetch_ahead($fullchain = false) {

        $config = get_config('sharedresource');

         $mtdstandard = sharedresource_get_plugin($config->schema, $this->shrentryrec->id);

        if (is_null($mtdstandard->get_version_support_element())) {
            return $this;
        }

        $nextid = $mtdstandard->get_next();
        if ($nextid != $this->id) {
            $next = self::get_by_id($nextid);
            if (empty($next)) {
                throw new moodle_exception("Non existing versionned resource");
            }
            return $next->fetch_ahead($fullchain);
        }

        return $this;
    }

    /**
     * Tells if the current entry has a next version.
     * @return the next sharedresource id and the current resource id if end of chain.
     */
    public function has_next() {
        $nextid = $this->mtdstandard->get_next();
        return (!empty($nextid) && ($nextid != $this->id));
    }

    /**
     * Defines the given sharedresouce as next version of the current resource.
     * @return the next sharedresource id and the current resource id if end of chain.
     */
    public function get_next() {
        return $this->mtdstandard->get_next();
    }

    /**
     * Defines the given sharedresouce as next version of the current resource.
     * @param object $shrentry
     */
    public function set_next($shrentry) {
        return $this->mtdstandard->set_next($shrentry);
    }

    /**
     * Defines the given sharedresouce as next version of the current resource.
     */
    public function get_previous() {
        return $this->mtdstandard->get_previous();
    }

    /**
     * Defines the given sharedresouce as previous version of the current resource.
     * @param object $shrentry
     */
    public function set_previous($shrentry) {
        return $this->mtdstandard->set_previous($shrentry);
    }

    /**
     * Checks some simple access policy on ressource.
     * A user may have a user_info_field holding an acceptable value to match
     * in resource allowed value.
     * Access filter only affect search and browse capabilities. but not access to the sharedresource
     * if the user has access to a sharedresource publication in a course.
     * The resource may be multivaluated, depending on wether the access check is allowed to register
     * multiple values or not. this is set up in global settings.
     *
     * @param object $resourceentry
     * @return boolean value (has access or not).
     * @see local/sharedresources/lib.php get_local_resources()§137
     */
    public function has_access() {
        global $DB;
        static $userdatacache;

        if (!isset($userdatacache)) {
            $userdatacache = [];
        }

        $config = get_config('sharedresource');

        if (empty($config->accesscontrol)) {
            // Global switch in config. Disables all access conrol overhead.
            return true;
        }

        // Per resource strategy.
        /*
         * This is a fine grain resource per resource accesss control management using
         * user userfields values.
         * Each resource has one single userfield control attribute (holding a custom userfield reference per id)
         * and a set of control values (allowed values) as a coma separated list.
         */
        if (!empty($this->accessctl)) {
            $accessctl = access_ctl::instance($this->accessctl);
            if ($accessctl->can_use()) {
                return true;
            }
        }

        // By taxonomy access control.
        /*
         * Taxonomies have some access control rules based on profile field or capabilities.
         * At least one match is required to pass, when the resource has explicit taxonomy binding.
         * The check examines all sources registered for the resource, confirmed by an actual registered
         * binding taxonid. (note there could be remanent registered sources without any taxonid, following
         * a taxon deletion in the taxonomy.)
         */
         $mtdstandard = sharedresource_get_plugin($config->schema);
         $taxumarr = $mtdstandard->get_taxum_path();

        if (empty($taxumarr)) {
            // No need to care about taxonomy access control as there is no taxonomy in the standard.
            return true;
        }

        $sources = metadata::instances_by_node($this->id, $config->schema, $taxumarr['source']);
        if (!empty($sources)) {
            foreach ($sources as $source) {
                // Find some tokenids that are attached to this source instance.
                $idkey = metadata::to_instance($taxumarr['id'], $source->get_element_key()); // Normalise element key.
                $elmids = metadata::instances_by_element($this->id, $config->schema, $idkey, null, true);
                if (empty($elmids)) {
                    continue;
                }
                /*
                 * Pass if
                 * - one source at least has no access control.
                 * - one source at least has access allowed for user.
                 */
                 $taxonomy = $DB->get_record('sharedresource_classif', ['id' => $source->get_value()]);
                 $classif = new \local_sharedresource\browser\navigator($taxonomy);
                if ($classif->can_use()) {
                    return true;
                }
            }
            // No taxonomy match.
            return false;
        }

        return true;
    }
}
