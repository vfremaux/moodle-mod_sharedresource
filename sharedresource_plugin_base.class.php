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
 * @author  Valery Fremaux  valery.fremaux@club-internet.fr
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */

/**
 * sharedresource_plugin_base is the base class for sharedresource plugins
 *
 * This class provides all the functionality for a sharedresource plugin that does nothing :-)
 *
 * The idea of the plugin is to give access to particular events in the cycle of creating new
 * shared Resources (new resources, NOT the attachment of resources to a course as a course module).
 *
 * These events fall into three broad categories - creating a New Resource, updating a Resource,
 * and searching for Resources that will be attached to a course as a course module.
 *
 * Plugins are subclassed from this class, in a file called plugin.class.php, which must live in
 * a directory named after the plugin, and follow a strict naming convention.  For example, the
 * two standard plugins provided are, local, and solr.
 * local: provides a search interface using the local resource table sharedresource_entry.
 * solr: provides a simple search interface to an Apache-Solr directory populated with data
 * from the sharedresource_entry table.
 *
 * local lives in the mod/sharedresource/plugins/local/plugin.class.php file with a class name of
 * sharedresource_plugin_local.
 *
 * All plugins are stacked, so you can create several specialised handlers, and have them run one 
 * after the other.  If you want the processing of stacked handlers to finish at any stage then 
 * return false from your handling method.
 *
 * Plugins can be deactivated by system config eg. to deactivate the solr plugin, use:
 * $CFG->sharedresource_plugin_hide_solr  = 1;
 * So it is sharedresource_plugin_hide_<plugin name>.
 *
 */

abstract class sharedresource_plugin_base {

    public $entryid; // The sharedresource entry id.

    public $pluginname;

    /**
     * Entry point to modify the search form - add/modify elements
     * here.
     *
     * @param mform   object, reference to Moodle Forms object
     * @return bool, return true to continue to the next handler
     *         false to stop the running of any subsequent plugin handlers.
     */
    public function search_definition(&$mform) {
        return true;
    }
    
    /**
     * Entry point to facilitate the search based on search form
     * inputs submitted.  Using the form input values, populate 
     * the $results array with sharedresource_entry objects corresponding
     * to what you want to give back to the user.
     *
     * @param fromform   object, reference to Moodle Forms populated
     * values.
     * @param result   array, reference to an array
     * @return bool, return true to continue to the next handler
     *         false to stop the running of any subsequent plugin handlers.
     */
    public function search(&$fromform, &$result) {
        return true;
    }

    /**
     * Entry point to modify the sharedresource_entry_form form - add/modify elements
     * here.
     *
     * @param mform   object, reference to Moodle Forms object
     * @return bool, return true to continue to the next handler
     *         false to stop the running of any subsequent plugin handlers.
     */
    public function sharedresource_entry_definition(&$mform) {
        return true;
    }

    
    /**
     * Entry point to validate the sharedresource_entry_form form.
     * Add your errors to the $errors array, and use $mode to determine
     * if the sharedresource_entry is being updated or added new (add == new).
     *
     * @param  data   object, reference to $data as per normal Moodle Forms validations
     * @param  files  object, reference to $files as per normal Moodle Forms validations
     * @param  errors object, reference to $errors as per normal Moodle Forms validations
     * @param  mode   add = new resource being created
     * @return bool,  return true to continue to the next handler
     *         false to stop the running of any subsequent plugin handlers.
     */
    public function sharedresource_entry_validation($data, $files, &$errors, $mode) {
        return true;
    }

    /**
     * Entry point to get a list of field names to be ignored as incoming
     * metadata.
     *
     * @return array,  return an array of CGI parms names or empty array
     */
    public function sharedresource_get_ignored() {
        return array();
    }

    /**
     * Access to the sharedresource_entry object before a new object
     * is saved.  This is a good position to populate the remoteid
     * value after submitting the details to the external CNDP index.
     * 
     * @param sharedresource_entry   object, reference to sharedresource_entry object
     *        including metadata
     * @return bool, return true to continue to the next handler
     *        false to stop the running of any subsequent plugin handlers.
     */
    public function before_save(&$sharedresource_entry) {
        return true;
    }

    /**
     * Access to the sharedresource_entry object after a new object
     * is saved. 
     * 
     * @param sharedresource_entry   object, reference to sharedresource_entry object
     *        including metadata
     * @return bool, return true to continue to the next handler
     *        false to stop the running of any subsequent plugin handlers.
     */
    public function after_save(&$sharedresource_entry) {
        return true;
    }

    /**
     * Access to the sharedresource_entry object before an existing object
     * is updated. 
     * 
     * @param sharedresource_entry   object, reference to sharedresource_entry object
     *        including metadata
     * @return bool, return true to continue to the next handler
     *        false to stop the running of any subsequent plugin handlers.
     */
    public function before_update(&$sharedresource_entry) {
        return true;
    }

    /**
     * Access to the sharedresource_entry object after an existing object
     * is updated. 
     * 
     * @param sharedresource_entry   object, reference to sharedresource_entry object
     *        including metadata
     * @return bool, return true to continue to the next handler
     *        false to stop the running of any subsequent plugin handlers.
     */
    public function after_update(&$sharedresource_entry) {

        if (method_exists('setKeywords', $this)) {
            setKeywords($this->keywords);
        }

        return true;
    }

    /**
     * given a sharedresource entry, retrieves a suitable metadata string that might cope with
     * the metadata collected
     * 
     * @param sharedresource_entry   object, reference to sharedresource_entry object
     *        including metadata
     * @return string   the metadata produced
     */
    public function get_metadata(&$sharedresource_entry) {
        return true;
    }

    /**
     * retrieves an eventual metadata parser
     *
     */
    function get_parser() {
        if (file_exists($CFG->dirroot."/mod/sharedresource/plugins/metadata_xml_parser_$pluginname/xmlparser.php")) {
            require_once($CFG->dirroot."/mod/sharedresource/plugins/$pluginname/xmlparser.php");
            $parser_class_name = "metadata_xml_parser_$pluginname";
            return new $parser_class_name();
        }
        return null;
    }

    /**
     * tells the outside world if we know this node in the current standard.
     * @param string a Dublin Core node identifier.
     * @return true if the node is known
     */
    function hasNode($nodekey) {
        if (empty($this->METADATATREE)) return false;
        return array_key_exists($nodekey, $this->METADATATREE);
    }

    /**
     * set the current resource entry id for this plugin
     */
    function setEntry($entryid) {
        $this->entryid = $entryid;
    }

    /**
     * keyword have a special status as stored both in metadata and in entry record
     */
    abstract function getKeywordValues($metadata);

    /**
     * get the metadata node identity for title
     */
    abstract function getTitleElement();

    /**
     * get the metadata node identity for description
     */
    abstract function getDescriptionElement();

    /**
     * get the metadata node identity for keyword
     */
    abstract function getKeywordElement();

    /**
     * get the metadata node identity for taxonomy purpose
     */
    abstract function getTaxonomyPurposeElement();

    /**
     * add keywords metadata entries from a comma separated list
     * of values. Each plugin know how and where to put values
     */
    abstract function setKeywords($keywords);

    /**
     * function to get any element only with its number of node
     */
    function getElement($id) {
        $element = new StdClass;
        $element -> id = $id;
        $element -> name = $this->METADATATREE[$id]['name'];
        $element -> type = $this->METADATATREE[$id]['widget'];
        return $element;
    }

    /**
     * A generic method that allows changing a simple text value
     *
     */
    function setTextElementValue($element, $item, $value) {
        global $DB;

        if (!array_key_exists($element, $this->METADATATREE)) {
            throw new MetadataException("Bad element ID");
        }

        if ($this->METADATATREE[$element]['type'] != 'text') {
            throw new MetadataException("Bad element type for setting text");
        }

        $itemdepth = count(explode('_', $element));
        $defaultitemarr = array_fill(0, $itemdepth, 0);
        if (empty($item)) {
            $item = implode('_', $defaultitemarr);
        }

        $mtdrec = new StdClass;
        $mtdrec->entry_id = $this->entryid;
        $mtdrec->element = "$element:$item";
        $mtdrec->namespace = $this->pluginname;
        $mtdrec->value = $value;

        if ($oldrec = $DB->get_record('sharedresource_metadata', array('entry_id' => $this->entryid, 'element' => $mtdrec->element, 'namespace' => $this->pluginname))){
            $mtdrec->id = $oldrec->id;
            $DB->update_record('sharedresource_metadata', $mtdrec);
        } else {
            $DB->insert_record('sharedresource_metadata', $mtdrec);
        }
    }

    /**
     * records title in metadata flat table from db attributes?
     * title element identification is given by each concrete plugin
     */
    function setTitle($title) {
        global $DB;

        if ($this->entryid == 0) {
            return;
        }

        $titleElement = $this->getTitleElement();
        $titlekey = '$titleElement:0_0';

        $DB->delete_records('sharedresource_metadata', array('entry_id' => $this->entryid, 'namespace' => $this->namespace, 'element' => $titlekey));
        $mtdrec = new StdClass;
        $mtdrec->entry_id = $this->entryid;
        $mtdrec->element = $titlekey;
        $mtdrec->namespace = $this->namespace;
        $mtdrec->value = $title;

        return $DB->insert_record('sharedresource_metadata', $mtdrec);
    }

    /**
     * records master description in metadata flat table from db attributes
     * description element identification is given by each concrete plugin
     */
    function setDescription($description) {
        global $DB;

        if ($this->entryid == 0) {
            return;
        }

        $descriptionElement = $this->getDescriptionElement();
        $desckey = '$descriptionElement:0_0';

        $DB->delete_records('sharedresource_metadata', array('entry_id' => $this->entryid, 'namespace' => $this->namespace, 'element' => $desckey));

        $mtdrec = new StdClass;
        $mtdrec->entry_id = $this->entryid;
        $mtdrec->element = $desckey;
        $mtdrec->namespace = $this->namespace;
        $mtdrec->value = $description;

        return $DB->insert_record('sharedresource_metadata', $mtdrec);
    }

    /**
     * records resource physical lcoation in metadata flat table from db attributes?
     * location element identification is given by each concrete plugin
     */
    function setLocation($location){
        global $DB;

        if ($this->entryid == 0) {
            return;
        }

        $locationElement = $this->getLocationElement();
        $locationkey = '$locationElement:0_0';

        $DB->delete_records('sharedresource_metadata', array('entry_id' => $this->entryid, 'namespace' => $this->namespace, 'element' => $locationkey));
        $mtdrec = new StdClass;
        $mtdrec->entry_id = $this->entryid;
        $mtdrec->element = $locationkey;
        $mtdrec->namespace = $this->namespace;
        $mtdrec->value = $location;

        return $DB->insert_record('sharedresource_metadata', $mtdrec);
    }

    /**
     * gets a default value for a node if exists
     *
     */
    function defaultValue($field) {
        return @$this->METADATATREE[$field]['default'];
    }
    
    /**
     * loads an externally defined default values for the schema
     * the provided default tree must provide additional default keys 
     * for relevant nodes : 
     * 
     * $METADATATREE_DEFAULT = array (
     *    '1_1_1' => arrau('default' => 'MyCatalog');
     * );
     *
     * would define a default value for the "Catalog field" of LOM based schemas
     */
    function load_defaults($METADATATREE_DEFAULTS) {
        if (!empty($METADATATREE_DEFAULTS)) {
            foreach ($METADATATREE_DEFAULTS as $key => $default) {
                $this->METADATATREE[$key]['default'] = $default['default'];
            }
        }
    }

    /**
     * a static factory. Gives back a metadata object loded with default values
     *
     */
    static function load_mtdstandard($schemaname) {
        global $CFG;

        if (file_exists($CFG->dirroot.'/mod/sharedresource/plugins/'.$schemaname.'/plugin.class.php')) {
            include_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$schemaname.'/plugin.class.php');
            $classname = "sharedresource_plugin_$schemaname";
            $mtdstandard = new $classname();
            if (!empty($CFG->METADATATREE_DEFAULTS)) {
                $mtdstandard->load_defaults($CFG->METADATATREE_DEFAULTS);
            }
            return $mtdstandard;
        }

        return false;
    }
}
