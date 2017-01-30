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
require_once($CFG->dirroot.'/mod/sharedresource/sharedresource_metadata_exception.class.php');
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

    protected $entryid; // The sharedresource entry id.

    public $pluginname;

    protected $namespace;

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

    function getNamespace() {
        return $this->namespace;
    }

    /**
    * Form handler for scalar value (regular case)
    */
    function sharedresource_entry_definition_scalar(&$mform, &$element) {

        if (empty($this->namespace)) {
            throw new coding_exception('sharedresource_entry_definition_scalar() : Trying to use on core mtd plugin class. No namespace assigned. Please inform developers.');
        }

        if ($element['type'] == 'select') {
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
        global $CFG, $DB;

        $config = get_config('sharedresource_'.$this->namespace);

        $iterators = array();
        foreach (array_keys($this->METADATATREE['0']['childs']) as $fieldid) {
            if (has_capability('mod/sharedresource:systemmetadata', $this->context)) {
                $metadataswitch = "config_lom_system_";
            } elseif (has_capability('mod/sharedresource:indexermetadata', $this->context)) {
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
                    if ($instances = $DB->get_records_select('sharedresource_metadata', " entry_id = ? AND namespace $mtdsql AND name LIKE '$generic:%' ", array_merge(array($this->entryid), $mtdparams))) {
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

    function sharedresource_entry_definition_rec(&$mform, $nodeid, &$iterators) {
        global $CFG, $DB;

        if (!array_key_exists($nodeid, $this->METADATATREE)) {
            print_error('metadatastructureerror', 'sharedresource');
        }

        $config = get_config('sharedresource_'.$this->namespace);

        // Special trap : Classification taxon,is made of two fields.
        if ($this->METADATATREE[$nodeid]['name'] == 'TaxonPath') {
            $source = $this->METADATATREE['9_2_1'];
            if (!empty($source['internalref']) && preg_match("/table=(.*?)&idfield=(.*?)&entryfield=(.*?)&treefield=(.*?)&treestart=(.*?)(?:&context\{(.*?)\})?/", $source['internalref'], $matches)) {
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
        } elseif ($this->METADATATREE[$nodeid]['type'] == 'list') {
            // get exiting records in db
            list($mtdsql, $mtdparams) = $DB->get_in_or_equal($this->ALLSOURCES);
            $elementinstances = $DB->get_records_select('sharedresource_metadata', " entry_id = ? AND namespace {$mtdsql} and name LIKE '{$generic}:%' ", array_merge($this->entryid, $mtdparams));
            // iterate on instances
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
    function configure($config) {
        // Initiate.
        $selallstr = get_string('selectall', 'sharedresource');
        $selnonestr = get_string('selectnone', 'sharedresource');

        echo '<legend><b>&nbsp;'.get_string('pluginname', 'sharedmetadata_'.$this->namespace).'</b></legend>';
        echo '<br/><center>';
        echo '<table border="1px" width="90%"><tr><td colspan="4">';
        echo '</td></tr>';
        echo '<tr><td width="30%"><b>&nbsp;'.get_string('fieldname', 'sharedresource').'</b></td>';
        echo '<td class="mtdsetting"><b>'.get_string('system', 'sharedresource').'</b><br/><a href="javascript:selectall(\'system\', \''.$this->namespace.'\')">'.$selallstr.'</a>/<a href="javascript:selectnone(\'system\', \''.$this->namespace.'\')">'.$selnonestr.'</a></td>';
        echo '<td class="mtdsetting"><b>'.get_string('indexer', 'sharedresource').'</b><br/><a href="javascript:selectall(\'indexer\', \''.$this->namespace.'\')">'.$selallstr.'</a>/<a href="javascript:selectnone(\'indexer\', \''.$this->namespace.'\')">'.$selnonestr.'</a></td>';
        echo '<td class="mtdsetting"><b>'.get_string('author', 'sharedresource').'</b><br/><a href="javascript:selectall(\'author\', \''.$this->namespace.'\')">'.$selallstr.'</a>/<a href="javascript:selectnone(\'author\', \''.$this->namespace.'\')">'.$selnonestr.'</a></td>';
        echo '<td class="mtdsetting"><b>'.get_string('widget', 'sharedresource').'</b></td></tr>';
        echo '</table>';
        foreach (array_keys($this->METADATATREE['0']['childs']) as $fieldid) {
            echo '<table border="1px" width="90%"><tr><td colspan="4">';
            $this->print_configure_rec($fieldid);
            echo '</table>';
        }
        echo "</center>";
    }

    /**
     * widget classes are automagically loaded when gound in activewidgets
     * @see .§configure()
     */
    function print_configure_rec($fieldid, $parentnode = '0') {
        static $indent = 0;

        $config = get_config('sharedresource_'.$this->namespace);

        if (!array_key_exists($fieldid, $this->METADATATREE)) {
            print_error('metadatastructureerror', 'sharedresource');
        } 
        $field = $this->METADATATREE[$fieldid];
        $csk = 'config_'.$this->namespace.'_system_'.$fieldid;
        $sk = $this->namespace.'_system_'.$fieldid;
        $checked_system = (@$config->$csk) ? 'checked="checked"' : '';
        $cik = 'config_'.$this->namespace.'_indexer_'.$fieldid;
        $ik = $this->namespace.'_indexer_'.$fieldid;
        $checked_indexer = (@$config->$cik) ? 'checked="checked"' : '';
        $cak = 'config_'.$this->namespace.'_author_'.$fieldid;
        $ak = $this->namespace.'_author_'.$fieldid;
        $checked_author = (@$config->$cak) ? 'checked="checked"' : '';
        $wk = $this->namespace.'_widget_'.$fieldid;
        $wn = 'widget_'.$this->namespace.'_'.$fieldid;

        $activewidgets = unserialize(get_config(null, 'activewidgets'));
        $checked_widget = '';
        if (!empty($activewidgets)) {
            foreach ($activewidgets as $key => $widget) {
                if ($widget->id == $fieldid) {
                    $checked_widget = 'checked="checked"';
                }
            }
        }

        $indentsize = 15 * $indent;
        $fieldname = strtolower(clean_string_key($field['name']));
        $fieldname = get_string($fieldname, 'sharedmetadata_'.$this->namespace);

        if ($field['type'] == 'category') {
            echo '<tr';
            if ($parentnode == '0') {
                echo ' class="rootnode"';
            }
            echo '><td width="30%" align="left" style="padding-left:'.$indentsize.'px"><b>&nbsp;'.$fieldname.'</b></td>';
        } else {
            echo '<tr><td width="30%" align="left" style="padding-left:'.$indentsize.'px">&nbsp;'.$fieldname.'</td>';
        }
        if ($parentnode == '0') {
            echo '<td class="mtdsetting"><input id="'.$sk.'" type="checkbox" name="'.$csk.'" '.$checked_system.' value="1" onclick="toggle_childs(\''.$this->namespace.'\', \'system\', \''.$fieldid.'\')" /></td>';
            echo '<td class="mtdsetting"><input id="'.$ik.'" type="checkbox" name="'.$cik.'" '.$checked_indexer.' value="1" onclick="toggle_childs(\''.$this->namespace.'\', \'indexer\', \''.$fieldid.'\')" /></td>';
            echo '<td class="mtdsetting"><input id="'.$ak.'" type="checkbox" name="'.$cak.'" '.$checked_author.' value="1" onclick="toggle_childs(\''.$this->namespace.'\', \'author\', \''.$fieldid.'\')" /></td>';
            if (isset($field['widget'])) {
                echo '<td class="mtdsetting"><input id="'.$wk.'" type="checkbox" name="'.$wk.'" '.$checked_widget.' value="1"/></td></tr>';
            } else {
                echo '<td class="mtdsetting"></td></tr>';
            }
        } else {
            if ($checked_system == 'checked="checked"') {
                echo '<td class="mtdsetting"><input id="'.$sk.'" type="checkbox" name="'.$csk.'" '.$checked_system.' value="1" onclick="toggle_childs(\''.$this->namespace.'\', \'system\', \''.$fieldid.'\')"/></td>';
            } else {
                echo '<td class="mtdsetting"><input id="'.$sk.'" type="checkbox" name="'.$csk.'" '.$checked_system.' value="1" onclick="toggle_childs(\''.$this->namespace.'\', \'system\', \''.$fieldid.'\')" DISABLED /></td>';
            }
            if ($checked_indexer == 'checked="checked"') {
                echo '<td class="mtdsetting"><input id="'.$ik.'" type="checkbox" name="'.$cik.'" '.$checked_indexer.' value="1" onclick="toggle_childs(\''.$this->namespace.'\', \'indexer\', \''.$fieldid.'\')" /></td>';
            } else {
                echo '<td class="mtdsetting"><input id="'.$ik.'" type="checkbox" name="'.$cik.'" '.$checked_indexer.' value="1" onclick="toggle_childs(\''.$this->namespace.'\', \'indexer\', \''.$fieldid.'\')" DISABLED/></td>';
            }
            if ($checked_author == 'checked="checked"') {
                echo '<td class="mtdsetting"><input id="'.$ak.'" type="checkbox" name="'.$cak.'" '.$checked_author.' value="1" onclick="toggle_childs(\''.$this->namespace.'\', \'author\', \''.$fieldid.'\')"/></td>';
            } else {
                echo '<td class="mtdsetting"><input id="'.$ak.'" type="checkbox" name="'.$cak.'" '.$checked_author.' value="1" onclick="toggle_childs(\''.$this->namespace.'\', \'author\', \''.$fieldid.'\')" DISABLED/></td>';
            }
            if (isset($field['widget'])) {
                if ($checked_widget == 'checked="checked"') {
                    echo '<td class="mtdsetting"><input id="'.$wk.'" type="checkbox" name="'.$wn.'" '.$checked_widget.' value="1"/></td></tr>';
                } else {
                    echo '<td class="mtdsetting"><input id="'.$wk.'" type="checkbox" name="'.$wn.'" '.$checked_widget.' value="1"/></td></tr>';
                }
            } else {
                echo '<td class="mtdsetting"></td></tr>';
            }
        }
        $i = 1;
        if ($field['type'] == 'category') {
            if (!empty($field['childs'])) {
                foreach (array_keys($field['childs']) as $childfieldid) {
                    $indent++;
                    $this->print_configure_rec($childfieldid, $parentnode.'_'.$i);
                    $indent--;
                    $i++;
                }
            }
        }
    }

    function get_cardinality($element, &$fields, &$cardinality) {
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
    function sharedresource_entry_definition_taxum(&$mform, $table, $idfield, $entryfield, $context) {
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
    function search_definition(&$mform) {

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

    function search(&$fromform, &$result) {
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
            $searchterms[]= '%';
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
                $searchterm          = substr($searchterm,1);
                $titlesearch        .= " title $REGEXP '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
                $descriptionsearch  .= " description $REGEXP '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
            } else if (substr($searchterm,0,1) == "-") {
                $searchterm          = substr($searchterm,1);
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
        if (!empty($resources)) {
            foreach ($resources as $resource) {
                $result[] = new sharedresource_entry($resource);
            }
        }
    }

    /**
     * generates a full XML metadata document attached to the resource entry
     */
    function get_metadata(&$sharedresource_entry, $namespace = null) {
        global $SITE, $CFG, $DB;

        if (empty($namespace)) {
            ($namespace = $CFG->{'pluginchoice'}) or ($namespace = 'lom');
        }

        // Cleanup some values.
        if ($sharedresource_entry->description == '$@NULL@$') {
            $sharedresource_entry->description = '';
        }

        // Default.
        $lang = substr(current_language(), 0, 2);
        list($mtdsql, $mtdparams) = $DB->get_in_or_equal($this->ALLSOURCES);
        $fields = $DB->get_records_select('sharedresource_metadata', " entry_id = ? AND namespace $mtdsql ", array_merge(array($sharedresource_entry->id), $mtdparams));

        // Construct cardinality table.
        $cardinality = array();
        $this->get_cardinality('0', $fields, $cardinality);

        foreach ($fields as $field) {
            $parts = explode(':',$field->element);
            $element = $parts[0];
            $path = @$parts[1];
            if (!isset($metadata[$element])) {
                 $metadata[$element] =  array();
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
        $element->id = $id;
        $element->name = $this->METADATATREE[$id]['name'];
        $element->type = $this->METADATATREE[$id]['widget'];
        $element->source = $this->METADATATREE[$id]['source'];
        return $element;
    }

    /**
     * A generic method that allows changing a simple text value
     *
     */
    function setTextElementValue($element, $item, $value) {
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
        $mtdrec->entry_id = $this->entryid;
        $mtdrec->element = "$element:$item";
        // Any element value will be stored witht the element original source.
        $mtdrec->namespace = $this->METADATATREE[$element]['source'];
        $mtdrec->value = $value;

        if ($oldrec = $DB->get_record('sharedresource_metadata', array('entry_id' => $this->entryid, 'element' => $mtdrec->element, 'namespace' => $mtdrec->namespace))){
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

        if (empty($this->entryid)) {
            throw new coding_exception('setLocation() : sharedresource entry is null or empty. This should not happen. Please inform developers.');
        }

        $titleElement = $this->getTitleElement();
        $titlekey = '$titleElement:0_0';
        $titleSource = $this->METADATATREE[$titleElement]['source'];

        $DB->delete_records('sharedresource_metadata', array('entry_id' => $this->entryid, 'namespace' => $titleSource, 'element' => $titlekey));
        $mtdrec = new StdClass;
        $mtdrec->entry_id = $this->entryid;
        $mtdrec->element = $titlekey;
        $mtdrec->namespace = $titleSource;
        $mtdrec->value = $title;

        return $DB->insert_record('sharedresource_metadata', $mtdrec);
    }

    /**
     * records master description in metadata flat table from db attributes
     * description element identification is given by each concrete plugin
     */
    function setDescription($description) {
        global $DB;

        if (empty($this->entryid)) {
            throw new coding_exception('setDescription() : sharedresource entry is null or empty. This should not happen. Please inform developers.');
        }

        $descriptionElement = $this->getDescriptionElement();
        $desckey = '$descriptionElement:0_0';
        $descriptionSource = $this->METADATATREE[$descriptionElement]['source'];

        $DB->delete_records('sharedresource_metadata', array('entry_id' => $this->entryid, 'namespace' => $descriptionSource, 'element' => $desckey));

        $mtdrec = new StdClass;
        $mtdrec->entry_id = $this->entryid;
        $mtdrec->element = $desckey;
        $mtdrec->namespace = $descriptionSource;
        $mtdrec->value = $description;

        return $DB->insert_record('sharedresource_metadata', $mtdrec);
    }

    /**
     * records resource physical lcoation in metadata flat table from db attributes?
     * location element identification is given by each concrete plugin
     */
    function setLocation($location) {
        global $DB;

        if (empty($this->entryid)) {
            throw new coding_exception('setLocation() : sharedresource entry is null or empty. This should not happen. Please inform developers.');
        }

        $locationElement = $this->getLocationElement();
        $locationkey = '$locationElement:0_0';
        $locationSource = $this->METADATATREE[$locationElement]['source'];

        $DB->delete_records('sharedresource_metadata', array('entry_id' => $this->entryid, 'namespace' => $locationSource, 'element' => $locationkey));
        $mtdrec = new StdClass;
        $mtdrec->entry_id = $this->entryid;
        $mtdrec->element = $locationkey;
        $mtdrec->namespace = $locationSource;
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
