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
 * @author  Valery Fremaux  valery.fremaux@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package mod_sharedresource
 * @category mod
 *
 */
namespace mod_sharedresource;

use StdClass;

defined('MOODLE_INTERNAL') || defined('SHAREDRESOURCE_INTERNAL') || die("Not directly loadable. Use __autoload.php instead.");

include_once(dirname(__FILE__).'/sharedresource_metadata_exception.class.php');

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
 * a directory named after the plugin, and follow a strict naming convention.
 *
 * local lives in the mod/sharedresource/plugins/local/plugin.class.php file with a class name of
 * sharedresource_plugin_local.
 */
abstract class plugin_base {

    protected $entryid; // The sharedresource entry id.

    public $pluginname; // string

    protected $namespace; // string

    public $METADATATREE = [];

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
    public function before_save(&$shrentry) {
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
    public function after_save(&$shrentry) {
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
    public function before_update(&$shrentry) {
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
    public function after_update(&$shrentry) {

        if (method_exists('setKeywords', $this)) {
            setKeywords($this->keywords);
        }

        return true;
    }

    public function getNamespace() {
        return $this->namespace;
    }

    public function getElementNamespace($nodeid) {
        return $this->getElement($nodeid)->source;
    }

    /**
     * Form handler for scalar value (regular case)
     */
    public function sharedresource_entry_definition_scalar(&$mform, &$element) {

        if (empty($this->namespace)) {
            throw new coding_exception('sharedresource_entry_definition_scalar() : Trying to use on core mtd plugin class. No namespace assigned. Please inform developers.');
        }

        if ($element['type'] == 'select' || $element['type'] == 'sortedselect') {
            $values = $element['values'];
            $options = array();
            foreach ($values as $value) {
                $options[$value] = preg_replace('/\[\[|\]\]/', '', get_string(str_replace(' ', '_', strtolower($value)), 'sharedmetadata_'.$this->namespace));
            }
            $mform->addElement($element['type'], $element['name'], get_string(clean_string_key($element['name']), 'sharedmetadata_'.$this->namespace), $options);
        } else {
            $mform->addElement($element['type'], $element['name'], get_string(clean_string_key($element['name']), 'sharedmetadata_'.$this->namespace));
        }
    }

    public function sharedresource_entry_definition(&$mform) {
        global $DB;

        $config = get_config('sharedresource_'.$this->namespace);

        $iterators = array();
        foreach (array_keys($this->METADATATREE['0']['childs']) as $fieldid) {
            if (has_capability('mod/sharedresource:systemmetadata', $this->context)) {
                $metadataswitch = "config_lom_system_";
            } else if (has_capability('mod/sharedresource:indexermetadata', $this->context)) {
                $metadataswitch = "config_lom_indexer_";
            } else {
                $metadataswitch = "config_lom_author_";
            }
            $mform->metadataswitch = $metadataswitch;
            $metadataswitch .= $fieldid;
            if ($config->$metadataswitch) {
                $fieldtype = $this->METADATATREE['0']['childs'][$fieldid];
                $generic = $this->METADATATREE[$fieldid]['name'];
                if ($fieldtype == 'list') {
                    list($mtdsql, $mtdparams) = $DB->get_in_or_equal($this->ALLSOURCES);
                    if ($instances = $DB->get_records_select('sharedresource_metadata', " entryid = ? AND namespace $mtdsql AND name LIKE '$generic:%' ",
                        array_merge(array($this->entryid), $mtdparams))) {
                        $iterators[] = 0;
                        foreach ($instances as $instance) {
                            $this->sharedresource_entry_definition_rec($mform, $fieldid, $iterators);
                            $iterator = array_pop($iterators);
                            $iterator++;
                            array_push($iterators, $iterator);
                        }
                    }
                }
                $this->sharedresource_entry_definition_rec($mform, $fieldid, $iterators);
            }
        }
        return true;
    }

    public function sharedresource_entry_definition_rec(&$mform, $nodeid, &$iterators) {
        global $DB;

        if (!array_key_exists($nodeid, $this->METADATATREE)) {
            print_error('metadatastructureerror', 'sharedresource');
        }

        $config = get_config('sharedresource', $this->namespace);

        // Special trap : Classification taxon,is made of two fields.
        if ($this->METADATATREE[$nodeid]['name'] == 'TaxonPath') {
            $source = $this->METADATATREE['9_2_1'];
            if (!empty($source['internalref']) && preg_match("/table=(.*?)&idfield=(.*?)&entryfield=(.*?)&treefield=(.*?)&treestart=(.*?)(?:&context\{(.*?)\})?/",
                $source['internalref'], $matches)) {
                $table = $matches[1];
                $idfield = $matches[2];
                $entryfield = $matches[3];
                $treefield = $matches[4];
                $treestart = $matches[5];
                $context = @$matches[6];
                // We can get Classification list from internal ref.
                sharedresource_entry_definition_taxum($mform, $table, $idfield, $entryfield, $treefield, $treestart, $context);
            }
            return;
        }

        // Common case.
        $generic = $this->METADATATREE[$nodeid]['name'];
        if ($this->METADATATREE[$nodeid]['type'] == 'category') {
            $mform->addElement('header', $generic, get_string(str_replace(' ', '_', strtolower($generic)), 'sharedresource'));
            $mform->addElement('hidden', $generic, 1);
            foreach (array_keys($this->METADATATREE[$nodeid]['childs']) as $fieldid) {
                $metadataswitch = $mform->metadataswitch.$fieldid;
                if ($config->$metadataswitch) {
                    $this->sharedresource_entry_definition_rec($mform, $fieldid);
                }
            }
        } else if ($this->METADATATREE[$nodeid]['type'] == 'list') {

            // Get existing records in db.
            list($mtdsql, $mtdparams) = $DB->get_in_or_equal($this->ALLSOURCES);
            $select = " entryid = ? AND namespace {$mtdsql} and name LIKE '{$generic}:%' ";
            $elementinstances = $DB->get_records_select('sharedresource_metadata', $select, array_merge($this->entryid, $mtdparams));

            // Iterate on instances.
            $metadataswitch = $mform->metadataswitch.$nodeid;
            if ($instances && $config->$metadataswitch) {
                $iterators[] = 0;
                foreach ($instances as $instance) {
                    $this->sharedresource_entry_definition_rec($mform, $fieldid, $iterators);
                    $iterztor = array_pop($iterators);
                    $iterator++;
                    array_push($iterators, $iterator);
                }
            }
        } else {
            $metadataswitch = $mform->metadataswitch.$nodeid;
            if (!empty($config->$metadataswitch)) {
                $this->sharedresource_entry_definition_scalar($mform, $this->METADATATREE[$nodeid]);
            }
        }
    }

    /**
     * prints a full configuration form allowing element by element selection against the user profile
     * regarding to metadata
     */
    public function configure($config) {
        global $OUTPUT;
        // Initiate.

        $template = new StdClass;

        $template->selallstr = get_string('selectall', 'sharedresource');
        $template->selnonestr = get_string('selectnone', 'sharedresource');

        $template->pluginnamestr = get_string('pluginname', 'sharedmetadata_'.$this->namespace);
        $template->fieldnamestr = get_string('fieldname', 'sharedresource');
        $template->systemstr = get_string('system', 'sharedresource');
        $template->indexerstr = get_string('indexer', 'sharedresource');
        $template->authorstr = get_string('author', 'sharedresource');
        $template->mandatorystr = get_string('mandatory', 'sharedresource');
        $template->widgetstr = get_string('widget', 'sharedresource');
        $template->namespace = $this->namespace;

        $template->haschilds = false;
        if (!empty($this->METADATATREE['0']['childs'])) {
            foreach (array_keys($this->METADATATREE['0']['childs']) as $fieldid) {
                $template->childs[] = $this->print_configure_rec($fieldid);
            }
            $template->haschilds = true;
        }

        return $OUTPUT->render_from_template('mod_sharedresource/metadataform', $template);
    }

    /**
     * widget classes are automagically loaded when bound in activewidgets
     * @see .§configure()
     * @return an object representing a child.
     */
    public function print_configure_rec($fieldid, $parentnode = '0') {
        static $indent = 0;
        static $activewidgets = null;

        if (is_null($activewidgets)) {
            $activewidgets = unserialize(get_config('sharedresource', 'activewidgets'));
        }

        $template = new StdClass;
        $config = get_config('sharedmetadata_'.$this->namespace);
        $field = $this->METADATATREE[$fieldid];

        // Extract parent id.
        if (preg_match('/(.*)_\d+$/', $fieldid, $matches)) {
            $parentid = $matches[1];
        } else {
            $parentid = 0;
        }

        // First get for children.
        $i = 1;
        $template->haschilds = false;
        if ($field['type'] == 'category') {
            if (!empty($field['childs'])) {
                $template->haschilds = true;
                foreach (array_keys($field['childs']) as $childfieldid) {
                    $indent++;
                    $template->childs[] = $this->print_configure_rec($childfieldid, $parentnode.'_'.$i);
                    $indent--;
                    $i++;
                }
            }
        }

        $template->fieldid = $fieldid;

        $template->isparentclass = '';
        if ($template->haschilds) {
            $template->isparentclass = 'mtd-parent';
        }

        if (!array_key_exists($fieldid, $this->METADATATREE)) {
            print_error('metadatastructureerror', 'sharedresource');
        }
        $template->cskw = 'config_'.$this->namespace.'_system_write_'.$fieldid;
        $template->skw = $this->namespace.'-system-write-'.$fieldid;
        $template->cskr = 'config_'.$this->namespace.'_system_read_'.$fieldid;
        $template->skr = $this->namespace.'-system-read-'.$fieldid;
        $template->sparentclassw = $this->namespace.'-system-write-'.$parentid;
        $template->sparentclassr = $this->namespace.'-system-read-'.$parentid;
        $template->scheckedw = (!empty($config->{$template->cskw})) ? 'checked="checked"' : '';
        $template->scheckedr = (!empty($config->{$template->cskr})) ? 'checked="checked"' : '';

        $template->cikw = 'config_'.$this->namespace.'_indexer_write_'.$fieldid;
        $template->ikw = $this->namespace.'-indexer-write-'.$fieldid;
        $template->cikr = 'config_'.$this->namespace.'_indexer_read_'.$fieldid;
        $template->ikr = $this->namespace.'-indexer-read-'.$fieldid;
        $template->iparentclassw = $this->namespace.'-indexer-write-'.$parentid;
        $template->iparentclassr = $this->namespace.'-indexer-read-'.$parentid;
        $template->icheckedw = (!empty($config->{$template->cikw})) ? 'checked="checked"' : '';
        $template->icheckedr = (!empty($config->{$template->cikr})) ? 'checked="checked"' : '';

        $template->cakw = 'config_'.$this->namespace.'_author_write_'.$fieldid;
        $template->akw = $this->namespace.'-author-write-'.$fieldid;
        $template->cakr = 'config_'.$this->namespace.'_author_read_'.$fieldid;
        $template->akr = $this->namespace.'-author-read-'.$fieldid;
        $template->aparentclassw = $this->namespace.'-author-write-'.$parentid;
        $template->aparentclassr = $this->namespace.'-author-read-'.$parentid;
        $template->acheckedw = (!empty($config->{$template->cakw})) ? 'checked="checked"' : '';
        $template->acheckedr = (!empty($config->{$template->cakr})) ? 'checked="checked"' : '';

        $template->cmk = 'config_'.$this->namespace.'_mandatory_'.$fieldid;
        $template->mparentclass = $this->namespace.'-mandatory-'.$parentid;
        $template->mk = $this->namespace.'-mandatory-'.$fieldid;
        $template->mchecked = (!empty($config->{$template->cmk})) ? 'checked="checked"' : '';

        $template->wk = $this->namespace.'-widget-'.$fieldid;
        $template->wn = 'widget_'.$this->namespace.'_'.$fieldid;

        $widgetchecked = '';
        $template->wchecked = '';
        if (!empty($activewidgets)) {
            foreach ($activewidgets as $key => $widget) {
                // We search in active widgets if this widget is selected.
                if ($widget->id == $fieldid) {
                    $template->wparentclass = $this->namespace.'-widget-'.$fieldid;
                    $template->wchecked = 'checked="checked"';
                    break;
                }
            }
        }

        $template->haswidget = isset($field['widget']);
        $template->indentsize = 15 * $indent;
        $fieldname = strtolower(clean_string_key($field['name']));
        $template->fieldname = get_string($fieldname, 'sharedmetadata_'.$this->namespace);

        $template->iscategory = ($field['type'] == 'category');

        if ($field['type'] == 'category') {
            if ($parentnode == '0') {
                $template->nodeclass = ' class="rootnode"';
            }
        }

        $template->isrootnode = ($parentnode == '0');

        if ($parentnode != '0') {
            if (empty($template->scheckedr)) {
                $template->sdisabled = "disabled";
            }
            if (empty($template->icheckedr)) {
                $template->idisabled = "disabled";
            }
            if (empty($template->acheckedr)) {
                $template->adisabled = "disabled";
            }
            if (empty($template->acheckedr) && empty($template->icheckedr) && empty($template->scheckedr)) {
                $template->wdisabled = "disabled";
            }
        }

        return $template;
    }

    public function get_cardinality($element, &$fields, &$cardinality) {
        if (!($this->METADATATREE[$element]['type'] == 'category' || $this->METADATATREE[$element]['type'] == 'root')) {
            return;
        }
        foreach ($this->METADATATREE[$element]['childs'] as $elem => $value) {
            if ($value == 'list') {
                $cardinality[$elem] = 0;
                foreach ($fields as $field) {
                    if (strpos($field->element, "$elem:") === 0) {
                        $cardinality[$elem]++;
                    }
                }
            }
            $this->get_cardinality($elem, $fields, $cardinality);
        }
    }

    /**
     * Special form handler for Taxum
     *
     */
    public function sharedresource_entry_definition_taxum(&$mform, $table, $idfield, $entryfield, $context) {
        global $DB;

        if (empty($idfield) || empty($entryfield)) {
            $optionsrec = $DB->get_records_select($table, "$context", array(), "$idfield, $entryfield", "$idfield");
            foreach ($optionssrec as $id => $option) {
                $options[$id] = " $id $option";
            }
            $mform->addElement('select', 'lom_TaxonPath', get_string('taxonpath', 'sharedmetadata_'.$this->namespace), $options);
        }
    }

    // a weak implementation using only in resource title and description.
    public function search_definition(&$mform) {

        // Search text box.
        $mform->addElement('text', 'search', get_string('searchfor', 'sharedresource'), array('size' => '35'));
        // Checkboxes to choose search scope.
        $searchin = array();
        $searchin[] = &MoodleQuickForm::createElement('checkbox', 'title', '', get_string('title', 'sharedresource'));
        $searchin[] = &MoodleQuickForm::createElement('checkbox', 'description', '', get_string('description', 'sharedresource'));
        $mform->addGroup($searchin, 'searchin', get_string('searchin', 'sharedresource'), array(' '), false);

        // Set defaults.
        $mform->setDefault('title', 1);
        $mform->setDefault('description', 1);
        return false;
    }

    public function search(&$fromform, &$result) {
        global $CFG, $DB;

        $fromform->title = isset($fromform->title) ? true : false;
        $fromform->description = isset($fromform->description) ? true : false;
        // If the search criteria is left blank then this is a complete browse.
        if ($fromform->search == '') {
            $fromform->search = '*';
        }
        if ($fromform->section == 'block') {
            $fromform->title = true;
            $fromform->description = true;
        }
        $searchterms = explode(' ', $fromform->search); // Search for words independently.
        foreach ($searchterms as $key => $searchterm) {
            if (strlen($searchterm) < 2) {
                unset($searchterms[$key]);
            }
        }
        // No valid search terms so lets just open it up.
        if (count($searchterms) == 0) {
            $searchterms[] = '%';
        }
        $search = trim(implode(" ", $searchterms));
        // To allow case-insensitive search for postgesql.
        if ($CFG->dbfamily == 'postgres') {
            $LIKE = 'ILIKE';
            $NOTLIKE = 'NOT ILIKE'; // Case-insensitive.
            $REGEXP = '~*';
            $NOTREGEXP = '!~*';
        } else {
            $LIKE = 'LIKE';
            $NOTLIKE = 'NOT LIKE';
            $REGEXP = 'REGEXP';
            $NOTREGEXP = 'NOT REGEXP';
        }
        $titlesearch = '';
        $descriptionsearch = '';
        foreach ($searchterms as $searchterm) {
            if ($titlesearch) {
                $titlesearch .= ' AND ';
            }
            if ($descriptionsearch) {
                $descriptionsearch .= ' AND ';
            }
            if (substr($searchterm, 0, 1) == '+') {
                $searchterm          = substr($searchterm, 1);
                $titlesearch        .= " title $REGEXP '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
                $descriptionsearch  .= " description $REGEXP '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
            } else if (substr($searchterm, 0, 1) == "-") {
                $searchterm          = substr($searchterm, 1);
                $titlesearch        .= " title $NOTREGEXP '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
                $descriptionsearch  .= " description $NOTREGEXP '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
            } else {
                $titlesearch        .= ' title '.       $LIKE .' \'%'. $searchterm .'%\' ';
                $descriptionsearch  .= ' description '. $LIKE .' \'%'. $searchterm .'%\' ';
            }
        }
        $selectsql  = '';
        $selectsqlor  = '';
        $selectsql .= '{sharedresource_entry} WHERE (';
        $selectsqlor = '';
        if ($fromform->title && $search) {
            $selectsql .= $titlesearch;
            $selectsqlor = ' OR ';
        }
        if ($fromform->description && $search) {
            $selectsql .= $selectsqlor.$descriptionsearch;
            $selectsqlor = ' OR ';
        }
        $selectsql .= ')';
        $sort = "title ASC";
        $page = '';
        $recordsperpage = SHAREDRESOURCE_SEARCH_LIMIT;
        if ($fromform->title || $fromform->description) {
            // When given a complete wildcard, then this is browse mode.
            if ($fromform->search == '*') {
                $resources = $DB->get_records('sharedresource_entry', array(), $sort); // A VERIFIER !!!
            } else {
                $sql = 'SELECT * FROM '. $selectsql .' ORDER BY '. $sort;
                $resources = $DB->get_records_sql($sql, array(), $page, $recordsperpage); // A VERIFIER !!!
            }
        }
        // Append the results.
        $entryclass = \mod_sharedresource\entry_factory::get_entry_class();
        if (!empty($resources)) {
            foreach ($resources as $resource) {
                $result[] = new $entryclass($resource);
            }
        }
    }

    /**
     * generates a full XML metadata document attached to the resource entry
     */
    public function get_metadata(&$shrentry, $namespace = null) {
        global $DB;

        $schema = get_config('sharedresource', 'schema');

        if (empty($namespace)) {
            ($namespace = $schema) || ($namespace = 'lom');
        }

        // Cleanup some values.
        if ($shrentry->description == '$@NULL@$') {
            $shrentry->description = '';
        }

        // Default.
        $lang = substr(current_language(), 0, 2);
        list($mtdsql, $mtdparams) = $DB->get_in_or_equal($this->ALLSOURCES);
        $fields = $DB->get_records_select('sharedresource_metadata', " entryid = ? AND namespace $mtdsql ", array_merge(array($shrentry->id), $mtdparams));

        // Construct cardinality table.
        $cardinality = array();
        $this->get_cardinality('0', $fields, $cardinality);

        foreach ($fields as $field) {
            $parts = explode(':', $field->element);
            $element = $parts[0];
            $path = @$parts[1];
            if (!isset($metadata[$element])) {
                 $metadata[$element] = array();
            }
            $metadata[$element][$path] = $field->value;
            if ($element == '3_4') {
                $lang = $field->value;
            }
        }

        $languageattr = 'language="'.$lang.'"';
        $lom = $this->lomHeader();
        $tmpstr = '';

        if ($this->generate_xml('0', $metadata, $languageattr, $tmpstr, $cardinality, '')) {
            $lom .= $tmpstr;
        }
        $lom .= "
            </lom:lom>
            ";
        return $lom;
    }

    /**
     * retrieves an eventual metadata parser
     *
     */
    public function get_parser() {
        global $CFG;

        if (file_exists($CFG->dirroot.'/mod/sharedresource/plugins/metadata_xml_parser_'.$this->config->scheme.'/xmlparser.php')) {
            require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$this->config->scheme.'/xmlparser.php');
            $parserclass = 'metadata_xml_parser_'.$this->config->scheme;
            return new $parserclass();
        }
        return null;
    }

    /**
     * tells the outside world if we know this node in the current standard.
     * @param string a Dublin Core node identifier.
     * @return true if the node is known
     */
    public function hasNode($nodekey) {
        if (empty($this->METADATATREE)) {
            return false;
        }
        return array_key_exists($nodekey, $this->METADATATREE);
    }

    /**
     * set the current resource entry id for this plugin
     */
    public function setEntry($entryid) {
        $this->entryid = $entryid;
    }

    /**
     * keyword have a special status as stored both in metadata and in entry record
     */
    abstract public function getKeywordValues($metadata);

    /**
     * get the metadata node identity for title
     */
    abstract public function getTitleElement();

    /**
     * get the metadata node identity for description
     */
    abstract public function getDescriptionElement();

    /**
     * get the metadata node identity for keyword
     */
    abstract public function getKeywordElement();

    /**
     * get the metadata node identity for taxonomy purpose
     */
    public function getTaxonomyPurposeElement() {
        return null;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    public function getTaxonomyValueElement() {
        return null;
    }

    /**
     * versionned sharedresources entry must use Relation elements to link each other.
     */
    public function getVersionSupportElement() {
        return null;
    }

    /**
     * Get the list of fields that can be searched by a simple search.
     * As a default, any 'text' type field can be used to find a resource.
     */
    public function getSimpleSearchElements() {

        $simpleelmeents = [];

        foreach ($this->METADATATREE as $index => $element) {
            if ($element['type'] == 'text') {
                $simpleelements[] = $index;
            }
        }

        return $simpleelements;
    }

    /**
     * Get the next entry reference using Relation metadata record. We should use only one
     * hasversion <-> isversionof chaining between resources at the moment.
     * @return the internal id of the next sharedresource entry in the version chaining, or our
     * self resource id if nothing is next.
     */
    public function getNext() {
        global $DB;

        $config = get_config('sharedresource');

        if (is_null($this->getVersionSupportElement())) {
            return $this->entryid;
        }

        $select = "
            entryid = :entryid AND
            element LIKE :element AND
            value = :value AND
            namespace = :namespace
        ";
        $params = [
            'entryid' => $this->entryid,
            'element' => '7_1:%',
            'value' => 'hasversion',
            'namespace' => $this->namespace
        ];
        $versionelement = $DB->get_record_select('sharedresource_metadata', $select, $params);
        if (!$versionelement) {
            return $this->entryid;
        }
        $versionmetadata = new metadata($this->entryid, $versionelement->element, $versionelement->value, $this->namespace);
        $resourceelementid = metadata::to_instance('7_2_1_2', $versionmetadata->get_instance_id());
        $params = [
            'entryid' => $this->entryid,
            'element' => $resourceelementid,
            'namespace' => $this->namespace
        ];
        $resourceid = $DB->get_field('sharedresource_metadata', 'value', $params);
        return $resourceid;
    }

    /**
     * Get the next entry reference using Relation metadata record. We should use only one
     * hasversion <-> isversionof chaining between resources at the moment.
     * @return the internal id of the next sharedresource entry in the version chaining, or our
     * self resource id if nothing is next.
     */
    public function getPrevious() {
        global $DB;

        $config = get_config('sharedresource');

        if (is_null($this->getVersionSupportElement())) {
            return $this->entryid;
        }

        $select = "
            entryid = :entryid AND
            element LIKE :element AND
            value = :value AND
            namespace = :namespace
        ";
        $params = [
            'entryid' => $this->entryid, 
            'element' => '7_1:%', 
            'value' => 'isversionof', 
            'namespace' => $this->namespace
        ];
        $versionelement = $DB->get_record_select('sharedresource_metadata', $select, $params);
        if (!$versionelement) {
            return $this->entryid;
        }
        $versionmetadata = new metadata($this->entryid, $versionelement->element, $versionelement->value, $this->namespace);
        $resourceelementid = metadata::to_instance('7_2_1_2', $versionmetadata->get_instance_id());
        $params = [
            'entryid' => $this->entryid,
            'element' => $resourceelementid,
            'namespace' => $this->namespace
        ];
        $resourceid = $DB->get_field('sharedresource_metadata', 'value', $params);
        return $resourceid;
    }

    public function setNext($sharedresourceentry) {
        $this->setRelation($sharedresourceentry, 'hasversion');
    }

    public function setPrevious($sharedresourceentry) {
        $this->setRelation($sharedresourceentry, 'isversionof');
    }

    /**
     * Adds a relationship between resources based on metadata.
     * @param object $sharedresourceentry
     * @param string $kind
     */
    protected function setRelation($sharedresourceentry, $kind) {
        global $DB;

        $config = get_config('sharedresource');

        if (is_null($this->getVersionSupportElement())) {
            return $this->entryid;
        }

        $select = "
            entryid = :entryid AND
            element LIKE :element AND
            value = :value AND
            namespace = :namespace
        ";
        $params = ['entryid' => $this->entryid, 'element' => '7_1:%', 'value' => $kind, 'namespace' => $this->namespace];
        $versionelementid = $DB->get_field_select('sharedresource_metadata', 'element', $select, $params);

        if ($versionelementid) {
            // We have already.
            throw new exception($kind.' relation already registered for resource '.$this->entryid.'.');
        }

        // We do not have this and must register a new Relation.

        $versionsupport = $this->getVersionSupportElement();
        $mainnodeid = $versionsupport['main'];
        $mainnode = metadata::instance($this->entryid, $mainnodeid.':0', $this->namespace, false);
        $maxoccurrence = $mainnode->get_max_occurrence();
        if (!is_numeric($maxoccurrence)) {
            $maxoccurrence = 0;
        }

        $entrynodeid = $versionsupport['entry'];
        $kindnodeid = $versionsupport['kind'];

        // Register the relation kind.
        $kindnode = new metadata($this->entryid, $kindnodeid.':'.$maxoccurrence.'_0', $kind, $this->namespace);
        // debug_trace("{$this->entryid} recording $kindnodeid.':'.$maxoccurrence.'_0', $kind as {$sharedresourceentry->id}");
        $kindnode->add_instance();

        // Register the linked sharedresource.
        $kindnode = new metadata($this->entryid, $entrynodeid.':'.$maxoccurrence.'_0_0_0', $sharedresourceentry->id, $this->namespace);
        $kindnode->add_instance();

    }

    /**
     * add keywords metadata entries from a comma separated list
     * of values. Each plugin know how and where to put values
     */
    abstract public function setKeywords($keywords);

    /**
     * get any element definition given its node number.
     * @param string $id an element node index in the m[_n[_o...]] format
     * @return object an element description
     */
    public function getElement($id) {
        $element = new StdClass;
        $element->id = $id;
        $element->name = $this->METADATATREE[$id]['name'];
        $element->type = $this->METADATATREE[$id]['type'];
        $element->widget = @$this->METADATATREE[$id]['widget'];
        $element->source = $this->METADATATREE[$id]['source'];

        // Get the islist option for this element.
        if (strpos($id, '_') !== false) {
            $parentid = preg_replace('/_[^_]+$/', '', $id);
        } else {
            $parentid = 0;
        }

        $element->islist = $this->METADATATREE[$parentid]['childs'][$id] == 'list';
        return $element;
    }

    /**
     * get any element definition given its node number.
     * @param string $id an element node index in the m[_n[_o...]] format
     * @return object an element description
     */
    public function getElementChilds($id) {
        return @$this->METADATATREE[$id]['childs'];
    }

    /**
     * Get any value of any element given its node number and its instance path
     * @param string $id an element node index in the x[_y[_z...]] format
     * @param int $entryid the resource entry id
     * @param string $instanceid a value in the metadata tree given by the  i[_j[_k...]] index format.
     * @return a metadata value
     */
    public function getElementValue($entryid, $elementid, $instanceid) {
        global $DB;

        $metadataid = "$elementid:$instanceid";
        $element = $this->get_element($elementid);

        return $DB->get_field('sharedresource_metadata', 'value', array('entryid' => $entryid, 'element' => $metadataid, 'namespace' => $element->source));
    }

    /**
     * A generic method that allows changing a simple text value
     *
     */
    public function setTextElementValue($element, $item, $value) {
        global $DB;

        if (empty($this->entryid)) {
            throw new coding_exception('setLocation() : sharedresource entry is null or empty. This should not happen. Please inform developers.');
        }

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
        $mtdrec->entryid = $this->entryid;
        $mtdrec->element = "$element:$item";
        // Any element value will be stored with the element original source.
        // $mtdrec->namespace = $this->METADATATREE[$element]['source'];
        // Temporary solution : record with current metadata namespace.
        $mtdrec->namespace = $this->namespace;
        $mtdrec->value = $value;

        if ($oldrec = $DB->get_record('sharedresource_metadata', array('entryid' => $this->entryid, 'element' => $mtdrec->element, 'namespace' => $mtdrec->namespace))) {
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
    public function setTitle($title) {
        global $DB;

        if (empty($this->entryid)) {
            throw new coding_exception('setLocation() : sharedresource entry is null or empty. This should not happen. Please inform developers.');
        }

        $titleElement = $this->getTitleElement();
        $titlekey = '$titleElement:0_0';
        $titlesource = $this->METADATATREE[$titleElement]['source'];
        $titlesource = $this->namespace;

        $DB->delete_records('sharedresource_metadata', array('entryid' => $this->entryid, 'namespace' => $titlesource, 'element' => $titlekey));
        $mtdrec = new StdClass;
        $mtdrec->entryid = $this->entryid;
        $mtdrec->element = $titlekey;
        $mtdrec->namespace = $titlesource;
        $mtdrec->value = $title;

        return $DB->insert_record('sharedresource_metadata', $mtdrec);
    }

    /**
     * records master description in metadata flat table from db attributes
     * description element identification is given by each concrete plugin
     */
    public function setDescription($description) {
        global $DB;

        if (empty($this->entryid)) {
            throw new coding_exception('setDescription() : sharedresource entry is null or empty. This should not happen. Please inform developers.');
        }

        $descriptionelement = $this->getDescriptionElement();
        $desckey = '$descriptionelement:0_0';
        // At the moment we do not record elements under their own source.
        // $descriptionsource = $this->METADATATREE[$descriptionelement]['source'];
        $descriptionsource = $this->namespace;

        $DB->delete_records('sharedresource_metadata', array('entryid' => $this->entryid, 'namespace' => $descriptionsource, 'element' => $desckey));

        $mtdrec = new StdClass;
        $mtdrec->entryid = $this->entryid;
        $mtdrec->element = $desckey;
        $mtdrec->namespace = $descriptionsource;
        $mtdrec->value = $description;

        return $DB->insert_record('sharedresource_metadata', $mtdrec);
    }

    /**
     * records resource physical lcoation in metadata flat table from db attributes?
     * location element identification is given by each concrete plugin
     */
    public function setLocation($location) {
        global $DB;

        if (empty($this->entryid)) {
            throw new coding_exception('setLocation() : sharedresource entry is null or empty. This should not happen. Please inform developers.');
        }

        $locationElement = $this->getLocationElement();
        $locationkey = '$locationElement:0_0';
        // $locationsource = $this->METADATATREE[$locationElement]['source'];
        $locationsource = $this->namespace;

        $DB->delete_records('sharedresource_metadata', array('entryid' => $this->entryid, 'namespace' => $locationsource, 'element' => $locationkey));
        $mtdrec = new StdClass;
        $mtdrec->entryid = $this->entryid;
        $mtdrec->element = $locationkey;
        $mtdrec->namespace = $locationsource;
        $mtdrec->value = $location;

        return $DB->insert_record('sharedresource_metadata', $mtdrec);
    }

    /** 
    * Tells if the value can be interpretated as an entry id, so that a sharedresource
    * reference or link can be built for rendering.
    */
    public function isResourceIndex($nodeid) {
        return false;
    }

    /**
     * Gets a default value for a node or node instance if exists.
     * Default value is returned if any of the default mask match the input.
     *
     * @param string $elementkey A full elementkey (m_n_o:x_y_z) or a node id (m_n_o)
     *
     */
    public function defaultValue($elementkey) {

        if (strpos($elementkey, ':') !== false) {
            list($elementid, $instanceid) = explode(':', $elementkey);
            if (!array_key_exists('default', $this->METADATATREE[$elementid])) {
                return null;
            }
            if (!empty($this->METADATATREE[$elementid]['default'])) {

                foreach ($this->METADATATREE[$elementid]['default'] as $mask => $defaultvalue) {
                    if ($mask == '*') {
                        // Global wildcard will serve in fine... after all thiner masks.
                        continue;
                    }

                    $pregmask = str_replace('*', '[0-9]+', $mask); // A wildcard means any node number.
                    if (preg_match('/^'.$pregmask.'$/', $instanceid)) {
                        return $defaultvalue;
                    }
                }
                return @$this->METADATATREE[$elementid]['default']['*'];
            }
        }

        return @$this->METADATATREE[$elementkey]['default']['*'];
    }

    /**
     *
     */
    public function delete_classifications($classifid, $namespace) {
        $classifinfo = $this->getTaxumpath();

        $sources = metadata::instances_by_node(null, $namespace, $classifinfo['source'], $classifid);

        if ($sources) {
            foreach ($matchingsources as $source) {
                assert(1);
                /*
                 * Say a source instance id is f.e. 9_2_1:0_3_0,
                 * the taxon id and entry would be : 9_2_2_1:0_3_0_0 and 9_2_2_2:0_3_0_0
                 */
                 // Find master node ID of the taxon from the source id, same with instance indexes :
            }
        }
    }

    /**
     * Finds all resources that are using this taxon in metadata and removes all
     * the metadata records related to this binding.
     */
    public function unbind_taxon($classifid, $taxonid) {

        $namespace = get_config('sharedresource', 'schema');

        $classifinfo = $this->getTaxumpath();

        // Search all sources that are using this classification as a source.
        $matchingsources = metadata::instances_by_node(null, $namespace, $classifinfo['source'], $classifid);

        if (!empty($matchingsources)) {
            foreach ($matchingsources as $source) {

                /*
                 * Say a source instance id is f.e. 9_2_1:0_3_0,
                 * the taxon id and entry would be : 9_2_2_1:0_3_0_0 and 9_2_2_2:0_3_0_0
                 *
                 * a single source entry may have several taxonids registered (multiple binding).
                 * those are all childs of the taxon root element 9_2_2:0_3_0
                 */
                // Find master node ID of the taxon from the source id, same with instance indexes :
                $instanceid = $source->get_instance_id(); // a x_y_z instance index path

                // Get all subinstances of the taxon root. they are taxonids or taxonentries.
                $elementid = $classifinfo['main'].':'.$instanceid;
                $taxonsubparts = metadata::instances_by_element($source->entryid, $namespace, $elementid, null, true);

                if (!empty($taxonsubparts)) {
                    foreach ($taxonsubparts as $elm) {
                        if ($elm->get_node_id() == $classifinfo['main'] && $elm->value == $taxonid) {
                            // Destroy both elements id and entry of same instanceid :
                            $params = array('entryid' => $source->entryid,
                                            'namespace' => $namespace,
                                            'element' => $elm->get_element_key());
                            $DB->delete_records('sharedresource_metadata', $params);

                            // Related taxon element has his node id and same instanceid.
                            $taxonentryelm = metadata::to_instance($classifinfo['entry'], $elm->get_instance_id());
                            $params = array('entryid' => $source->entryid,
                                            'namespace' => $namespace,
                                            'element' => $taxonentryelm->get_element_key());
                            $DB->delete_records('sharedresource_metadata', $params);
                        }
                    }
                }

                /*
                 * We do NOT delete the source element itself as there might be some taxon entries that still match.
                 */
            }
        }
    }

    /**
     * loads an externally defined default values for the schema
     * the provided default tree must provide additional default keys
     * for relevant nodes :
     *
     * $METADATATREE_DEFAULT = array (
     *       'lomfr' => array(
     *          '1_1_1' => array('default' => 'MyCatalog'
     *       )
     *    )
     * );
     *
     * would define a default value for the "Catalog field" of LOM based schemas
     */
    public function load_defaults($mtddefaults) {

        $config = get_config('sharedresource');

        if (!empty($mtddefaults)) {
            if (array_key_exists($config->schema, $mtddefaults)) {
                foreach ($mtddefaults[$config->schema] as $key => $default) {
                    if (strpos($key, ':') !== false) {
                        list($elementid, $instanceid) = explode(':', $key);
                        $this->METADATATREE[$elementid]['default'][$instanceid] = $default['default'];
                    } else {
                        $this->METADATATREE[$key]['default']['*'] = $default['default'];
                    }
                }
            }
        }
    }

    /**
     * a static factory. Gives back a metadata object loaded with default values
     *
     */
    public static function load_mtdstandard($schema) {
        global $CFG;

        if (file_exists($CFG->dirroot.'/mod/sharedresource/plugins/'.$schema.'/plugin.class.php')) {

            include_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$schema.'/plugin.class.php');

            $mtdclass = '\\mod_sharedresource\\plugin_'.$schema;
            $mtdstandard = new $mtdclass();

            if (!empty($CFG->METADATATREE_DEFAULTS)) {
                $mtdstandard->load_defaults($CFG->METADATATREE_DEFAULTS);
            }

            return $mtdstandard;
        }

        return false;
    }
}
