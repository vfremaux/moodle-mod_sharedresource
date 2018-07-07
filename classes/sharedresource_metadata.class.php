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
 * @package sharedresource
 *
 */
namespace mod_sharedresource;

use \StdClass;
use \moodle_exception;
use \coding_exception;

defined('MOODLE_INTERNAL') || die;

/**
 * \mod_sharedresource\metadata defines a sharedresource_metadata element
 *
 * This class provides all the functionality for a sharedresource_metadata
 * record of a metadata value.
 */
class metadata {

    /**
     * the element id as a m_n_o:x_y_z node identifier
     */
    public $element;

    /**
     * the currently used namespace. Proxies from sharedresource configuration
     */
    public $namespace;

    /**
     * The stored value
     */
    protected $value;

    /**
     * Boolean marker if the element has a database stored record.
     */
    public $isstored;

    /**
     * the sharedreosurce entry id this instance serves a value for
     */
    public $entryid;

    /**
     * The element identity as m_n_o_p nodeid.
     */
    protected $nodeid;

    /**
     * // The tree occurrence identity as w_x_y_z
     */
    protected $instanceid;

    /**
     * An array form of the nodeid.
     */
    protected $nodepath;

    /**
     * An array form of the intanceid.
     */
    protected $instancepath;

    protected $level;

    /**
     * Constructor for the sharedresource_metadata class
     */
    public function __construct($entryid, $element, $value, $namespace = '') {

        if (empty($namespace)) {
            throw new moodle_exception("Undefined namespace");
        }

        if (empty($element)) {
            throw new moodle_exception("Empty element");
        }

        if (!preg_match('/[^:]+:[^:]+/', $element)) {
            throw new moodle_exception("Invalid element structure $element");
        }

        $this->entryid = $entryid;
        $this->element = $element;
        $this->namespace = $namespace;
        $this->value = $value;

        list($this->nodeid, $this->instanceid) = explode(':', $this->element);
        $this->nodepath = explode('_', $this->nodeid);
        $this->level = count($this->nodepath);
        $this->instancepath = explode('_', $this->instanceid);
    }

    /**
     * Knowing the id of a metadata record in database, return the metadata object
     * @param int $mtdid the metadata id.
     */
    public static function instance_by_id($mtdid) {
        global $DB;

        $intancerec = $DB->get_record('sharedresource_metadata', array('id' => $mtdid));
        if (!$instancerec) {
            throw new \coding_exception('No such metadata in database');
        }
        $instance = new metadata($intancerec->entryid, $instancerec->element, $instancerec->value, $instancerec->namespace);
        $instance->isstored = true;
        return $instance;
    }

    /**
     * Knowing some attributes values of the metadata record, get the metadata object based on this record.
     * @param int $entryid the sharedresource associated to this metadata
     * @param int $namespace the metadata namespace
     * @param int $element the element signature as position:occurence index.
     * @param bool $mustexist if true, will throw an exception if the metadata element is not in base.
     */
    public static function instance($entryid, $element, $namespace, $mustexist = true) {
        global $DB;

        $plugin = sharedresource_get_plugin($namespace);
        $record = null;

        if ($entryid != 0) {
            $params = array('entryid' => $entryid, 'namespace' => $namespace, 'element' => $element);
            $record = $DB->get_record('sharedresource_metadata', $params);
        }

        if ($mustexist && !$record) {
            throw new moodle_exception("Metadata instance $element do not exist in database");
        }

        $isstored = true;
        if (!$record) {
            $record = new StdClass;
            $record->entryid = $entryid;
            $record->namespace = $namespace;
            $record->element = $element;
            $record->value = $plugin->defaultValue($element);
            $isstored = false;
        }

        $instance = new metadata($record->entryid, $record->element, $record->value, $record->namespace);
        $instance->isstored = $isstored;
        return $instance;
    }

    /**
     * Get all instances that are matching the node (or subtree)
     * @param int $entryid a sharedresource entry id
     * @param string $namespace namespace name
     * @param string $nodeid a metadata node id (m_n_o)
     * @param string $value if value is given search all nodes with such value only
     * @param boolean $subtree if true, will get all instances in the whole node subtree.
     */
    public static function instances_by_node($entryid, $namespace, $nodeid, $value = null, $subtree = false) {
        global $DB;

        if ($entryid) {
            $selectarr[] = " entryid = ? ";
            $params[] = $entryid;
        }

        if ($namespace) {
            $selectarr[] = " namespace = ? ";
            $params[] = $namespace;
        }

        if ($value) {
            $selectarr[] = " value = ? ";
            $params[] = $value;
        }

        if ($subtree) {
            $selectarr[] = " (element LIKE ? OR element LIKE ?) ";
            $params[] = $nodeid.':%';
            $params[] = $nodeid.'_%';
        } else {
            $selectarr[] = " element LIKE ? ";
            $params[] = $nodeid.':%';
        }

        $select = implode(' AND ', $selectarr);

        $records = $DB->get_records_select('sharedresource_metadata', $select, $params);

        $recordsarr = Array();
        if ($records) {
            foreach ($records as $record) {
                $instance = new metadata($record->entryid, $record->element, $record->value, $record->namespace);
                $instance->isstored = true;
                $recordsarr[] = $instance;
            }
        }

        return $recordsarr;
    }

    /**
     * Get all instances that are matching the node instance (or subtree of this instance)
     * @param int $entryid a sharedresource entry id
     * @param string $namespace namespace name
     * @param string $elementkey a metadata element id (m_n_o:x_y_z)
     * @param string $value if value is given search all nodes with such value only
     * @param boolean $subtree if true, will get all instances in the whole node subtree.
     */
    public static function instances_by_element($entryid, $namespace, $elementkey, $value = null, $subtree = false) {
        global $DB;

        list($nodeid, $instanceid) = explode(':', $elementkey);

        if ($entryid) {
            $selectarr[] = " entryid = ? ";
            $params[] = $entryid;
        }

        if ($namespace) {
            $selectarr[] = " namespace = ? ";
            $params[] = $namespace;
        }

        if ($value) {
            $selectarr[] = " value = ? ";
            $params[] = $value;
        }

        if ($subtree) {
            $selectarr[] = " (element LIKE ? OR element LIKE ?) ";
            $params[] = $nodeid.':'.$instanceid;
            $params[] = $nodeid.'_%:'.$instanceid.'_%';
        } else {
            $selectarr[] = " element LIKE ? ";
            $params[] = $nodeid.':'.$instanceid;
        }

        $select = implode(' AND ', $selectarr);

        $records = $DB->get_records_select('sharedresource_metadata', $select, $params);

        $recordsarr = Array();
        if ($records) {
            foreach ($records as $record) {
                $instance = new metadata($record->entryid, $record->element, $record->value, $record->namespace);
                $instance->isstored = true;
                $recordsarr[] = $instance;
            }
        }

        return $recordsarr;
    }

    /**
     * Stores or updates the metadata element in database.
     */
    public function add_instance() {
        global $DB;

        if ($this->entryid == 0) {
            // Not yet ready to register the metadata.
            return;
        }

        $conditions = array('entryid' => $this->entryid, 'element' => $this->element, 'namespace' => $this->namespace);
        if ($oldentry = $DB->get_record('sharedresource_metadata', $conditions)) {
            $this->id = $oldentry->id;
            return $DB->update_record('sharedresource_metadata', $this);
        }
        $data = new StdClass;
        $data->element = $this->element;
        $data->namespace = $this->namespace;
        $data->value = ''.$this->value; // Unnulify if empty.
        $data->entryid = $this->entryid;
        return $DB->insert_record('sharedresource_metadata', $data);
    }

    public function get_element_key() {
        return $this->element;
    }

    public function get_node_id() {
        return $this->nodeid;
    }

    public function get_value() {
        return $this->value;
    }

    public function set_value($value) {
        $this->value = $value;
    }

    /**
     * gives the numeric index of the node at his own level
     */
    public function get_node_index() {
        return $this->nodepath[count($this->nodepath) - 1];
    }

    public function get_node_path($i = 0) {
        if ($i) {
            return $this->nodepath[$i];
        }
        return $this->nodepath;
    }

    public function get_instance_id() {
        return $this->instanceid;
    }

    public function get_level() {
        return $this->level;
    }

    /**
     * gives the numeric index of the instance at his own level
     */
    public function get_instance_index() {
        return $this->instancepath[count($this->instancepath) - 1];
    }

    public function get_instance_path($i = 0) {
        if ($i) {
            return $this->instancepath[$i];
        }
        return $this->instancepath;
    }

    /**
     * get the immediate parent instance.
     */
    public function get_parent($mustexist = true) {

        if ($this->level == 1) {
            // We are at root.
            return null;
        }

        $namespace = get_config('sharedresource', 'schema');
        $mtdstandard = sharedresource_get_plugin($namespace);

        $parentnodeidarr = array_slice($this->nodepath, 0, $this->level - 1);
        $parentinstanceidarr = array_slice($this->instancepath, 0, $this->level - 1);

        $parentnodeid = implode('_', $parentnodeidarr);
        $parentinstanceid = implode('_', $parentinstanceidarr);

        $parentelementkey = "$parentnodeid:$parentinstanceid";

        return self::instance($this->entryid, $parentelementkey, $namespace, $mustexist);
    }

    /**
     * Get all instances at root.
     * @param int $nodeindex If not null, forces to fetch only in the specified nodeid set.
     * @param string $capability the user profile.
     * @param string $rw 'read' or 'write'.
     * @param bool $recurse if true, recurses in subs and retrieves the childs that have effetive records in the dependancies
     */
    public function get_roots($nodeid, $capability = null, $rw = 'read', $recurse = false) {
        global $DB;

        $namespace = get_config('sharedresource', 'schema');

        if ($recurse) {
            $rootsarr = array();
            $subnodes = $this->get_all_subnodes(true);
            if (!empty($subnodes)) {
                foreach ($subnodes as $node) {
                    $ancestor = $this->get_level_instance(1);
                    if (!array_key_exists($ancestor->element, $rootsarr)) {
                        $rootsarr[$ancestor->element] = $ancestor;
                    }
                }
            }
        } else {

            // Any element id having no '_' is necessarily a N:N root element.
            if ($nodeid) {
                // All instances of one single root index.
                $select = " entryid = ? AND namespace = ? AND element NOT LIKE '%_%' AND element LIKE ? ";
                $params = array($this->entryid, $namespace, $nodeid.':%');
            } else {
                // All instances of all roots.
                $select = " entryid = ? AND namespace = ? AND element NOT LIKE '%_%' ";
                $params = array($this->entryid, $namespace);
            }

            $roots = $DB->get_records_select('sharedresource_metadata', $select, $params, 'element');
            $rootsarr = array();
            foreach ($roots as $r) {
                $relm = self::instance($r->entryid, $r->element, $namespace);
                if (!empty($capability)) {
                    if (!$relm->node_has_capability($capability, $rw)) {
                        continue;
                    }
                }
                $rootsarr[$r->element] = $relm;
            }
        }
        return $rootsarr;
    }

    /**
     * Get all child elements of an element. (node related, not instances).
     * @param int $nodeindex If not null, forces to fetch only the index subranch.
     * @param string $capability the user profile.
     * @param string $rw 'read' or 'write'.
     * @param bool $recurse if true, recurses in subs and retrieves the childs that have effetive records in the dependancies
     */
    public function get_childs($nodeindex = 0, $capability = null, $rw = 'read', $recurse = false) {
        global $DB;

        if ($recurse) {
            $childsarr = array();
            $subnodes = $this->get_all_subnodes(true);
            if (!empty($subnodes)) {
                foreach ($subnodes as $node) {
                    $ancestor = $this->get_level_instance($this->level);
                    if (!array_key_exists($ancestor->element, $childsarr)) {
                        $childsarr[$ancestor->element] = $ancestor;
                    }
                }
            }
        } else {

            $namespace = get_config('sharedresource', 'schema');

            $select = " entryid = ? AND namespace = ? AND element LIKE ? ";
            if (!$nodeindex) {
                    $params = array($this->entryid, $namespace, $this->nodeid.'_%:'.$this->instanceid.'_%');
            } else {
                $params = array($this->entryid, $namespace, $this->nodeid.'_'.$nodeindex.':'.$this->instanceid.'_%');
            }

            $childs = $DB->get_records_select('sharedresource_metadata', $select, $params);
            $childsarr = array();
            if (!empty($childs)) {
                foreach ($childs as $child) {
                    $childelm = self::instance($child->entryid, $child->element, $namespace);
                    if (!empty($capability)) {
                        if (!$childelm->node_has_capability($capability, $rw)) {
                            continue;
                        }
                    }
                    $childsarr[$child->element] = $childelm;
                }
            }
        }
        return $childsarr;
    }

    /**
     * Get all elements that are on same tree level and branch. These are mainly 
     * direct children of our parent having the same subbranch.
     */
    public function get_siblings() {

        if ($this->level == 1) {
            $siblings = $this->get_roots($this->get_node_index());
        } else {
            $siblings = $this->get_parent(false)->get_childs($this->get_node_index());
        }
        unset($siblings[$this->element]); // Remove myself from siblings..

        return $siblings;
    }

    /**
     * Get all effective subnodes of an element signature in the stored metadata
     */
    private function get_all_subnodes($onlysubs = false) {
        global $DB;

        $namespace = get_config('sharedresource', 'schema');

        $select = "
            entryid = ? AND namespace = ? AND element LIKE ?
        ";

        if(!$onlysubs) {
            $params = array($this->entryid, $namespace, $this->nodeid.':%');

            $instancesofme = $DB->get_records_select_menu('sharedresource_metadata', $select, $params, 'element', 'id,element');
        }

        $params = array($this->entryid, $namespace, $this->nodeid.'_%:%');

        $instancesofmychilds = $DB->get_records_select_menu('sharedresource_metadata', $select, $params, 'element', 'id,element');

        // Normalise to arrays.
        if (empty($instancesofme)) {
            $instancesofme = array();
        }

        if (!$instancesofmychilds) {
            $instancesofmychilds = array();
        }

        // Merge all results.
        return $results = array_merge($instancesofme, $instancesofmychilds);
    }

    /**
     * Get the highest sibling element in the current node level.
     * The max occurence may be implicit f.e for categories that only are
     * containers. there will be no direct records for the category in the metadata table, 
     * but some child that holds effective data.
     * the function will track all the node childs of the current node, and will scan for the highest index
     * representing its own level.
     */
    public function get_max_occurrence() {

        $subnodes = $this->get_all_subnodes();
        if (empty($subnodes)) {
            return '';
        }

        $maxoccurrence = 0;

        // Decode.
        foreach ($subnodes as $fooid => $elementid) {
            list($nodeid, $instanceid) = explode(':', $elementid);
            $parts = explode('_', $instanceid);
            if ($parts[$this->level - 1] > $maxoccurrence) {
                $maxoccurrence = $parts[$this->level - 1];
            }
        }

        return $maxoccurrence;
    }

    /**
     * Checks if every subnode has been filled.
     * We can use database records for that as any subtree element on the same instance branch.
     */
    public function childs_have_content($capability = 'system', $rw = 'read') {
        global $DB;

        $namespace = get_config('sharedresource', 'schema');

        $sql = "
            SELECT
                sm.id
            FROM
                {sharedresource_metadata} sm,
                {config_plugins} cf
            WHERE
                cf.plugin = 'sharedmetadata_{$namespace}' AND
                cf.name = ? AND
                sm.entryid = ? AND
                sm.namespace = ? AND
                sm.element LIKE ? AND
                sm.value IS NOT NULL AND sm.value != ''
        ";

        $capabilitykey = "config_{$namespace}_{$capability}_{$rw}_".$this->get_node_id();
        $elementmask = $this->get_node_id().'_%:'.$this->get_instance_id().'_%';

        $params = array($capabilitykey, $this->entryid, $namespace, $elementmask);
        return $DB->record_exists_sql($sql, $params);
    }

    /**
     * Checks for capability to use the element in read or write mode.
     * Capability is a role level concept, not a moodle per user capability.
     * @param text $capability a sharedresource related user profile (system, indexer or author)
     * @param text $rw read or write mode
     */
    function node_has_capability($capability, $rw = 'read') {
        global $DB;

        /*
         * We need to call real used schema to check capability, not the element source schema
         * which may be different.
         */
        $namespace = get_config('sharedresource', 'schema');

        $configkey = "config_{$namespace}_{$capability}_{$rw}_".$this->get_node_id();
        $params = array($configkey);
        return $DB->record_exists_select('config_plugins', "name LIKE ? ", $params);
    }

    /**
     * Get the ancestor metadata instance in the branch at some level. this element may not have
     * database storage.
     */
    function get_level_instance($level) {

        $namespace = get_config('sharedresource', 'schema');

        if ($level > $this->level) {
            throw new coding_exception('Cannot get ancestor lower or at same then the element level');
        }

        $nodepathtmp = array();
        $instancepathtmp = array();

        for ($i = 0; $i < $level; $i++) {
            $nodepathtmp[] = $this->nodepath[$i];
            $instancepathtmp[] = $this->instancepath[$i];
        }

        $ancestornodeid = implode('_', $nodepathtmp);
        $ancestorinstanceid = implode('_', $instancepathtmp);

        return self::instance($this->entryid, "$ancestornodeid:$ancestorinstanceid", $namespace, false);
    }

    /*
     * This function converts the key of the field to the correct form recorded in the database (for instance, 1n2_3n4 becomes 1_3:2_4)
     */
    public static function html_to_storage($htmlname) {

        if (empty($htmlname)) {
            return '';
        }

        $parts = explode('_', $htmlname);
        foreach ($parts as $part) {
            list($node, $occurrence) = explode('n', $part);
            $nodepath[] = $node;
            $instancepath[] = $occurrence;
        }

        $nodeid = implode('_', $nodepath);
        $instanceid = implode('_', $instancepath);

        return $nodeid.':'.$instanceid;
    }

    /**
     * This function converts the key of the field to the correct syntax for making html elements ids (for instance, 1_3:2_4 becomes 1n2_3n4 )
     */
    public static function storage_to_html($elementid) {

        if (empty($elementid)) {
            return '';
        }

        list($nodeid, $instanceid) = explode(':', $elementid);

        $nodearr = explode('_', $nodeid);
        $instancearr = explode('_', $instanceid);

        for ($i = 0; $i < count($nodearr); $i++) {
            $namepath[] = $nodearr[$i].'n'.$instancearr[$i];
        }

        return implode('_', $namepath);
    }

    /**
     * Builds a first instance element id from a nodeid
     * If an instance id is given, the path will be initialized with the instanceid and eventually completed
     * with 0ed index suffic chain to complete until level is reached.
     * @param string $nodeid the standard element id to complete instance for
     * @param string $instanceid an optional instance id as branch reference.
     * @return a full element instance id as m_n_o_p:w_x_y_z
     */
    public static function to_instance($nodeid, $instanceid = null) {

        $instancerefarr = explode('_', $instanceid);

        $parts = explode('_', $nodeid);
        for ($i = 0; $i < count($parts); $i++) {
            $instanceidarr[] = 0 + @$instancerefarr[$i];
        }

        $instanceid = implode('_', $instanceidarr);

        return "$nodeid:$instanceid";
    }

    /*
     * This function converts the key of the field to the correct form recorded in the database (for instance, 1n2_3n4 becomes 1_3:2_4)
     */
    public static function storage_to_array($shrentryid) {
        global $DB;

        $namespace = get_config('sharedresource', 'schema');

        $mtds = $DB->get_records_menu('sharedresource_metadata', array('namespace' => $namespace, 'entryid' => $shrentryid), 'element', 'id,element');

        if (!$mtds) {
            return array();
        }

        $mtdarray = array();

        foreach ($mtds as $mtdid => $elementid) {

            list($nodeid, $instanceid) = explode(':', $elementid);

            $nodearr = explode('_', $nodeid);
            $instancearr = explode('_', $instanceid);

            $arrayrec = &$mtdarray;
            for ($i = 0; $i < count($nodearr); $i++) {
                if (!isset($arrayrec[$nodearr[$i]][$instancearr[$i]])) {
                    $arrayrec[$nodearr[$i]][$instancearr[$i]] = array();
                }
                $arrayrec = &$arrayrec[$nodearr[$i]][$instancearr[$i]];
            }
            $arrayrec = -1; // End of chain.
        }

        return $mtdarray;
    }

    /*
     * Finds all the elementkey replacements to do to normalize a metadata tree with regular instance
     * index numbering from 0.
     * @param objectref $shrentry
     * @param boolean $processroot If true (default), will process also the root
     */
    public static function normalize_storage($shrentryid, $processroot = true) {
        global $DB;

        $storagearr = self::storage_to_array($shrentryid);

        if (empty($storagearr)) {
            return;
        }

        $nodepath = array();
        $frominstancepath = array();
        $toinstancepath = array();

        $replacementsarr = array();

        foreach ($storagearr as $nodeindex => $nodearray) {
            $j = 0;
            foreach ($nodearray as $instanceindex => $instancearr) {
                $nodepath[] = $nodeindex;
                $frominstancepath[] = $instanceindex;
                $toinstancepath[] = $j;
                if ($j != $instanceindex) {
                    // Index has dropped some numbers. We need to pull this branch down.

                    $fromelementid = implode('_', $nodepath).':'.implode('_', $frominstancepath);
                    $toelementid = implode('_', $nodepath).':'.implode('_', $toinstancepath);

                    $replacementsarr[$fromelementid] = $toelementid;
                    $replacing = true;
                } else {
                    $replacing = false;
                }
                if (is_array($instancearr)) {
                    self::normalize_storage_rec($instancearr, $replacementsarr, $nodepath, $frominstancepath, $toinstancepath, $replacing);
                } else {
                    // We reached end of chain.
                    assert($instancearr == -1);
                }
                $j++;
            }
        }

        if (!empty($replacementsarr)) {
            $transaction = $DB->start_delegated_transaction();
            foreach ($replacementsarr as $from => $to) {
                echo "Replacing in db $from => $to ";
                $DB->set_field('sharedresource_metadata', 'element', $to, array('element' => $from, 'entryid' => $shrentryid));
            }
            $transaction->allow_commit();
        }
    }

    /*
     * Processes recursively the subtree to search for index replacements.
     */
    private static function normalize_storage_rec($nodearr, &$replacementsarr, &$nodepath, &$frominstancepath, &$toinstancepath, $replacing) {
        global $DB;
        static $level = 0;

        $level++;

        foreach ($nodearr as $nodeindex => $nodearray) {
            $j = 0;
            foreach ($nodearray as $instanceindex => $instancearr) {
                if ($j != $instanceindex || $replacing) {
                    // Index has dropped some numbers. We need to pull this branch down.
                    $nodepath[] = $nodeindex;
                    $frominstancepath[] = $instanceindex;
                    $toinstancepath[] = $j;

                    $fromelementid = implode('_', $nodepath).':'.implode('_', $frominstancepath);
                    $toelementid = implode('_', $nodepath).':'.implode('_', $toinstancepath);

                    $replacementsarr[$fromelementid] = $toelementid;
                    $replacing = true;
                }
                if (is_array($instancearr)) {
                    self::normalize_storage_rec($instancearr, $replacementsarr, $nodepath, $frominstancepath, $toinstancepath, $replacing);
                } else {
                    // We reached end of chain.
                    assert($instancearr == -1);
                }
                $j++;
            }
        }

        $level--;
    }

    /**
     * checks if a entry is an integer
     */
    public static function is_integer ($x) {
        return (is_numeric($x)? intval($x) == $x : false);
    }

    /*
     * transforms a time in seconds to a time in days, hours, minutes and seconds.
     * Used to transform the duration in seconds.
     */
    public static function build_time($time) {
        $result = array();
        if ($time >= 86400) {
            $result['day'] = floor($time / 86400);
            $reste = $time % 86400;
            $result['hour'] = floor($reste / 3600);
            $reste = $reste % 3600;
            $result['minute'] = floor($reste / 60);
            $result['second'] = $reste % 60;
        } else if ($time < 86400 && $time >= 3600) {
            $result['day'] = '';
            $result['hour'] = floor($time / 3600);
            $reste = $time % 3600;
            $result['minute'] = floor($reste / 60);
            $result['second'] = $reste % 60;
        } else if ($time < 3600 && $time >= 60) {
            $result['day'] = '';
            $result['hour'] = '';
            $result['minute'] = floor($time / 60);
            $result['second'] = $reste % 60;
        } else if ($time < 60) {
            $result['day'] = '';
            $result['hour'] = '';
            $result['minute'] = '';
            $result['second'] = $time;
        }
        return $result;
    }

    /**
     * checks that children of a category have been filled 
     * (in case of a suppression of a classification, because there can be empty categories).
     * maybe not used... 
     */
    public static function check_subcats_filled($listresult, $numoccur, &$shrentry) {

        $isfilled = false;

        foreach ($listresult as $key => $field) {
            $listresult[$key] .= ':'.$numoccur;
        }

        if (!empty($shrentry->metadata_elements)) {
            foreach ($shrentry->metadata_elements as $fookey => $metadata) {
                foreach ($listresult as $fookey => $field) {
                    if (substr_compare($field, $metadata->element, 0, strlen($field)) == 0) {
                        $isfilled = true;
                    }
                }
            }
        }
        return $isfilled;
    }

    public static function use_branch($nodeid, $capability, $rw = 'read') {
        global $DB;

        $config = get_config('sharedresource');
        $namespace = $config->schema;

        $select = "
            name LIKE ? AND
            plugin = ?
        ";

        $params = array("config_{$namespace}_{$capability}_{$rw}_{$nodeid}_%", "sharedmetadata_{$namespace}");
        $hasuse = $DB->record_exists_select('config_plugins', $select, $params);
        if ($hasuse) {
            return true;
        }

        $params = array("config_{$namespace}_{$capability}_{$nodeid}_%", "sharedmetadata_{$namespace}");
        $legacy = $DB->record_exists_select('config_plugins', $select, $params);

        return $legacy;
    }

}
