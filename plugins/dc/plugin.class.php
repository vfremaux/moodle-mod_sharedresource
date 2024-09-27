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
 * Dublin Core standard.
 *
 * @package sharedmetadata_dc
 * @author  Valery Fremaux valery.fremaux@gmail.com
 * @copyright  Valery Fremaux valery.fremaux@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
namespace mod_sharedresource;

defined('MOODLE_INTERNAL') || die();

/*
 * Extend the base resource class for file resources.
 */
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_plugin_base.class.php');
require_once($CFG->dirroot.'/lib/accesslib.php');

use context_system;
use StdClass;
use coding_exception;

/**
 * Class for Dublin Core standard.
 */
class plugin_dc extends plugin_base {

    /*
     * we may setup a context in which we can decide where users
     * can be assigned role regarding metadata
     */

    /** @var namespace */
    protected $namespace;

    /** @var context */
    protected $context;

    /** @var list of source namespaces */
    public $allsources = ['dc'];

    /** @var default source */
    public $defaultsource = 'DCMESv1.1';

    /** @var Full tree */
    public $metadatatree = [
        '0' => [
            'name' => 'Root',
            'source' => 'dc',
            'type' => 'root',
            'childs' => [
                '1' => 'single',
                '2' => 'single',
                '3' => 'single',
                '4' => 'single',
                '5' => 'single',
                '6' => 'single',
                '7' => 'single',
                '8' => 'single',
                '9' => 'single',
                '10' => 'single',
                '11' => 'single',
                '12' => 'single',
                '13' => 'single',
                '14' => 'single',
                '15' => 'single',
            ],
        ],
        '1' => [
            'name' => 'Title',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ],
            'childs' => [
                '1_1' => 'single',
                '1_2' => 'single',
            ],
        ],
        '1_1' => [
            'name' => 'title',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ],
        ],
        '1_2' => [
            'name' => 'alternative',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '2' => [
            'name' => 'creator',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ],
            'childs' => [
                '2_1' => 'single',
            ],
        ],
        '2_1' => [
            'name' => 'creator',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ],
        ],
        '3' => [
            'name' => 'Subject',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ],
            'childs' => [
                '3_1' => 'single',
                '3_2' => 'single',
            ],
        ],
        '3_1' => [
            'name' => 'subject',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ],
        ],
        '3_2' => [
            'name' => 'tableOfContent',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ],
            'widget' => 'freetext',
        ],
        '4' => [
            'name' => 'description',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'childs' => [
                '4_1' => 'single',
                '4_2' => 'single',
            ],
        ],
        '4_1' => [
            'name' => 'description',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '4_2' => [
            'name' => 'abstract',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '5' => [
            'name' => 'Publisher',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'childs' => [
                '5_1' => 'list',
            ],
        ],
        '5_1' => [
            'name' => 'Publisher',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '6' => [
            'name' => 'Contributor',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'childs' => [
                '6_1' => 'list',
            ],
        ],
        '6_1' => [
            'name' => 'contributor',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '7' => [
            'name' => 'Date',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'childs' => [
                '7_1' => 'single',
                '7_2' => 'single',
                '7_3' => 'single',
                '7_4' => 'single',
                '7_5' => 'single',
                '7_6' => 'single',
                '7_7' => 'single',
                '7_8' => 'list',
            ],
        ],
        '7_1' => [
            'name' => 'Date',
            'source' => 'dc',
            'type' => 'date',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'date',
        ],
        '7_2' => [
            'name' => 'available',
            'source' => 'dcterm',
            'type' => 'date',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'date',
        ],
        '7_3' => [
            'name' => 'created',
            'source' => 'dcterm',
            'type' => 'date',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'date',
        ],
        '7_4' => [
            'name' => 'dateAccepted',
            'source' => 'dcterm',
            'type' => 'date',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'date',
        ],
        '7_5' => [
            'name' => 'dateCopyrighted',
            'source' => 'dcterm',
            'type' => 'date',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'date',
        ],
        '7_6' => [
            'name' => 'dateSubmitted',
            'source' => 'dcterm',
            'type' => 'date',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'date',
        ],
        '7_7' => [
            'name' => 'issued',
            'source' => 'dcterm',
            'type' => 'date',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'date',
        ],
        '7_8' => [
            'name' => 'modified',
            'source' => 'dcterm',
            'type' => 'date',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'date',
        ],
        '8' => [
            'name' => 'Type',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'childs' => [
                '8_1' => 'single',
            ],
        ],
        '8_1' => [
            'name' => 'Type',
            'source' => 'dcterm',
            'type' => 'select',
            'values' => ['collection', 'dataset', 'event', 'image', 'interactiveresource', 'service', 'software', 'sound', 'text', 'physicalobject'],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'selectmultiple',
        ],
        '9' => [
            'name' => 'Format',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'childs' => [
                '9_1' => 'single',
                '9_2' => 'single',
                '9_3' => 'single',
            ],
        ],
        '9_1' => [
            'name' => 'format',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '9_2' => [
            'name' => 'extent',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '9_3' => [
            'name' => 'medium',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '10' => [
            'name' => 'Identifier',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'childs' => [
                '10_1' => 'single',
                '10_2' => 'list',
            ],
        ],
        '10_1' => [
            'name' => 'Identifier',
            'source' => 'dcterm',
            'type' => 'codetext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '10_2' => [
            'name' => 'bibliographicCitation',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '11' => [
            'name' => 'Source',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'childs' => [
                '11_1' => 'list',
            ],
        ],
        '11_1' => [
            'name' => 'Source',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '12' => [
            'name' => 'Language',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ],
            'childs' => [
                '12_1' => 'list',
            ],
        ],
        '12_1' => [
            'name' => 'Language',
            'source' => 'dcterm',
            'type' => 'codetext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ],
        ],
        '13' => [
            'name' => 'relation',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'childs' => [
                '13_1' => 'list',
                '13_2' => 'list',
                '13_3' => 'list',
                '13_4' => 'list',
                '13_5' => 'list',
                '13_6' => 'list',
                '13_7' => 'list',
                '13_8' => 'list',
                '13_9' => 'list',
                '13_10' => 'list',
                '13_11' => 'list',
                '13_12' => 'list',
                '13_13' => 'list',
                '13_14' => 'list',
            ],
        ],
        '13_1' => [
            'name' => 'relation',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '13_2' => [
            'name' => 'conformsTo',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '13_3' => [
            'name' => 'hasFormat',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '13_4' => [
            'name' => 'hasPart',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '13_5' => [
            'name' => 'hasVersion',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '13_6' => [
            'name' => 'isFormatOf',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '13_7' => [
            'name' => 'isPartOf',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '13_8' => [
            'name' => 'isReferencedBy',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '13_9' => [
            'name' => 'isReplacedBy',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '13_10' => [
            'name' => 'isRequiredBy',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '13_11' => [
            'name' => 'isVersionOf',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '13_12' => [
            'name' => 'references',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '13_13' => [
            'name' => 'replaces',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '13_14' => [
            'name' => 'requires',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '14' => [
            'name' => 'Coverage',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'childs' => [
                '14_1' => 'list',
                '14_2' => 'list',
                '14_3' => 'list',
            ],
        ],
        '14_1' => [
            'name' => 'coverage',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '14_2' => [
            'name' => 'spatial',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '14_3' => [
            'name' => 'temporal',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '15' => [
            'name' => 'rights',
            'source' => 'dc',
            'type' => 'category',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'childs' => [
                '15_1' => 'list',
                '15_2' => 'list',
                '15_3' => 'list',
            ],
        ],
        '15_1' => [
            'name' => 'rights',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '15_2' => [
            'name' => 'accessRights',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '15_3' => [
            'name' => 'license',
            'source' => 'dcterm',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
    ];
    /*
     * phpcs:enable
     */

    /**
     * Constructor
     * @param id $entryid
     */
    public function __construct($entryid = 0) {
        $this->entryid = $entryid;
        $this->context = context_system::instance();
        $this->pluginname = 'dc';
        $this->namespace = 'dc';
    }

    /**
     * Search in metadata
     * @param object $fromform search options from search form
     * @param array $result
     */
    public function search($fromform, & $result) {
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
            $like = 'ILIKE';
            $notlike = 'NOT ILIKE'; // Case-insensitive.
            $regexp = '~*';
            $notregexp = '!~*';
        } else {
            $like = 'LIKE';
            $notlike = 'NOT LIKE';
            $regexp = 'REGEXP';
            $notregexp = 'NOT REGEXP';
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
                $titlesearch        .= " title $regexp '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
                $descriptionsearch  .= " description $regexp '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
            } else if (substr($searchterm, 0, 1) == "-") {
                $searchterm          = substr($searchterm, 1);
                $titlesearch        .= " title $notregexp '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
                $descriptionsearch  .= " description $notregexp '(^|[^a-zA-Z0-9])$searchterm([^a-zA-Z0-9]|$)' ";
            } else {
                $titlesearch        .= ' title '.       $like .' \'%'. $searchterm .'%\' ';
                $descriptionsearch  .= ' description '. $like .' \'%'. $searchterm .'%\' ';
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
                $resources = $DB->get_records('sharedresource_entry', [], $sort); // A VERIFIER !!!
            } else {
                $sql = 'SELECT * FROM '. $selectsql .' ORDER BY '. $sort;
                $resources = $DB->get_records_sql($sql, [], $page, $recordsperpage); // A VERIFIER !!!
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
    public function dc_header() {
        return '<dcds:descriptionSet  xml:base="http://purl.org/dc/terms/"
                    xmlns:dcds="http://purl.org/dc/xmlns/2008/09/01/dc-ds-xml/">';
    }

    /**
     * Generates metadata element as XML (recursive)
     * @param $elem current level element
     * @param array $metadata
     * @param &$languageattr
     * @param string &$fatherstr,
     * @param int &$cardinality
     * @param string $pathcode
     */
    public function generate_xml($elem, $metadata, &$languageattr, &$fatherstr, &$cardinality, $pathcode) {

        $value = $this->metadatatree[$elem];
        $tmpname = str_replace(' ', '', $value['name']);
        $name = strtolower(substr($tmpname, 0, 1)).substr($tmpname, 1);
        $valid = 0;
        $namespace = $value['source'] ?? $this->defaultsource;
        // Category/root : we have to call generate_xml on each child.
        if ($elem == '0') {
            $tab = [];
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
            $tab = [];
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
                // A "node" that contains data.
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
     * @param entry $shrentry   sharedresource_entry object including metadata
     * @return bool, return true to continue to the next handler
     *        false to stop the running of any subsequent plugin handlers.
     */
    public function after_save($shrentry) {
        if (!empty($shrentry->keywords)) {
            $this->set_keywords($shrentry->keywords);
        }

        if (!empty($shrentry->title)) {
            $this->set_title($shrentry->title);
        }

        if (!empty($shrentry->description)) {
            $this->set_description($shrentry->description);
        }

        return true;
    }

    /**
     * Access to the sharedresource_entry object after a sharedresource is updated.
     * Usually we need to transfer some metadata that are both stored in moodle core's model and
     * duplicated in metadata standard.
     *
     * @param sharedresource_entry $shrentry object including metadata
     * @return bool, return true to continue to the next handler
     *        false to stop the running of any subsequent plugin handlers.
     */
    public function after_update($shrentry) {
        if (!empty($shrentry->keywords)) {
            $this->set_keywords($shrentry->keywords);
        }

        if (!empty($shrentry->title)) {
            $this->set_title($shrentry->title);
        }

        if (!empty($shrentry->description)) {
            $this->set_description($shrentry->description);
        }

        return true;
    }

    /**
     * title is mapped to sharedresource info, so we'll need to get the element often.
     */
    public function get_title_element() {
        $element = (object)$this->metadatatree['1_1'];
        $element->node = '1_1';
        return $element;
    }

    /**
     * description is mapped to sharedresource info, so we'll need to get the element often.
     */
    public function get_description_element() {
        $element = (object)$this->metadatatree['4_1'];
        $element->node = '4_1';
        return $element;
    }

    /**
     * keyword have a special status in metadata form, so a function to find the keyword field is necessary
     */
    public function get_keyword_element() {
        return null;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    public function get_file_format_element() {
        $element = (object)$this->metadatatree['9_1'];
        $element->node = '9_1';
        return $element;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    public function get_size_element() {
        $element = (object)$this->metadatatree['9_2'];
        $element->node = '9_2';
        return $element;
    }

    /**
     * location have a special status in metadata form, so a function to find the location field is necessary
     */
    public function get_location_element() {
        $element = (object)$this->metadatatree['11_1'];
        $element->node = '11_1';
        return $element;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     * Dublin Core do not provide any taxonomy capability.
     */
    public function get_taxonomy_purpose_element() {
        return null;
    }

    /**
     * keyword have a special status in metadata form, so a function to find the keyword values
     */
    public function get_keyword_values($metadata) {
        return '';
    }

    /**
     * Allow to get the taxumpath category and other information about its children node.
     */
    public function get_taxum_path() {
        return null;
    }

    /**
     * Allow to get the taxumpath category and other information about its children node.
     */
    public function get_classification() {
        return null;
    }

    /**
     * records keywords in metadata flat table.
     * Null processing, but needs to be there to raise abstraction.
     * @param string $keywords
     */
    public function set_keywords($keywords) {
        return null;
    }

    /**
     * records title in metadata flat table from db attributes
     * @param string $title
     */
    public function set_title($title) {
        global $DB;

        if (empty($this->entryid)) {
            $msg = 'set_title() : sharedresource entry is null or empty. This should not happen. Please inform developers.';
            throw new coding_exception($msg);
        }

        $titlesource = $this->metadatatree['1_1']['source'];
        $params = ['entryid' => $this->entryid, 'namespace' => $titlesource, 'element' => '1_1:0_0'];
        $DB->delete_records('sharedresource_metadata', $params);
        $mtdrec = new StdClass();
        $mtdrec->entryid = $this->entryid;
        $mtdrec->element = '1_1:0_0';
        $mtdrec->namespace = $titlesource;
        $mtdrec->value = $title;

        return $DB->insert_record('sharedresource_metadata', $mtdrec);
    }
}
