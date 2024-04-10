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
 * @author  Valery Fremaux valery.fremaux@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 * @subpackage sharedresource_lom
 */
namespace mod_sharedresource;

defined('MOODLE_INTERNAL') || die();

/*
 * Extend the base resource class for file resources.
 */
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_plugin_base.class.php');
require_once($CFG->dirroot.'/lib/accesslib.php');

class plugin_lom extends plugin_base {

    // we may setup a context in which we can decide where users
    // can be assigned role regarding metadata

    protected $namespace;

    protected $context;

    public $ALLSOURCES = array('lom');

    public $DEFAULTSOURCE = 'LOMv1.0';

    public $METADATATREE = array(
        '0' => array(
            'name' => 'Root',
            'source' => 'lom',
            'type' => 'root',
            'childs' => array(
                '1' => 'single',
                '2' => 'single',
                '3' => 'single',
                '4' => 'single',
                '5' => 'single',
                '6' => 'single',
                '7' => 'single',
                '8' => 'single',
                '9' => 'single'
            ),
        ),
        '1' => array(
            'name' => 'General',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '1_1' => 'list',
                '1_2' => 'single',
                '1_3' => 'list',
                '1_4' => 'list',
                '1_5' => 'list',
                '1_6' => 'list',
                '1_7' => 'single',
                '1_8' => 'single'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            )
        ),
        '1_1' => array(
            'name' => 'Identifier',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '1_1_1' => 'single',
                '1_1_2' => 'single'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            )
        ),
        '1_1_1' => array(
            'name' => 'Catalog',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ),
            'widget' => 'freetext',
        ),
        '1_1_2' => array(
            'name' => 'Entry',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ),
            'widget' => 'freetext',
        ),
        '1_2' => array(
            'name' => 'Title',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ),
            'widget' => 'freetext',
        ),
        '1_3' => array(
            'name' => 'Language',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ),
            'widget' => 'freetext',
        ),
        '1_4' => array(
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'longtext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ),
            'widget' => 'freetext',
        ),
        '1_5' => array(
            'name' => 'Keyword',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '1_6' => array(
            'name' => 'Coverage',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '1_7' => array(
            'name' => 'Structure',
            'source' => 'lom',
            'type' => 'sortedselect',
            'values' => array('atomic', 'collection', 'networked', 'hierarchical', 'linear'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '1_8' => array(
            'name' => 'Aggregation Level',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('1', '2', '3', '4'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '2' => array(
            'name' => 'Life Cycle',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '2_1' => 'single',
                '2_2' => 'single',
                '2_3' => 'list'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            )
        ),
        '2_1' => array(
            'name' => 'Version',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '2_2' => array(
            'name' => 'Status',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('draft', 'final', 'revised', 'unavailable'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '2_3' => array(
            'name' => 'Contribution',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '2_3_1' => 'single',
                '2_3_2' => 'list',
                '2_3_3' => 'single'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '2_3_1' => array(
            'name' => 'Role',
            'source' => 'lom',
            'type' => 'sortedselect',
            'values' => array('author', 'publisher', 'unknown', 'initiator', 'terminator', 'validator', 'editor', 'graphical designer', 'technical implementer',
                        'content provider', 'technical validator', 'educational validator', 'script writer', 'instructional designer', 'subject matter expert'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '2_3_2' => array(
            'name' => 'Entity',
            'source' => 'lom',
            'type' => 'vcard',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '2_3_3' => array(
            'name' => 'Date',
            'source' => 'lom',
            'type' => 'date',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'date',
        ),
        '3' => array(
            'name' => 'Meta-Metadata',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '3_1' => 'list',
                '3_2' => 'list',
                '3_3' => 'list',
                '3_4' => 'single'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '3_1' => array(
            'name' => 'Identifier',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '3_1_1' => 'single',
                '3_1_2' => 'single'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '3_1_1' => array(
            'name' => 'Catalog',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '3_1_2' => array(
            'name' => 'Entry',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '3_2' => array(
            'name' => 'Contribute',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '3_2_1' => 'single',
                '3_2_2' => 'list',
                '3_2_3' => 'single'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '3_2_1' => array(
            'name' => 'Role',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('creator', 'validator'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'select',
        ),
        '3_2_2' => array(
            'name' => 'Entity',
            'source' => 'lom',
            'type' => 'vcard',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '3_2_3' => array(
            'name' => 'Date',
            'source' => 'lom',
            'type' => 'date',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'date',
        ),
        '3_3' => array(
            'name' => 'Metadata Schema',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '3_4' => array(
            'name' => 'Language',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '4' => array(
            'name' => 'Technical',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '4_1' => 'list',
                '4_2' => 'single',
                '4_3' => 'list',
                '4_4' => 'list',
                '4_5' => 'single',
                '4_6' => 'single',
                '4_7' => 'single'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '4_1' => array(
            'name' => 'Format',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '4_2' => array(
            'name' => 'Size',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'numeric',
        ),
        '4_3' => array(
            'name' => 'Location',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '4_4' => array(
            'name' => 'Requirement',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '4_4_1' => 'list'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '4_4_1' => array(
            'name' => 'OrComposite',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '4_4_1_1' => 'single',
                '4_4_1_2' => 'single',
                '4_4_1_3' => 'single',
                '4_4_1_4' => 'single'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '4_4_1_1' => array(
            'name' => 'Type',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('operating system', 'browser'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'select',
        ),
        '4_4_1_2' => array(
            'name' => 'Name',
            'source' => 'lom',
            'type' => 'sortedselect',
            'values' => array('pc-dos', 'ms-windows', 'macos', 'unix', 'multi-os', 'none', 'any', 'netscape communicator', 'ms-internet explorer', 'opera', 'amaya'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '4_4_1_3' => array(
            'name' => 'Minimum Version',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '4_4_1_4' => array(
            'name' => 'Maximum Version',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '4_5' => array(
            'name' => 'Installation Remarks',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '4_6' => array(
            'name' => 'Other Platform Requirements',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '4_7' => array(
            'name' => 'Duration',
            'source' => 'lom',
            'type' => 'duration', // Or text TO CHECK.
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'duration',
        ),
        '5' => array(
            'name' => 'Educational',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '5_1' => 'single',
                '5_2' => 'list',
                '5_3' => 'single',
                '5_4' => 'single',
                '5_5' => 'list',
                '5_6' => 'list',
                '5_7' => 'list',
                '5_8' => 'single',
                '5_9' => 'single',
                '5_10' => 'list',
                '5_11' => 'list'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '5_1' => array(
            'name' => 'Interactivity Type',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('active', 'expositive', 'mixed'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_2' => array(
            'name' => 'Learning Resource Type',
            'source' => 'lom',
            'type' => 'sortedselect',
            'values' => array(
                'exercise',
                'simulation',
                'questionnaire',
                'diagram',
                'figure',
                'graph',
                'index',
                'slide',
                'table',
                'narrative text',
                'exam',
                'experiment',
                'problem statement',
                'self assessment',
                'lecture'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_3' => array(
            'name' => 'Interactivity Level',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('very low', 'low', 'medium', 'high', 'very high' ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_4' => array(
            'name' => 'Semantic Density',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('very low', 'low', 'medium', 'high', 'very high' ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_5' => array(
            'name' => 'Intended End User Role',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('teacher', 'author', 'learner', 'manager'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_6' => array(
            'name' => 'Context',
            'source' => 'lom',
            'type' => 'select',
            'values' => array(
                'school',
                'higher education',
                'training',
                'other'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_7' => array(
            'name' => 'Typical Age Range',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '5_8' => array(
            'name' => 'Difficulty',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('very easy', 'easy', 'medium', 'difficult', 'very difficult'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_9' => array(
            'name' => 'Typical Learning Time',
            'source' => 'lom',
            'type' => 'duration', // Or text TO CHECK.
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'duration',
        ),
        '5_10' => array(
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'longtext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '5_11' => array(
            'name' => 'Language',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '6' => array(
            'name' => 'Rights',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '6_1' => 'single',
                '6_2' => 'single',
                '6_3' => 'single'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '6_1' => array(
            'name' => 'Cost',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('yes', 'no'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'select',
        ),
        '6_2' => array(
            'name' => 'Copyright And Other Restrictions',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('yes', 'no'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'select',
        ),
        '6_3' => array(
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'longtext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '7' => array(
            'name' => 'Relation',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '7_1' => 'single',
                '7_2' => 'single'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '7_1' => array(
            'name' => 'Kind',
            'source' => 'lom',
            'type' => 'select',
            'values' => array(
                'ispartof',
                'haspart',
                'isversionof',
                'hasversion',
                'isformatof',
                'hasformat',
                'references',
                'isreferencedby',
                'isbasedon'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '7_2' => array(
            'name' => 'Resource',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '7_2_1' => 'list',
                '7_2_2' => 'list'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '7_2_1' => array(
            'name' => 'Identifier',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '7_2_1_1' => 'single',
                '7_2_1_2' => 'single'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '7_2_1_1' => array(
            'name' => 'Catalog',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '7_2_1_2' => array(
            'name' => 'Entry',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '7_2_2' => array(
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'longtext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '8' => array(
            'name' => 'Annotation',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '8_1' => 'single',
                '8_2' => 'single',
                '8_3' => 'single'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '8_1' => array(
            'name' => 'Entity',
            'source' => 'lom',
            'type' => 'vcard',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '8_2' => array(
            'name' => 'Date',
            'source' => 'lom',
            'type' => 'date',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'date',
        ),
        '8_3' => array(
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'longtext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '9' => array(
            'name' => 'Classification',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '9_1' => 'single',
                '9_2' => 'list',
                '9_3' => 'single',
                '9_4' => 'list'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '9_1' => array(
            'name' => 'Purpose',
            'source' => 'lom',
            'type' => 'sortedselect',
            'values' => array(
                'discipline',
                'idea',
                'prerequisite',
                'educational objective',
                'accessibility restrictions',
                'educational level',
                'skill level',
                'security level',
                'competency'),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '9_2' => array(
            'name' => 'Taxon Path',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '9_2_1' => 'single',
                '9_2_2' => 'list'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
        ),
        '9_2_1' => array(
            'name' => 'Source',
            'source' => 'lom',
            'type' => 'select',
            'func' => array('class' => '\local_sharedresources\browser\navigation', 'method' => 'get_taxonomies_menu'),
            'extraclass' => 'taxonomy-source',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '9_2_2' => array(
            'name' => 'Taxum',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '9_2_2_1' => 'single',
                '9_2_2_2' => 'single'
            ),
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '9_2_2_1' => array(
            'name' => 'Id',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
            'widget' => 'treeselect',
        ),
        '9_2_2_2' => array(
            'name' => 'Entry',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ),
        ),
        '9_3' => array(
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'longtext',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        ),
        '9_4' => array(
            'name' => 'Keyword',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            )
        )
    );

    public function __construct($entryid = 0) {
        $this->entryid = $entryid;
        $this->context = \context_system::instance();
        $this->pluginname = 'lom';
        $this->namespace = 'lom';
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
        if (!empty($resources)) {
            foreach ($resources as $resource) {
                $result[] = new entry($resource);
            }
        }
    }

    /**
     * Provides lom metadata fragment header
     */
    public function lomHeader() {
        return "
            <lom:lom xmlns:lom=\"http://ltsc.ieee.org/xsd/LOM\"
                        xmlns:lomfr=\"http://www.lom-fr.fr/xsd/LOMFR\"
                            xmlns:scolomfr=\"http://www.lom-fr.fr/xsd/SCOLOMFR\">";
    }

    /**
     * Generates metadata element as XML
     *
     */
    public function generate_xml($elem, &$metadata, &$languageattr, &$fatherstr, &$cardinality, $pathcode) {

        $value = $this->METADATATREE[$elem];
        $tmpname = str_replace(' ', '', $value['name']);
        $name = strtolower(substr($tmpname, 0, 1)).substr($tmpname, 1);
        $valid = 0;
        $namespace = @$value['source'];
        // Category/root : we have to call generate_xml on each child.
        if ($elem == '0') {
            $tab = array();
            $childnum = 0;
            foreach ($value['childs'] as $child => $multiplicity) {
                $tab[$childnum] = '';
                if (isset($cardinality[$child]) && $cardinality[$child] != 0) {
                    for ($i = 0; $i < $cardinality[$child]; $i++) {
                        $valid = ($this->generate_xml($child, $metadata, $languageattr, $tab[$childnum], $cardinality, $i) || $valid);
                        $childnum++;
                    }
                } else {
                    $valid = ($this->generate_xml($child, $metadata, $languageattr, $tab[$childnum], $cardinality, '0') || $valid);
                    $childnum++;
                }
            }
            for ($i = 0; $i < count($tab); $i++) {
                $fatherstr .= $tab[$i];
            }
        } else if ($value['type'] == 'category') {
            $tab = array();
            $childnum = 0;
            foreach ($value['childs'] as $child => $multiplicity) {
                $tab[$childnum] = '';
                if (isset($cardinality[$child]) && $cardinality[$child] != 0) {
                    for ($i = 0; $i < $cardinality[$child]; $i++) {
                        $valid = ($this->generate_xml($child, $metadata, $languageattr, $tab[$childnum], $cardinality, $pathcode.'_'.$i) || $valid);
                        $childnum++;
                    }
                } else {
                    $valid = ($this->generate_xml($child, $metadata, $languageattr, $tab[$childnum], $cardinality, $pathcode.'_0') || $valid);
                    $childnum++;
                }
            }
            // At least one child has content.
            if ($valid) {
                $fatherstr .= "\n<{$namespace}:{$name}>";
                for ($i = 0; $i < count($tab); $i++) {
                    $fatherstr .= $tab[$i];
                }
                $fatherstr .= "
                </{$namespace}:{$name}>";
            }
        } else if (count(@$metadata[$elem]) > 0) {
            foreach ($metadata[$elem] as $path => $val) {
                // a "node" that contains data
                if (strpos($path, $pathcode) === 0) {
                    switch ($value['type']) {
                        case 'text':
                            $fatherstr .= "\n<{$namespace}:{$name}>
                            <{$namespace}:string $languageattr>".$metadata[$elem][$path]."</{$namespace}:string>
                        </{$namespace}:{$name}>";
                            break;

                        case 'select':
                            if (in_array($metadata[$elem][$path], $this->OTHERSOURCES['LOMv1.0'])) {
                                $source = 'LOMv1.0';
                            } else {
                                $source = $this->DEFAULTSOURCE;
                            }
                            $fatherstr .= "
                        <{$namespace}:{$name}>
                            <{$namespace}:source>".$source."</{$namespace}:source>
                            <{$namespace}:value>".$metadata[$elem][$path]."</{$namespace}:value>
                        </{$namespace}:{$name}>";
                            break;

                        case 'date':
                            $fatherstr .= "
                        <{$namespace}:{$name}>
                            <{$namespace}:dateTime>".$metadata[$elem][$path]."</{$namespace}:dateTime>
                        </{$namespace}:{$name}>";
                            break;

                        case 'duration':
                            $fatherstr .= "
                        <{$namespace}:{$name}>
                            <{$namespace}:duration>".$metadata[$elem][$path]."</{$namespace}:duration>
                        </{$namespace}:{$name}>";
                            break;

                        default:
                            $fatherstr .= "
                        <{$namespace}:{$name}>".$metadata[$elem][$path]."</{$namespace}:{$name}>";
                    }
                    $valid = 1;
                }
            }
        }
        return $valid;
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
        if (!empty($shrentry->keywords)) {
            $this->setKeywords($shrentry->keywords);
        }

        if (!empty($shrentry->title)) {
            $this->setTitle($shrentry->title);
        }

        if (!empty($shrentry->description)) {
            $this->setDescription($shrentry->description);
        }

        return true;
    }

    public function after_update(&$shrentry) {
        if (!empty($shrentry->keywords)) {
            $this->setKeywords($shrentry->keywords);
        }

        if (!empty($shrentry->title)) {
            $this->setTitle($shrentry->title);
        }

        if (!empty($shrentry->description)) {
            $this->setDescription($shrentry->description);
        }

        return true;
    }

    /**
     * title is mapped to sharedresource info, so we'll need to get the element often.
     */
    public function getTitleElement() {
        $element = (object)$this->METADATATREE['1_2'];
        $element->node = '1_2';
        return $element;
    }

    /**
     * description is mapped to sharedresource info, so we'll need to get the element often.
     */
    public function getDescriptionElement() {
        $element = (object)$this->METADATATREE['1_4'];
        $element->node = '1_4';
        return $element;
    }

    /**
     * keyword have a special status in metadata form, so a function to find the keyword field is necessary
     */
    public function getKeywordElement() {
        $element = (object)$this->METADATATREE['1_5'];
        $element->node = '1_5';
        return $element;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    public function getFileFormatElement() {
        $element = (object)$this->METADATATREE['4_1'];
        $element->node = '4_1';
        return $element;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    public function getSizeElement() {
        $element = (object)$this->METADATATREE['4_2'];
        $element->node = '4_2';
        return $element;
    }

    /**
     * location have a special status in metadata form, so a function to find the location field is necessary
     */
    public function getLocationElement() {
        $element = (object)$this->METADATATREE['4_3'];
        $element->node = '4_3';
        return $element;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    public function getTaxonomyPurposeElement() {
        $element = (object)$this->METADATATREE['9_1'];
        $element->node = '9_1';
        return $element;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    public function getTaxonomyValueElement() {
        $element = (object)$this->METADATATREE['9_2_2_1'];
        $element->node = '9_2_2_1';
        return $element;
    }

    /**
     * keyword have a special status in metadata form, so a function to find the keyword values
     */
    public function getKeywordValues($metadata) {
        $keyelm = $this->getKeywordElement();
        $keykeys = preg_grep("/{$keyelm->node}:.*/", array_keys($metadata));
        $kwlist = array();
        foreach ($keykeys as $k) {
            $kwlist[] = $metadata[$k]->get_value();
        }
        return implode(', ', $kwlist);
    }

    /**
     * Get the metadata elements identifiers that stores a taxon index binding
     * for a resource. the "main" designates the root of a complete taxon
     * entry in metadata. Taxon may be composed of a set of subproperties.
     * The "source" holds a reference to a classification source, @see table mdl_sharedresource_classif
     * The "id" points to the local id to the taxon in the taxonoy source table
     * The "entry" contains a textual recomposed full path to the taxon from taxonomy root.
     */
    public function getTaxumpath() {
        $element = array();
        $element['mainname'] = "Taxon Path";
        $element['source'] = "9_2_1";
        $element['main'] = "9_2_2";
        $element['id'] = "9_2_2_1";
        $element['entry'] = "9_2_2_2";
        return $element;
    }

    /**
     * Gets the metadata node identifier that provides classification storage capability.
     */
    public function getClassification() {
        $element = "9";
        return $element;
    }

    /**
     * Tells if the node's value is assimilable to a sharedresource entry.
     */
    public function isResourceIndex($nodeid) {
        return $nodeid == '7_2_1_2';
    }

    /**
     * versionned sharedresources entry must use Relation elements to link each other.
     */
    public function getVersionSupportElement() {
        $element = array();
        $element['mainname'] = "Relation";
        $element['main'] = "7";
        $element['kind'] = "7_1";
        $element['catalog'] = "7_2_1_1";
        $element['entry'] = "7_2_1_2";
        return $element;
    }

    /**
     * records keywords in metadata flat table
     */
    public function setKeywords($keywords) {
        global $DB;

        if (empty($this->entryid)) {
            throw new \coding_exception('setLocation() : sharedresource entry is null or empty. This should not happen. Please inform developers.');
        }

        $keywordSource = $this->METADATATREE['1_5']['source'];
        $select = " namespace = '{$keywordSource}' AND element LIKE '1_5:0_%' AND entryid = ? ";
        $DB->delete_records_select('sharedresource_metadata', $select, array($this->entryid));
        if ($keywordsarr = explode(',', $keywords)) {
            $i = 0;
            foreach ($keywordsarr as $aword) {
                $aword = trim($aword);
                $mtdrec = new \StdClass;
                $mtdrec->entryid = $this->entryid;
                $mtdrec->element = '1_5:0_'.$i;
                $mtdrec->namespace = $keywordSource;
                $mtdrec->value = $aword;
                $DB->insert_record('sharedresource_metadata', $mtdrec);
                $i++;
            }
        }
    }

    /**
     * records title in metadata flat table from db attributes
     */
    public function setTitle($title) {
        global $DB;

        if (empty($this->entryid)) {
            throw new \coding_exception('setLocation() : sharedresource entry is null or empty. This should not happen. Please inform developers.');
        }

        $titleSource = $this->METADATATREE['1_2']['source'];
        $DB->delete_records('sharedresource_metadata', array('entryid' => $this->entryid, 'namespace' => $titleSource, 'element' => '1_2:0_0'));
        $mtdrec = new \StdClass;
        $mtdrec->entryid = $this->entryid;
        $mtdrec->element = '1_2:0_0';
        $mtdrec->namespace = $titleSource;
        $mtdrec->value = $title;

        return $DB->insert_record('sharedresource_metadata', $mtdrec);
    }
}
