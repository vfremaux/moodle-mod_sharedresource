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
 * Defines a sharedresource metadata element, i.e., the instanciation of a single metadata regarding
 * the activated metadata standard.
 *
 * @package     mod_sharedresource
 * @author      Piers Harding  <piers@catalyst.net.nz>, Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/*
 * Because classes preloading (SHAREDRESOURCE_INTERNAL) pertubrates MOODLE_INTERNAL detection.
 * phpcs:disable moodle.Files.MoodleInternal.MoodleInternalGlobalState
 */
namespace mod_sharedresource;

use StdClass;
use moodle_exception;
use coding_exception;

if (!defined('SHAREDRESOURCE_INTERNAL')) {
    defined('MOODLE_INTERNAL') || die();
}

/**
 * \mod_sharedresource\metadata defines a sharedresource_metadata element
 *
 * This class provides all the functionality for a sharedresource_metadata
 * record of a metadata value.
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
class metadata {

    /** @var the element id as a m_n_o:x_y_z node identifier. */
    public $element;

    /** @var the currently used namespace (string). Proxies from sharedresource configuration. */
    public $namespace;

    /** @var The stored scalar value. */
    protected $value;

    /** @var Marker if the element has a database stored record. */
    public $isstored;

    /** @var The sharedresource entry id this instance serves a value for. */
    public $entryid;

    /** @var The element identity as m_n_o_p nodeid. */
    protected $nodeid;

    /** @var The tree occurrence identity as w_x_y_z */
    protected $instanceid;

    /** @var An array form of the nodeid. */
    protected $nodepath;

    /** @var An array form of the intanceid. */
    protected $instancepath;

    /** @var The tree level. */
    protected $level;

    /**
     * Constructor for the sharedresource_metadata class.
     * @param int $entryid the sharedresource entry this metadata value belongs to
     * @param string $element the elementid as nodeid:instanceid
     * @param string $value the metadata node's value
     * @param string $namespace the related namespace
     */
    public function __construct($entryid, $element, $value, $namespace = '') {

        if (empty($namespace)) {
            throw new metadata_exception("Undefined namespace");
        }

        if (empty($element)) {
            throw new metadata_exception("Empty element");
        }

        if (!preg_match('/[^:]+:[^:]+/', $element)) {
            throw new metadata_exception("Invalid element structure \"$element\"");
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

        $intancerec = $DB->get_record('sharedresource_metadata', ['id' => $mtdid], '*', MUST_EXIST);
        $instance = new metadata($intancerec->entryid, $instancerec->element, $instancerec->value, $instancerec->namespace);
        $instance->isstored = true;
        return $instance;
    }

    /**
     * Knowing some attributes values of the metadata record, get the metadata object based on this record.
     * @param int $entryid the sharedresource associated to this metadata
     * @param int $namespace the metadata namespace
     * @param int $element the element signature as m_n_o:x_y_z index.
     * @param bool $mustexist if true, will throw an exception if the metadata element is not in base.
     */
    public static function instance($entryid, $element, $namespace, $mustexist = true) {
        global $DB;

        $plugin = sharedresource_get_plugin($namespace);
        $record = null;

        if ($entryid != 0) {
            $params = ['entryid' => $entryid, 'namespace' => $namespace, 'element' => $element];
            $record = $DB->get_record('sharedresource_metadata', $params);
        }

        if ($mustexist && is_null($record)) {
            throw new metadata_exception("Metadata instance $element does not exist in database");
        }

        $isstored = true;
        if (!$record) {
            $record = new StdClass();
            $record->entryid = $entryid;
            $record->namespace = $namespace;
            $record->element = $element;
            $record->value = $plugin->default_value($element);
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

        $recordsarr = [];
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

        $recordsarr = [];
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

        $params = [
            'entryid' => $this->entryid,
            'element' => $this->element,
            'namespace' => $this->namespace,
        ];
        $oldentry = $DB->get_record('sharedresource_metadata', $params);
        if ($oldentry) {
            $value = $this->value; // Beware of the __get magic trap.
            if (empty($value)) {
                $DB->delete_records('sharedresource_metadata', ['id' => $oldentry->id]);
                return true;
            }

            $this->id = $oldentry->id;
            return $DB->update_record('sharedresource_metadata', $this);
        }
        $data = new StdClass();
        $data->element = $this->element;
        $data->namespace = $this->namespace;
        $value = $data->value = ''.$this->value; // Unnulify if empty.
        if (!empty($value)) {
            $data->entryid = $this->entryid;
            return $DB->insert_record('sharedresource_metadata', $data);
        } else {
            // Skip inserting but do NOT block the update process. So answer true.
            return true;
        }
    }

    /**
     * Get the lowest possible instance on this tree node.
     * May even not exist in the stored metadata. this is obtained by setting
     * the last instance path index to 0.
     *
     * return a new metadata object with changed ids.
     */
    public function base_instance_sibling() {
        $sibling = clone($this);
        array_pop($sibling->instancepath);
        array_push($sibling->instancepath, 0);
        $sibling->instanceid = implode('_', $sibling->instancepath);
        return $sibling;
    }

    /**
     * Get the element def key.
     */
    public function get_element_key() {
        return $this->element;
    }

    /**
     * Get the instance nodeid
     */
    public function get_node_id() {
        return $this->nodeid;
    }

    /**
     * Get the metadata branch id (that is the top node index)
     */
    public function get_branch_id() {
        return $this->nodepath[0];
    }

    /**
     * Get the actual metadata value for this element in context.
     */
    public function get_value() {
        return $this->value;
    }

    /**
     * Set the value of this element in context.
     * @param string $value
     */
    public function set_value($value) {
        $this->value = $value;
    }

    /**
     * Used when cloning sharedresource entry object.
     * @param int $entryid the entry id in DB.
     */
    public function set_entryid($entryid) {
        $this->entryid = $entryid;
    }

    /**
     * gives the numeric index of the node at his own level
     */
    public function get_node_index() {
        return $this->nodepath[count($this->nodepath) - 1];
    }

    /**
     * Get the subpart of the node path from level $i
     * @param int $level
     */
    public function get_node_path($level = 0) {
        if ($level) {
            return $this->nodepath[$level];
        }
        return $this->nodepath;
    }

    /**
     * Get the DB instance id of this metadata element
     */
    public function get_instance_id() {
        return $this->instanceid;
    }

    /**
     * Get the metadata element's level in metadata tree.
     */
    public function get_level() {
        return $this->level;
    }

    /**
     * Gives the numeric index of the instance at his own level
     */
    public function get_instance_index() {
        return $this->instancepath[count($this->instancepath) - 1];
    }

    /**
     * Get the instance subpath under some level
     * @param int $level
     */
    public function get_instance_path($level = null) {
        if (!is_null($level)) {
            return $this->instancepath[$level];
        }
        return $this->instancepath;
    }

    /**
     * Get the immediate parent instance.
     * @param bool $mustexist if true throws a moodle_exception if not exists
     * @return a metadata instance.
     */
    public function get_parent($mustexist = true) {

        if ($this->level == 1) {
            // We are at root.
            return null;
        }

        $namespace = get_config('sharedresource', 'schema');

        $parentnodeid = implode('_', $this->nodepath);
        $parentinstanceid = implode('_', $this->instancepath);
        $parentnodeid = preg_replace('/_[^_]+$/', '', $parentnodeid);
        $parentinstanceid = preg_replace('/_[^_]+$/', '', $parentinstanceid);

        $parentelementkey = "$parentnodeid:$parentinstanceid";

        $parentnode = self::instance($this->entryid, $parentelementkey, $namespace, $mustexist);
        return $parentnode;
    }

    /**
     * Get all instances at root.
     * @param int $nodeindex If not null, forces to fetch only in the specified nodeid set.
     * @param string $capability the user profile.
     * @param string $rw 'read' or 'write'.
     * @param bool $recurse if true, recurses in subs and retrieves the childs that have effetive records in the dependancies
     * @return an array of metadata instances indexed by elementid.
     */
    public function get_roots($nodeid, $capability = null, $rw = 'read', $recurse = false) {
        global $DB;

        $namespace = get_config('sharedresource', 'schema');

        if ($recurse) {
            $rootsarr = [];
            $subnodes = $this->get_all_subnodes(true);
            if (!empty($subnodes)) {
                foreach ($subnodes as $node) {
                    // Should record $node ? Is this code used ?
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
                $select = "
                    entryid = ? AND
                    namespace = ? AND
                    element NOT LIKE '%_%' AND
                    element LIKE ?
                ";
                $params = [$this->entryid, $namespace, $nodeid.':%'];

                // Guess all nodes have at least one N_1 element.
                $subselect = "
                    entryid = ? AND
                    namespace = ? AND
                    element LIKE ?
                ";
                $subparams = [$this->entryid, $namespace, $nodeid.'_1:%'];
            } else {
                // All instances of all roots.
                $select = "
                    entryid = ? AND
                    namespace = ? AND
                    element NOT LIKE '%_%'
                ";
                $params = [$this->entryid, $namespace];
            }

            // First search on really stored elements. (generally none).
            $roots = $DB->get_records_select('sharedresource_metadata', $select, $params, 'element');

            $rootsarr = [];
            foreach ($roots as $r) {
                $relm = self::instance($r->entryid, $r->element, $namespace);
                if (!empty($capability)) {
                    if (!$relm->node_has_capability($capability, $rw)) {
                        continue;
                    }
                }
                $rootsarr[$r->element] = $relm;
            }

            // Second search on some stored subelements.
            if ($nodeid) {
                $rootsubs = $DB->get_records_select('sharedresource_metadata', $subselect, $subparams, 'element');
                if ($rootsubs) {
                    foreach ($rootsubs as $subnode) {
                        list($subnodeid, $instanceid) = explode(':', $subnode->element);
                        $instancearr = explode('_', $instanceid);
                        $rootelementid = $instancearr[0];
                        $rootelement = $nodeid.':'.$rootelementid;
                        $relm = self::instance(0, $rootelement, $namespace, false);
                        if (!empty($capability)) {
                            if (!$relm->node_has_capability($capability, $rw)) {
                                continue;
                            }
                        }
                        $rootsarr[$rootelement] = $relm;
                    }
                }
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
     * @return an array of metadata instances
     */
    public function get_childs($nodeindex = 0, $capability = null, $rw = 'read', $recurse = false) {
        global $DB;

        $childsarr = [];

        if ($recurse) {
            $subnodes = $this->get_all_subnodes(true);
            if (!empty($subnodes)) {
                foreach ($subnodes as $node) {
                    // Should record $node ? Is this code used ?
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
                $params = [$this->entryid, $namespace, $this->nodeid.'_%:'.$this->instanceid.'_%'];
            } else {
                $params = [$this->entryid, $namespace, $this->nodeid.'_'.$nodeindex.':'.$this->instanceid.'_%'];
            }

            $childs = $DB->get_records_select('sharedresource_metadata', $select, $params);
            if (!empty($childs)) {
                foreach ($childs as $child) {
                    $childelm = self::instance($child->entryid, $child->element, $namespace, false);
                    if (!empty($capability)) {
                        if (!$childelm->node_has_capability($capability, $rw)) {
                            continue;
                        }
                    }
                    $childsarr[$child->element] = $childelm;
                }
            }

            // Getting hidden branches for virtual nodes.
            if ($this->level == 1) {
                $select = " entryid = ? AND namespace = ? AND element LIKE ? ";
                if (!$nodeindex) {
                    $params = [$this->entryid, $namespace, $this->nodeid.'_%:'.$this->instanceid.'_%'];
                } else {
                    $params = [$this->entryid, $namespace, $this->nodeid.'_'.$nodeindex.'_%:'.$this->instanceid.'_%'];
                }

                $subbranches = $DB->get_records_select_menu('sharedresource_metadata', $select, $params, 'element', 'id,element');

                if (!empty($subbranches)) {
                    foreach ($subbranches as $eid => $element) {
                        $childnodes = [];
                        $childinstances = [];
                        $metadata = new metadata($this->entryid, $element, 0, $namespace);
                        for ($i = 0; $i <= $this->level; $i++) {
                            $childnodes[] = $metadata->nodepath[$i];
                            $childinstances[] = $metadata->instancepath[$i];
                        }
                        $childelementid = implode('_', $childnodes).':'.implode('_', $childinstances);
                        if (!array_key_exists($childelementid, $childsarr)) {
                            $otherchild = new metadata($this->entryid, $childelementid, 0, $namespace);
                            $childsarr[$childelementid] = $otherchild;
                        }
                    }
                }
            }
        }

        return $childsarr;
    }

    /**
     * Get all elements that are on same tree level and branch. These are mainly
     * direct children of our parent having the same subbranch.
     * @param int $level
     * @return an array of metadata elements that are my siblings.
     */
    public function get_siblings($level = 0) {
        global $DB, $CFG;

        $debug = optional_param('debug', false, PARAM_BOOL);
        $namespace = get_config('sharedresource', 'schema');
        $siblings = [];

        if ($level == 0) {
            if ($this->level == 1) {
                $siblings = $this->get_roots($this->get_node_index());
            } else {
                $parentnode = $this->get_parent(false);
                mod_sharedresource_debug_trace("My parent for ".$this->element.' at '.$this->level, SHR_TRACE_DEBUG);
                mod_sharedresource_debug_trace($parentnode, SHR_TRACE_DEBUG);
                $msg = "Getting parent other childs than me My node index ".$this->get_node_index();
                mod_sharedresource_debug_trace($msg, SHR_TRACE_DEBUG);
                if ($debug && $CFG->debug >= DEBUG_NORMAL) {
                    echo("My parent for ".$this->element.' at '.$this->level."<br/>");
                    echo("Getting parent other childs than me My node index ".$this->get_node_index()."<br/>");
                }
                $siblings = $parentnode->get_childs($this->get_node_index());
            }
        }

        if ($level == 1) {
            $mask = [];
            $parts = explode('_', $this->instanceid);
            array_pop($parts);
            array_pop($parts);
            $mask[] = '%';
            do {
                $node = array_pop($parts);
                $mask[] = $node;
            } while (count($parts));
            $mask = array_reverse($mask);
            $sqlmask = $this->nodeid.':'.implode('_', $mask);
            $select = "
                entryid = ? AND
                element LIKE ? AND
                namespace = ?
            ";

            $params = [$this->entryid, $sqlmask, $namespace];
            $extendedsiblings = $DB->get_records_select('sharedresource_metadata', $select, $params);
            if ($extendedsiblings) {
                foreach ($extendedsiblings as $extsib) {
                    if ($extsib->element != $this->element) {
                        // Do not add myself.
                        if (!array_key_exists($extsib->element, $siblings)) {
                            $siblings[$extsib->element] = self::instance($this->entryid, $extsib->element, $namespace);
                        }
                    }
                }
            }
        }

        if (array_key_exists($this->element, $siblings)) {
            unset($siblings[$this->element]); // Remove myself from siblings..
        }
        mod_sharedresource_debug_trace($siblings, SHR_TRACE_DEBUG);
        return $siblings;
    }

    /**
     * Get all effective subnodes of an element signature in the stored metadata
     * @param bool $onlysubs
     */
    private function get_all_subnodes($onlysubs = false) {
        global $DB;

        $namespace = get_config('sharedresource', 'schema');

        $select = "
            entryid = ? AND
            namespace = ? AND
            element LIKE ?
        ";

        if (!$onlysubs) {
            $params = [$this->entryid, $namespace, $this->nodeid.':%'];

            $instancesofme = $DB->get_records_select_menu('sharedresource_metadata', $select, $params, 'element', 'id,element');
        }

        $params = [$this->entryid, $namespace, $this->nodeid.'_%:%'];

        $instancesofmychilds = $DB->get_records_select_menu('sharedresource_metadata', $select, $params, 'element', 'id,element');

        // Normalise to arrays.
        if (empty($instancesofme)) {
            $instancesofme = [];
        }

        if (!$instancesofmychilds) {
            $instancesofmychilds = [];
        }

        // Merge all results.
        return array_merge($instancesofme, $instancesofmychilds);
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
        global $DB;

        $select = "
            entryid = ? AND
            element LIKE ? AND
            namespace = ?
        ";

        $mynode = $this->nodeid;
        $parent = $this->get_parent(false);
        if ($parent) {
            $instanceid = $parent->instanceid.'_%';
        } else {
            $instanceid = '%';
        }

        $params = [$this->entryid, $mynode.':'.$instanceid, $this->namespace];
        $subs = $DB->get_records_select('sharedresource_metadata', $select, $params, 'element', 'id,element');
        $params = [$this->entryid, $mynode.'_%:'.$instanceid, $this->namespace];
        $subsubs = $DB->get_records_select('sharedresource_metadata', $select, $params, 'element', 'id,element');

        if ($subs) {
            $allnodes = $subs;
        } else {
            $allnodes = [];
        }
        if ($subsubs) {
            $allnodes += $subsubs;
        }

        $lastoccur = '';
        if (!empty($allnodes)) {
            $lastoccur = 0;
            foreach ($allnodes as $node) {
                $parts = explode(':', $node->element);
                $instanceid = $parts[1];

                $instancearr = explode('_', $instanceid);
                if ($instancearr[$this->level - 1] > $lastoccur) {
                    $lastoccur = $instancearr[$this->level - 1];
                }
            }
        }

        return $lastoccur;
    }

    /**
     * Get the highest sibling element in the current node level.
     * The max occurence may be implicit f.e for categories that only are
     * containers. there will be no direct records for the category in the metadata table,
     * but some child that holds effective data.
     * the function will track all the node childs of the current node, and will scan for the highest index
     * representing its own level.
     */
    public function get_max_instance_index() {
        static $subnodes;

        if (!isset($subnodes)) {
            $subnodes = $this->get_all_subnodes();
        }
        if (empty($subnodes)) {
            return '';
        }

        $maxoccurrence = 0;

        // Decode.
        foreach (array_values($subnodes) as $elementid) {

            $parts = explode(':', $elementid);
            $instanceid = $parts[1];

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
     * @param string $capability
     * @param string $rw
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

        $params = [$capabilitykey, $this->entryid, $namespace, $elementmask];
        return $DB->record_exists_sql($sql, $params);
    }

    /**
     * Checks for capability to use the element in read or write mode.
     * Capability is a role level concept, not a moodle per user capability.
     * @param string $capability a sharedresource related user profile (system, indexer or author)
     * @param string $rw read or write mode
     */
    public function node_has_capability($capability, $rw = 'read') {
        global $DB;

        /*
         * We need to call real used schema to check capability, not the element source schema
         * which may be different.
         */
        $namespace = get_config('sharedresource', 'schema');

        $configkey = "config_{$namespace}_{$capability}_{$rw}_".$this->get_node_id();
        $params = [$configkey];
        return $DB->record_exists_select('config_plugins', "name LIKE ? ", $params);
    }

    /**
     * Checks for mandatory status of the node.
     */
    public function node_is_mandatory() {
        global $DB;

        /*
         * We need to call real used schema to check capability, not the element source schema
         * which may be different.
         */
        $namespace = get_config('sharedresource', 'schema');

        $configkey = "config_{$namespace}_mandatory_".$this->get_node_id();
        $configstate = get_config('sharedresource', $configkey);

        // Also check in tree scan in DB.
        $params = [$configkey];
        $dbstate = $DB->record_exists_select('config_plugins', "name LIKE ? ", $params);

        return $configstate || $dbstate;
    }

    /**
     * Get the ancestor metadata instance in the branch at some level. this element may not have
     * database storage.
     * @param int $level
     */
    public function get_level_instance($level) {

        $namespace = get_config('sharedresource', 'schema');

        if ($level > $this->level) {
            throw new coding_exception('Cannot get ancestor lower or at same then the element level');
        }

        $nodepathtmp = [];
        $instancepathtmp = [];

        for ($i = 0; $i < $level; $i++) {
            $nodepathtmp[] = $this->nodepath[$i];
            $instancepathtmp[] = $this->instancepath[$i];
        }

        $ancestornodeid = implode('_', $nodepathtmp);
        $ancestorinstanceid = implode('_', $instancepathtmp);

        return self::instance($this->entryid, "$ancestornodeid:$ancestorinstanceid", $namespace, false);
    }

    /**
     * This function converts the key of the field to the correct form recorded in the database
     * (for instance, 1n2_3n4 becomes 1_3:2_4)
     * @param string $htmlname
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
     * This function converts the key of the field to the correct syntax for making html elements ids
     * (for instance, 1_3:2_4 becomes 1n2_3n4 )
     * @param string $elementid
     * @return a string featured for HTML output.
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
    public static function to_instance($nodeid, $instanceid = '') {

        $instancerefarr = explode('_', $instanceid);

        $parts = explode('_', $nodeid);

        for ($i = 0; $i < count($parts); $i++) {
            if (!isset($instancerefarr[$i]) || !is_numeric($instancerefarr[$i])) {
                $instanceidarr[] = 0;
            } else {
                $instanceidarr[] = 0 + @$instancerefarr[$i];
            }
        }

        $instanceid = implode('_', $instanceidarr);

        return "$nodeid:$instanceid";
    }

    /**
     * This function converts the key of the field to the correct form recorded in the database
     * (for instance, 1n2_3n4 becomes 1_3:2_4)
     * @param int $shrentrytid
     */
    public static function storage_to_array($shrentryid) {
        global $DB;

        $namespace = get_config('sharedresource', 'schema');

        $params = ['namespace' => $namespace, 'entryid' => $shrentryid];
        $mtds = $DB->get_records_menu('sharedresource_metadata', $params, 'element', 'id,element');

        if (!$mtds) {
            return [];
        }

        $mtdarray = [];

        foreach (array_values($mtds) as $elementid) {

            list($nodeid, $instanceid) = explode(':', $elementid);

            $nodearr = explode('_', $nodeid);
            $instancearr = explode('_', $instanceid);

            $arrayrec = &$mtdarray;
            for ($i = 0; $i < count($nodearr); $i++) {
                if (!isset($arrayrec[$nodearr[$i]][$instancearr[$i]])) {
                    $arrayrec[$nodearr[$i]][$instancearr[$i]] = [];
                }
                $arrayrec = &$arrayrec[$nodearr[$i]][$instancearr[$i]];
            }
            $arrayrec = -1; // End of chain.
        }

        return $mtdarray;
    }

    /**
     * Finds all the elementkey replacements to do to normalize a metadata tree with regular instance
     * index numbering from 0.
     * @param string $shrentryid
     */
    public static function normalize_storage($shrentryid) {
        global $DB;

        // TEMPORARY till we agree with the normalize algorithm.
        return;

        $storagearr = self::storage_to_array($shrentryid);

        if (empty($storagearr)) {
            return;
        }

        $nodepath = [];
        $frominstancepath = [];
        $toinstancepath = [];

        $replacementsarr = [];

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
                    self::normalize_storage_rec($instancearr, $replacementsarr, $nodepath, $frominstancepath,
                            $toinstancepath, $replacing);
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
                $DB->set_field('sharedresource_metadata', 'element', $to, ['element' => $from, 'entryid' => $shrentryid]);
            }
            $transaction->allow_commit();
        }
    }

    /**
     * Processes recursively the subtree to search for index replacements.
     * @param array $nodearr
     * @param array $replacementsarr
     * @param array $nodepath
     * @param array $frominstancepath
     * @param array $toinstancepath
     * @param bool $replacing
     */
    private static function normalize_storage_rec($nodearr, & $replacementsarr, & $nodepath, & $frominstancepath,
            & $toinstancepath, $replacing) {
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
                    self::normalize_storage_rec($instancearr, $replacementsarr, $nodepath, $frominstancepath,
                            $toinstancepath, $replacing);
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
     * @param mixed $x
     */
    public static function is_integer($x) {
        return (is_numeric($x) ? intval($x) == $x : false);
    }

    /**
     * transforms a time in seconds to a time in days, hours, minutes and seconds.
     * Used to transform the duration in seconds.
     * @param string $time
     */
    public static function build_time($time) {
        $result = [];
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
     * @param array $listresult
     * @param int $numoccur
     * @param object $shrentry
     */
    public static function check_subcats_filled($listresult, $numoccur, $shrentry) {

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

    /**
     * Checks if the metadata branch is used
     * @param string $nodeid
     * @param string $capability
     * @param string $rw
     * @return bool
     */
    public static function use_branch($nodeid, $capability, $rw = 'read') {
        global $DB;

        $config = get_config('sharedresource');
        $namespace = $config->schema;

        $select = "
            name LIKE ? AND
            plugin = ?
        ";

        $params = ["config_{$namespace}_{$capability}_{$rw}_{$nodeid}_%", "sharedmetadata_{$namespace}"];
        $hasuse = $DB->record_exists_select('config_plugins', $select, $params);
        if ($hasuse) {
            return true;
        }

        $params = ["config_{$namespace}_{$capability}_{$nodeid}_%", "sharedmetadata_{$namespace}"];
        $legacy = $DB->record_exists_select('config_plugins', $select, $params);

        return $legacy;
    }

    /**
     * Checks for mandatory status of the node.
     * @param int $branchid
     */
    public static function has_mandatories($branchid) {
        global $DB;

        /*
         * We need to call real used schema to check capability, not the element source schema
         * which may be different.
         */
        $namespace = get_config('sharedresource', 'schema');

        $configkey = "config_{$namespace}_mandatory_".$branchid.'%';
        $params = [$configkey];
        $dbstate = $DB->record_exists_select('config_plugins', "name LIKE ? ", $params);
        return $dbstate;
    }

    /**
     * Decodes to internal storage a ful branch info comming from an add button
     * entry is mnw_nnx_ony:<value>;mnw_nnx_ony:<value> list form.
     * @param string $branch serialized branch info
     * @return array of elementids to value mapping.
     */
    public static function decode_branch_info($branch) {
        if (empty($branch)) {
            return false;
        }
        $branchelms = explode(';', $branch);

        $brancharr = [];
        foreach ($branchelms as $elmpair) {
            list($elementid, $value) = explode(':', $elmpair);
            $brancharr[self::html_to_storage($elementid)] = $value;
        }

        return $brancharr;
    }

    /**
     * A utility function : finds some tree prefixes into an array.
     * @param string $what
     * @param array $ids
     */
    public static function find($what, $ids) {

        $result = [];

        if (!empty($ids)) {
            foreach ($ids as $id) {
                if (strpos($id, $what) === 0) {
                    $result[] = $id;
                }
            }
        }

        return $result;
    }
}
