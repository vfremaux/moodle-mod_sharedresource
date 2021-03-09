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
 * Custom XML parser for XML Metadata (LOMFR)
 *
 * @author  Vincent Micheli
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
namespace mod_sharedresource;

use \StdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/sharedresource/classes/metadata_xml_parser.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_metadata.class.php');

/**
 * Custom XML parser class for XML Metadata
 */
class metadata_xml_parser_scolomfr extends metadata_xml_parser {

    /**
     * Constructor creates and initialises parser resource and calls initialise
     *
     * @return bool True
     */
    public function __construct() {
        parent::__construct();
        return $this->initialise();
    }

    /**
     * Set default element handlers and initialise properties to empty.
     *
     * @return bool True
     */
    public function initialise() {

        $this->parser = xml_parser_create();
        xml_set_object($this->parser, $this);

        xml_set_element_handler($this->parser, 'start_element', 'end_element');
        xml_set_character_data_handler($this->parser, 'default_data');

        $this->current_path = '';
        $this->start_discard = 0;
        $this->ignored_nodes = array('LOM', 'STRING', 'DATETIME', 'VALUE');
        $this->duration_nodes = array('DURATION', 'TYPICALLEARNINGTIME');

        $this->current_meta = null;
        $this->metadata = array();

        $this->title_node = '/GENERAL/TITLE';
        $this->url_node = '/TECHNICAL/LOCATION';
        $this->description_node = '/GENERAL/DESCRIPTION';
        $this->language_node = '/GENERAL/LANGUAGE';

        $this->title = '';
        $this->url = '';
        $this->description = '';
        $this->url_index = 0;
        $this->language = '';

        $this->plugin = 'scolomfr';

        $this->nodes_tree = array(
            '/GENERAL' => array(
                'item' => '1',
                'iteration' => -1
            ),
            '/GENERAL/IDENTIFIER' => array(
                'item' => '1_1',
                'iteration' => -1
            ),
            '/GENERAL/IDENTIFIER/CATALOG' => array(
                'item' => '1_1_1',
                'iteration' => -1
            ),
            '/GENERAL/IDENTIFIER/ENTRY' => array(
                'item' => '1_1_2',
                'iteration' => -1
            ),
            '/GENERAL/TITLE' => array(
                'item' => '1_2',
                'iteration' => -1
            ),
            '/GENERAL/LANGUAGE' => array(
                'item' => '1_3',
                'iteration' => -1
            ),
            '/GENERAL/DESCRIPTION' => array(
                'item' => '1_4',
                'iteration' => -1
            ),
            '/GENERAL/KEYWORD' => array(
                'item' => '1_5',
                'iteration' => -1
            ),
            '/GENERAL/COVERAGE' => array(
                'item' => '1_6',
                'iteration' => -1
            ),
            '/GENERAL/STRUCTURE' => array(
                'item' => '1_7',
                'iteration' => -1
            ),
            '/GENERAL/AGGREGATIONLEVEL' => array(
                'item' => '1_8',
                'iteration' => -1
            ),
            '/GENERAL/DOCUMENTTYPE' => array(
                'item' => '1_9',
                'iteration' => -1
            ),
            '/GENERAL/DOCUMENTGENERALPURPOSE' => array(
                'item' => '1_10',
                'iteration' => -1
            ),
            '/LIFECYCLE' => array(
                'item' => '2',
                'iteration' => -1
            ),
            '/LIFECYCLE/VERSION' => array(
                'item' => '2_1',
                'iteration' => -1
            ),
            '/LIFECYCLE/STATUS' => array(
                'item' => '2_2',
                'iteration' => -1
            ),
            '/LIFECYCLE/CONTRIBUTE' => array(
                'item' => '2_3',
                'iteration' => -1
            ),
            '/LIFECYCLE/CONTRIBUTE/ROLE' => array(
                'item' => '2_3_1',
                'iteration' => -1
            ),
            '/LIFECYCLE/CONTRIBUTE/ENTITY' => array(
                'item' => '2_3_2',
                'iteration' => -1
            ),
            '/LIFECYCLE/CONTRIBUTE/DATE' => array(
                'item' => '2_3_3',
                'iteration' => -1
            ),
            '/METAMETADATA' => array(
                'item' => '3',
                'iteration' => -1
            ),
            '/METAMETADATA/IDENTIFIER' => array(
                'item' => '3_1',
                'iteration' => -1
            ),
            '/METAMETADATA/IDENTIFIER/CATALOG' => array(
                'item' => '3_1_1',
                'iteration' => -1
            ),
            '/METAMETADATA/IDENTIFIER/ENTRY' => array(
                'item' => '3_1_2',
                'iteration' => -1
            ),
            '/METAMETADATA/CONTRIBUTE' => array(
                'item' => '3_2',
                'iteration' => -1
            ),
            '/METAMETADATA/CONTRIBUTE/ROLE' => array(
                'item' => '3_2_1',
                'iteration' => -1
            ),
            '/METAMETADATA/CONTRIBUTE/ENTITY' => array(
                'item' => '3_2_2',
                'iteration' => -1
            ),
            '/METAMETADATA/CONTRIBUTE/DATE' => array(
                'item' => '3_2_3',
                'iteration' => -1
            ),
            '/METAMETADATA/METADATASCHEMA' => array(
                'item' => '3_3',
                'iteration' => -1
            ),
            '/METAMETADATA/LANGUAGE' => array(
                'item' => '3_4',
                'iteration' => -1
            ),
            '/TECHNICAL' => array(
                'item' => '4',
                'iteration' => -1
            ),
            '/TECHNICAL/FORMAT' => array(
                'item' => '4_1',
                'iteration' => -1
            ),
            '/TECHNICAL/SIZE' => array(
                'item' => '4_2',
                'iteration' => -1
            ),
            '/TECHNICAL/LOCATION' => array(
                'item' => '4_3',
                'iteration' => -1
            ),
            '/TECHNICAL/REQUIREMENT' => array(
                'item' => '4_4',
                'iteration' => -1
            ),
            '/TECHNICAL/REQUIREMENT/ORCOMPOSITE' => array(
                'item' => '4_4_1',
                'iteration' => -1
            ),
            '/TECHNICAL/REQUIREMENT/ORCOMPOSITE/TYPE' => array(
                'item' => '4_4_1_1',
                'iteration' => -1
            ),
            '/TECHNICAL/REQUIREMENT/ORCOMPOSITE/NAME' => array(
                'item' => '4_4_1_2',
                'iteration' => -1
            ),
            '/TECHNICAL/REQUIREMENT/ORCOMPOSITE/MINIMUMVERSION' => array(
                'item' => '4_4_1_3',
                'iteration' => -1
            ),
            '/TECHNICAL/REQUIREMENT/ORCOMPOSITE/MAXIMUMVERSION' => array(
                'item' => '4_4_1_4',
                'iteration' => -1
            ),
            '/TECHNICAL/INSTALLATIONREMARKS' => array(
                'item' => '4_5',
                'iteration' => -1
            ),
            '/TECHNICAL/OTHERPLATFORMREQUIREMENTS' => array(
                'item' => '4_6',
                'iteration' => -1
            ),
            '/TECHNICAL/DURATION' => array(
                'item' => '4_7',
                'iteration' => -1
            ),
            '/EDUCATIONAL' => array(
                'item' => '5',
                'iteration' => -1
            ),
            '/EDUCATIONAL/INTERACTIVITYTYPE' => array(
                'item' => '5_1',
                'iteration' => -1
            ),
            '/EDUCATIONAL/LEARNINGRESOURCETYPE' => array(
                'item' => '5_2',
                'iteration' => -1
            ),
            '/EDUCATIONAL/INTERACTIVITYLEVEL' => array(
                'item' => '5_3',
                'iteration' => -1
            ),
            '/EDUCATIONAL/SEMANTICDENSITY' => array(
                'item' => '5_4',
                'iteration' => -1
            ),
            '/EDUCATIONAL/INTENDEDENDUSERROLE' => array(
                'item' => '5_5',
                'iteration' => -1
            ),
            '/EDUCATIONAL/CONTEXT' => array(
                'item' => '5_6',
                'iteration' => -1
            ),
            '/EDUCATIONAL/TYPICALAGERANGE' => array(
                'item' => '5_7',
                'iteration' => -1
            ),
            '/EDUCATIONAL/DIFFICULTY' => array(
                'item' => '5_8',
                'iteration' => -1
            ),
            '/EDUCATIONAL/TYPICALLEARNINGTIME' => array(
                'item' => '5_9',
                'iteration' => -1
            ),
            '/EDUCATIONAL/DESCRIPTION' => array(
                'item' => '5_10',
                'iteration' => -1
            ),
            '/EDUCATIONAL/LANGUAGE' => array(
                'item' => '5_11',
                'iteration' => -1
            ),
            '/EDUCATIONAL/ACTIVITY' => array(
                'item' => '5_12',
                'iteration' => -1
            ),
            '/EDUCATIONAL/CREDIT' => array(
                'item' => '5_13',
                'iteration' => -1
            ),
            '/EDUCATIONAL/LIEUX' => array(
                'item' => '5_14',
                'iteration' => -1
            ),
            '/EDUCATIONAL/MODALITE' => array(
                'item' => '5_15',
                'iteration' => -1
            ),
            '/EDUCATIONAL/OUTIL' => array(
                'item' => '5_16',
                'iteration' => -1
            ),
            '/RIGHTS' => array(
                'item' => '6',
                'iteration' => -1
            ),
            '/RIGHTS/COST' => array(
                'item' => '6_1',
                'iteration' => -1
            ),
            '/RIGHTS/COPYRIGHTANDOTHERRESTRICTIONS' => array(
                'item' => '6_2',
                'iteration' => -1
            ),
            '/RIGHTS/DESCRIPTION' => array(
                'item' => '6_3',
                'iteration' => -1
            ),
            '/RELATION' => array(
                'item' => '7',
                'iteration' => -1
            ),
            '/RELATION/KIND' => array(
                'item' => '7_1',
                'iteration' => -1
            ),
            '/RELATION/RESOURCE' => array(
                'item' => '7_2',
                'iteration' => -1
            ),
            '/RELATION/RESOURCE/IDENTIFIER' => array(
                'item' => '7_2_1',
                'iteration' => -1
            ),
            '/RELATION/RESOURCE/IDENTIFIER/CATALOG' => array(
                'item' => '7_2_1_1',
                'iteration' => -1
            ),
            '/RELATION/RESOURCE/IDENTIFIER/ENTRY' => array(
                'item' => '7_2_1_2',
                'iteration' => -1
            ),
            '/RELATION/RESOURCE/DESCRIPTION' => array(
                'item' => '7_2_2',
                'iteration' => -1
            ),
            '/ANNOTATION' => array(
                'item' => '8',
                'iteration' => -1
            ),
            '/ANNOTATION/ENTITY' => array(
                'item' => '8_1',
                'iteration' => -1
            ),
            '/ANNOTATION/DATE' => array(
                'item' => '8_2',
                'iteration' => -1
            ),
            '/ANNOTATION/DESCRIPTION' => array(
                'item' => '8_3',
                'iteration' => -1
            ),
            '/CLASSIFICATION' => array(
                'item' => '9',
                'iteration' => -1
            ),
            '/CLASSIFICATION/PURPOSE' => array(
                'item' => '9_1',
                'iteration' => -1
            ),
            '/CLASSIFICATION/TAXONPATH' => array(
                'item' => '9_2',
                'iteration' => -1
            ),
            '/CLASSIFICATION/TAXONPATH/SOURCE' => array(
                'item' => '9_2_1',
                'iteration' => -1
            ),
            '/CLASSIFICATION/TAXONPATH/TAXON' => array(
                'item' => '9_2_2',
                'iteration' => -1
            ),
            '/CLASSIFICATION/TAXONPATH/TAXON/ID' => array(
                'item' => '9_2_2_1',
                'iteration' => -1
            ),
            '/CLASSIFICATION/TAXONPATH/TAXON/ENTRY' => array(
                'item' => '9_2_2_2',
                'iteration' => -1
            ),
            '/CLASSIFICATION/DESCRIPTION' => array(
                'item' => '9_3',
                'iteration' => -1
            ),
            '/CLASSIFICATION/KEYWORD' => array(
                'item' => '9_4',
                'iteration' => -1
            )
        );

        return true;
    }

    /**
     * Parse a block of XML text
     *
     * @return bool True on success - false on failure
     */
    public function parse($data) {
        $p = xml_parse($this->parser, $data);
        return (bool)$p;
    }

    /**
     * Destroy the parser and free up any related resource.
     */
    public function free_resource() {
        $free = xml_parser_free($this->parser);
    }

    /**
     * Reset child nodes iteration
     */
    public function reset_childs($path) {
        foreach ($this->nodes_tree as $key => $value) {
            if (strpos($key, $path.'/') === 0) {
                $this->nodes_tree[$key]['iteration'] = -1;
            }
        }
    }

    /**
     * Get Element path (ie 0_0_1)
     */
    public function get_elempath($path) {
        $elempath = '';
        $depth = substr_count($path, '/');
        $tmppath = $path;
        $elempathtab = array();
        for ($i = 0; $i < $depth; $i++) {
            $elempathtab[$depth - ($i + 1)] = $this->nodes_tree[$tmppath]['iteration'];
            $tmppath = substr($tmppath, 0, strrpos($tmppath, '/'));
        }
        ksort($elempathtab);
        foreach ($elempathtab as $value) {
            $elempath .= $value.'_';
        }
        $elempath = substr($elempath, 0, strlen($elempath) - 1);

        return $elempath;
    }

    /**
     * Set the character-data handler to the right function for each element
     *
     * For each tag (element) name, this function switches the character-data
     * handler to the function that handles that element. Note that character
     * data is referred to the handler in blocks of 1024 bytes.
     *
     * @param   mixed   $parser The XML parser
     * @param   string  $name   The name of the tag, e.g. method_call
     * @param   array   $attrs  The tag's attributes (if any exist).
     * @return  bool            True
     */
    public function start_element($parser, $name, $attrs) {
        if (strpos($name, ':') !== false) {
            $name = substr($name, strpos($name, ':') + 1);
        }

        if (in_array($name, $this->ignored_nodes)) {
            return true;
        }

        // We need to distinguish the node 4_7 of the duration tag.
        if ($name == 'DURATION' &&
                in_array(substr($this->current_path, strrpos($this->current_path, '/') + 1), $this->duration_nodes)) {
            return true;
        }

        // We need to discard content of source node.
        if ($name == 'SOURCE' &&
                !(substr($this->current_path, strrpos($this->current_path, '/') + 1) == 'TAXONPATH')) {
            $this->start_discard = 1;
            return true;
        }

        $this->current_path .= '/'.$name;
        $this->nodes_tree[$this->current_path]['iteration']++;
        $this->reset_childs($this->current_path);

        $elemid = $this->nodes_tree[$this->current_path]['item'];
        $elempath = $this->get_elempath($this->current_path);
        $this->current_meta = new metadata(1, $elemid.':'.$elempath, '', $this->plugin);
        $this->metadata[] = $this->current_meta;

        return true;
    }

    /**
     * Text handler
     *
     * @param mixed $parser The XML parser
     * @param string $data The content of the current tag (1024 byte chunk)
     * @return bool true
     */
    public function default_data($parser, $data) {
        if (trim($data) == '' || $this->start_discard) {
            return true;
        }
        if ($this->current_path == $this->title_node) {
            if (empty($this->title)) {
                $this->title = addslashes($data);
            } else {
                $this->title .= addslashes($data);
            }
        }
        if ($this->current_path == $this->url_node) {
            if (empty($this->url)) {
                $this->url = addslashes($data);
            } else {
                $this->url .= addslashes($data);
            }
            $this->url_index = count($this->metadata) - 1;
        }
        if ($this->current_path == $this->description_node) {
            if (empty($this->description)) {
                $this->description = addslashes($data);
            } else {
                $this->description .= addslashes($data);
            }
        }
        if ($this->current_path == $this->language_node) {
            if (empty($this->language)) {
                $this->language = addslashes($data);
            } else {
                $this->language .= addslashes($data);
            }
        }

        if (empty($this->current_meta->value)) {
            $this->current_meta->value = addslashes($data);
        } else {
            $this->current_meta->value .= addslashes($data);
        }

        return true;
    }

    /**
     * Switch the character-data handler to ignore the next chunk of data
     *
     * @param mixed $parser The XML parser
     * @param string $name The name of the tag, e.g. method_call
     * @return bool true
     */
    public function end_element($parser, $name) {
        if (strpos($name, ':') !== false) {
            $name = substr($name, strpos($name, ':') + 1);
        }

        if (in_array($name, $this->ignored_nodes)) {
            return true;
        }

        // We need to distinguish the node 4_7 of the duration tag.
        if ($name == 'DURATION' &&
                strpos($this->current_path, '/DURATION') === false) {
            return true;
        }

        // We need to discard content of source node.
        if ($name == 'SOURCE' &&
                $this->start_discard == 1) {
            $this->start_discard = 0;
            return true;
        }

        $this->current_path = substr($this->current_path, 0, (strrpos($this->current_path, '/')));

        $this->current_meta = null;

        return true;
    }

    // Other utility functions to deal and change metadata.
    public function add_identifier(&$metadata, $catalog, $identifier, $entryid) {

        // Add identifier record.
        $identifiernodeid = $this->nodes_tree['/GENERAL/IDENTIFIER']['iteration'];
        $identifiernodeid++;
        $lastidentifierinstanceid = $this->nodes_tree['/GENERAL/IDENTIFIER']['item'].':0_'.$identifiernodeid;
        $metadatarec = new StdClass();
        $metadatarec->element = $lastidentifierinstanceid;
        $metadatarec->namespace = 'lomfr';
        $metadatarec->value = '';
        $metadatarec->entryid = $entryid;
        $metadata[] = $metadatarec;

        // Add catalog record.
        $cataloginstanceid = $this->nodes_tree['/GENERAL/IDENTIFIER/CATALOG']['item'].':0_'.$identifiernodeid.'_0';
        $metadatarec = new StdClass();
        $metadatarec->element = $cataloginstanceid;
        $metadatarec->namespace = 'lomfr';
        $metadatarec->value = $catalog;
        $metadatarec->entryid = $entryid;
        $metadata[] = $metadatarec;

        // Add entry value record.
        $entryinstanceid = $this->nodes_tree['/GENERAL/IDENTIFIER/ENTRY']['item'].':0_'.$identifiernodeid.'_0';
        $metadatarec = new StdClass();
        $metadatarec->element = $cataloginstanceid;
        $metadatarec->namespace = 'lomfr';
        $metadatarec->value = $identifier;
        $metadatarec->entryid = $entryid;
        $metadata[] = $metadatarec;
    }
}
