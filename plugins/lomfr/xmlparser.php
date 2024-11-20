<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Custom XML parser for XML Metadata (LOMFR)
 *
 * @author  Vincent Micheli
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package sharedmetadata_lomfr
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/mod/sharedresource/classes/metadata_xml_parser.class.php';
require_once $CFG->dirroot.'/mod/sharedresource/classes/sharedresource_metadata.class.php';

/**
 * Custom XML parser class for XML Metadata
 */


class metadata_xml_parser_lomfr extends metadata_xml_parser {
    /**
     * Constructor creates and initialises parser resource and calls initialise
     *
     * @return bool True
     */
    function __construct() {
        parent::__construct();
        return $this->initialise();
    }

    /**
     * Set default element handlers and initialise properties to empty.
     *
     * @return bool True
     */
    function initialise() {

        $this->parser = xml_parser_create();
        xml_set_object($this->parser, $this);

        xml_set_element_handler($this->parser, 'start_element', 'end_element');
        xml_set_character_data_handler($this->parser, 'default_data');

        $this->current_path = '';
        $this->start_discard = 0;
        $this->ignored_nodes = ['LOM', 'STRING', 'DATETIME', 'VALUE'];
        $this->duration_nodes = ['DURATION', 'TYPICALLEARNINGTIME'];

        $this->current_meta = null;
        $this->metadata = [];

        $this->title_node = '/GENERAL/TITLE';
        $this->url_node = '/TECHNICAL/LOCATION';
        $this->description_node = '/GENERAL/DESCRIPTION';
        $this->language_node = '/GENERAL/LANGUAGE';

        $this->title = '';
        $this->url = '';
        $this->description = '';
        $this->url_index = 0;
        $this->language = '';

        $this->plugin = 'lomfr';

        $this->nodes_tree = [
            '/GENERAL' => [
                'item' => '1',
                'iteration' => -1,
            ],
            '/GENERAL/IDENTIFIER' => [
                'item' => '1_1',
                'iteration' => -1,
            ],
            '/GENERAL/IDENTIFIER/CATALOG' => [
                'item' => '1_1_1',
                'iteration' => -1,
            ],
            '/GENERAL/IDENTIFIER/ENTRY' => [
                'item' => '1_1_2',
                'iteration' => -1,
            ],
            '/GENERAL/TITLE' => [
                'item' => '1_2',
                'iteration' => -1,
            ],
            '/GENERAL/LANGUAGE' => [
                'item' => '1_3',
                'iteration' => -1,
            ],
            '/GENERAL/DESCRIPTION' => [
                'item' => '1_4',
                'iteration' => -1,
            ],
            '/GENERAL/KEYWORD' => [
                'item' => '1_5',
                'iteration' => -1,
            ],
            '/GENERAL/COVERAGE' => [
                'item' => '1_6',
                'iteration' => -1,
            ],
            '/GENERAL/STRUCTURE' => [
                'item' => '1_7',
                'iteration' => -1,
            ],
            '/GENERAL/AGGREGATIONLEVEL' => [
                'item' => '1_8',
                'iteration' => -1,
            ],
            '/GENERAL/DOCUMENTTYPE' => [
                'item' => '1_9',
                'iteration' => -1,
            ],
            '/LIFECYCLE' => [
                'item' => '2',
                'iteration' => -1,
            ],
            '/LIFECYCLE/VERSION' => [
                'item' => '2_1',
                'iteration' => -1,
            ],
            '/LIFECYCLE/STATUS' => [
                'item' => '2_2',
                'iteration' => -1,
            ],
            '/LIFECYCLE/CONTRIBUTE' => [
                'item' => '2_3',
                'iteration' => -1,
            ],
            '/LIFECYCLE/CONTRIBUTE/ROLE' => [
                'item' => '2_3_1',
                'iteration' => -1,
            ],
            '/LIFECYCLE/CONTRIBUTE/ENTITY' => [
                'item' => '2_3_2',
                'iteration' => -1,
            ],
            '/LIFECYCLE/CONTRIBUTE/DATE' => [
                'item' => '2_3_3',
                'iteration' => -1,
            ],
            '/METAMETADATA' => [
                'item' => '3',
                'iteration' => -1,
            ],
            '/METAMETADATA/IDENTIFIER' => [
                'item' => '3_1',
                'iteration' => -1,
            ],
            '/METAMETADATA/IDENTIFIER/CATALOG' => [
                'item' => '3_1_1',
                'iteration' => -1,
            ],
            '/METAMETADATA/IDENTIFIER/ENTRY' => [
                'item' => '3_1_2',
                'iteration' => -1,
            ],
            '/METAMETADATA/CONTRIBUTE' => [
                'item' => '3_2',
                'iteration' => -1,
            ],
            '/METAMETADATA/CONTRIBUTE/ROLE' => [
                'item' => '3_2_1',
                'iteration' => -1,
            ],
            '/METAMETADATA/CONTRIBUTE/ENTITY' => [
                'item' => '3_2_2',
                'iteration' => -1,
            ],
            '/METAMETADATA/CONTRIBUTE/DATE' => [
                'item' => '3_2_3',
                'iteration' => -1,
            ],
            '/METAMETADATA/METADATASCHEMA' => [
                'item' => '3_3',
                'iteration' => -1,
            ],
            '/METAMETADATA/LANGUAGE' => [
                'item' => '3_4',
                'iteration' => -1,
            ],
            '/TECHNICAL' => [
                'item' => '4',
                'iteration' => -1,
            ],
            '/TECHNICAL/FORMAT' => [
                'item' => '4_1',
                'iteration' => -1,
            ],
            '/TECHNICAL/SIZE' => [
                'item' => '4_2',
                'iteration' => -1,
            ],
            '/TECHNICAL/LOCATION' => [
                'item' => '4_3',
                'iteration' => -1,
            ],
            '/TECHNICAL/REQUIREMENT' => [
                'item' => '4_4',
                'iteration' => -1,
            ],
            '/TECHNICAL/REQUIREMENT/ORCOMPOSITE' => [
                'item' => '4_4_1',
                'iteration' => -1,
            ],
            '/TECHNICAL/REQUIREMENT/ORCOMPOSITE/TYPE' => [
                'item' => '4_4_1_1',
                'iteration' => -1,
            ],
            '/TECHNICAL/REQUIREMENT/ORCOMPOSITE/NAME' => [
                'item' => '4_4_1_2',
                'iteration' => -1,
            ],
            '/TECHNICAL/REQUIREMENT/ORCOMPOSITE/MINIMUMVERSION' => [
                'item' => '4_4_1_3',
                'iteration' => -1,
            ],
            '/TECHNICAL/REQUIREMENT/ORCOMPOSITE/MAXIMUMVERSION' => [
                'item' => '4_4_1_4',
                'iteration' => -1,
            ],
            '/TECHNICAL/INSTALLATIONREMARKS' => [
                'item' => '4_5',
                'iteration' => -1,
            ],
            '/TECHNICAL/OTHERPLATFORMREQUIREMENTS' => [
                'item' => '4_6',
                'iteration' => -1,
            ],
            '/TECHNICAL/DURATION' => [
                'item' => '4_7',
                'iteration' => -1,
            ],
            '/EDUCATIONAL' => [
                'item' => '5',
                'iteration' => -1,
            ],
            '/EDUCATIONAL/INTERACTIVITYTYPE' => [
                'item' => '5_1',
                'iteration' => -1,
            ],
            '/EDUCATIONAL/LEARNINGRESOURCETYPE' => [
                'item' => '5_2',
                'iteration' => -1,
            ],
            '/EDUCATIONAL/INTERACTIVITYLEVEL' => [
                'item' => '5_3',
                'iteration' => -1,
            ],
            '/EDUCATIONAL/SEMANTICDENSITY' => [
                'item' => '5_4',
                'iteration' => -1,
            ],
            '/EDUCATIONAL/INTENDEDENDUSERROLE' => [
                'item' => '5_5',
                'iteration' => -1,
            ],
            '/EDUCATIONAL/CONTEXT' => [
                'item' => '5_6',
                'iteration' => -1,
            ],
            '/EDUCATIONAL/TYPICALAGERANGE' => [
                'item' => '5_7',
                'iteration' => -1,
            ],
            '/EDUCATIONAL/DIFFICULTY' => [
                'item' => '5_8',
                'iteration' => -1,
            ],
            '/EDUCATIONAL/TYPICALLEARNINGTIME' => [
                'item' => '5_9',
                'iteration' => -1,
            ],
            '/EDUCATIONAL/DESCRIPTION' => [
                'item' => '5_10',
                'iteration' => -1,
            ],
            '/EDUCATIONAL/LANGUAGE' => [
                'item' => '5_11',
                'iteration' => -1,
            ],
            '/EDUCATIONAL/ACTIVITY' => [
                'item' => '5_12',
                'iteration' => -1,
            ],
            '/EDUCATIONAL/CREDIT' => [
                'item' => '5_13',
                'iteration' => -1,
            ],
            '/RIGHTS' => [
                'item' => '6',
                'iteration' => -1,
            ],
            '/RIGHTS/COST' => [
                'item' => '6_1',
                'iteration' => -1,
            ],
            '/RIGHTS/COPYRIGHTANDOTHERRESTRICTIONS' => [
                'item' => '6_2',
                'iteration' => -1,
            ],
            '/RIGHTS/DESCRIPTION' => [
                'item' => '6_3',
                'iteration' => -1,
            ],
            '/RELATION' => [
                'item' => '7',
                'iteration' => -1,
            ],
            '/RELATION/KIND' => [
                'item' => '7_1',
                'iteration' => -1,
            ],
            '/RELATION/RESOURCE' => [
                'item' => '7_2',
                'iteration' => -1,
            ],
            '/RELATION/RESOURCE/IDENTIFIER' => [
                'item' => '7_2_1',
                'iteration' => -1,
            ],
            '/RELATION/RESOURCE/IDENTIFIER/CATALOG' => [
                'item' => '7_2_1_1',
                'iteration' => -1,
            ],
            '/RELATION/RESOURCE/IDENTIFIER/ENTRY' => [
                'item' => '7_2_1_2',
                'iteration' => -1,
            ],
            '/RELATION/RESOURCE/DESCRIPTION' => [
                'item' => '7_2_2',
                'iteration' => -1,
            ],
            '/ANNOTATION' => [
                'item' => '8',
                'iteration' => -1,
            ],
            '/ANNOTATION/ENTITY' => [
                'item' => '8_1',
                'iteration' => -1,
            ],
            '/ANNOTATION/DATE' => [
                'item' => '8_2',
                'iteration' => -1,
            ],
            '/ANNOTATION/DESCRIPTION' => [
                'item' => '8_3',
                'iteration' => -1,
            ],
            '/CLASSIFICATION' => [
                'item' => '9',
                'iteration' => -1,
            ],
            '/CLASSIFICATION/PURPOSE' => [
                'item' => '9_1',
                'iteration' => -1,
            ],
            '/CLASSIFICATION/TAXONPATH' => [
                'item' => '9_2',
                'iteration' => -1,
            ],
            '/CLASSIFICATION/TAXONPATH/SOURCE' => [
                'item' => '9_2_1',
                'iteration' => -1,
            ],
            '/CLASSIFICATION/TAXONPATH/TAXON' => [
                'item' => '9_2_2',
                'iteration' => -1,
            ],
            '/CLASSIFICATION/TAXONPATH/TAXON/ID' => [
                'item' => '9_2_2_1',
                'iteration' => -1,
            ],
            '/CLASSIFICATION/TAXONPATH/TAXON/ENTRY' => [
                'item' => '9_2_2_2',
                'iteration' => -1,
            ],
            '/CLASSIFICATION/DESCRIPTION' => [
                'item' => '9_3',
                'iteration' => -1,
            ],
            '/CLASSIFICATION/KEYWORD' => [
                'item' => '9_4',
                'iteration' => -1,
            ],
        ];

        return true;
    }

    /**
     * Parse a block of XML text
     *
     * @return bool True on success - false on failure
     */
    function parse($data) {
        $p = xml_parse($this->parser, $data);
        return (bool)$p;
    }

    /**
     * Destroy the parser and free up any related resource.
     */
    function free_resource() {
        $free = xml_parser_free($this->parser);
    }

    /**
     * Reset child nodes iteration
     */
    function reset_childs($path) {
        foreach ($this->nodes_tree as $key => $value) {
            if (strpos($key, $path.'/') === 0) {
                $this->nodes_tree[$key]['iteration'] = -1;
            }
        }
    }

    /**
     * Get Element path (ie 0_0_1)
     */
    function get_elempath($path) {
        $elempath = '';
        $depth = substr_count($path, '/');
        $tmppath = $path;
        $elempathtab = [];
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
    function start_element($parser, $name, $attrs) {
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
    function default_data($parser, $data) {
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
    function end_element($parser, $name) {
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
    function add_identifier(&$metadata, $catalog, $identifier, $entryid) {

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
