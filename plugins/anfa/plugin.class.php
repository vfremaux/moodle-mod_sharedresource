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
 * Main plugin implementation file.
 *
 * @author  Valery Fremaux valery.fremaux@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedmetadata_anfa
 * @subpackage sharedresource_anfa
 */

namespace mod_sharedresource;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use StdClass;
use context_system;

/*
 * Extend the base resource class for file resources.
 */
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_plugin_base.class.php');
require_once($CFG->dirroot.'/lib/accesslib.php');

class plugin_anfa extends plugin_base {

    // We may setup a context in which we can decide where users.
    // Can be assigned role regarding metadata.

    protected $namespace;

    protected $context;

    public $allsources = ['anfa', 'lom'];

    public $defaultsource = 'LOMv1.0';

    public $othersources = [];

    public $metadatatree = [
        '0' => [
            'name' => 'Root',
            'source' => 'lom',
            'type' => 'root',
            'childs' => [
                '1' => 'single',
                '2' => 'single',
                '3' => 'single',
                '4' => 'single',
                '5' => 'list',
                '6' => 'single',
                '7' => 'list', /* usefull to handle document version relations. */
    /*                '8' => 'list',    */
                '9' => 'list',
            ],
        ],
        '1' => [
            'name' => 'General',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '1_1' => 'list',
                '1_2' => 'single',
                '1_3' => 'list',
                '1_4' => 'list',
                '1_5' => 'list',
        /*                '1_6' => 'list',
                '1_7' => 'single',
                '1_8' => 'single', */
                '1_9' => 'list',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ],
        ],
        '1_1' => [
            'name' => 'Identifier',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '1_1_1' => 'single',
                '1_1_2' => 'single',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 1,
                'author_read'  => 1,
            ],
        ],
        '1_1_1' => [
            'name' => 'Catalog',
            'source' => 'lom',
            'type' => 'codetext',
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
        '1_1_2' => [
            'name' => 'Entry',
            'source' => 'lom',
            'type' => 'codetext',
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
        '1_2' => [
            'name' => 'Title',
            'source' => 'lom',
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
        '1_3' => [
            'name' => 'Language',
            'source' => 'lom',
            'type' => 'codetext',
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
        '1_4' => [
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'longtext',
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
        '1_5' => [
            'name' => 'Keyword',
            'source' => 'lom',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        /*
        '1_6' => [
            'name' => 'Coverage',
            'source' => 'lom',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        °,
        '1_7' => [
            'name' => 'Structure',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['atomic', 'collection', 'networked', 'hierarchical', 'linear'],
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
        '1_8' => [
            'name' => 'Aggregation Level',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['1', '2', '3', '4'],
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
        */
        '1_9' => [
            'name' => 'Document Type',
            'source' => 'lomfr',
            'type' => 'select',
            /*
            'values' => ['collection', 'ensemble de données', 'événement', 'image', 'image en mouvement', 'image fixe',
            'logiciel', 'objet physique', 'ressource interactive', 'service', 'son', 'texte'],
            */
            'values' => ['Image', 'Vidéo', 'Lien internet', 'Document texte',
                          'Présentation',
                          'Tableur',
                          'Fichier exécutable',
                          'Fichier compressé',
                          'Fichier de conception',
                          'Cours Moodle (.mbz)',
                           'Autres'],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'selectmultiple',
        ],
        '2' => [
            'name' => 'Life Cycle',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '2_1' => 'single',
                '2_2' => 'single',
                '2_3' => 'list',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '2_1' => [
            'name' => 'Version',
            'source' => 'lom',
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
        '2_2' => [
            'name' => 'Status',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['draft', 'final', 'revised', 'unavailable'],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'selectmultiple',
        ],
        '2_3' => [
            'name' => 'Contribute',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '2_3_1' => 'single',
                '2_3_2' => 'list',
                /* '2_3_3' => 'single' */
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '2_3_1' => [
            'name' => 'Role',
            'source' => 'lom',
            'type' => 'sortedselect',
            'values' => ['author', 'publisher', 'unknown', 'initiator', 'terminator', 'validator', 'editor', 'graphical designer',
            'technical implementer', 'content provider', 'technical validator', 'educational validator', 'script writer',
            'instructional designer', 'subject matter expert', 'contributor'],
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
        '2_3_2' => [
            'name' => 'Entity',
            'source' => 'lom',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        /*
        '2_3_3' => [
            'name' => 'Date',
            'source' => 'lom',
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
        */
        '3' => [
            'name' => 'Meta-Metadata',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '3_1' => 'list',
                '3_2' => 'list',
                '3_3' => 'list',
                '3_4' => 'single',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '3_1' => [
            'name' => 'Identifier',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '3_1_1' => 'single',
                '3_1_2' => 'single',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '3_1_1' => [
            'name' => 'Catalog',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '3_1_2' => [
            'name' => 'Entry',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '3_2' => [
            'name' => 'Contribute',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '3_2_1' => 'single',
                '3_2_2' => 'list',
                '3_2_3' => 'single',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '3_2_1' => [
            'name' => 'Role',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['creator', 'validator'],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'select',
        ],
        '3_2_2' => [
            'name' => 'Entity',
            'source' => 'lom',
            'type' => 'vcard',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '3_2_3' => [
            'name' => 'Date',
            'source' => 'lom',
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
        '3_3' => [
            'name' => 'Metadata Schema',
            'source' => 'lom',
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
        '3_4' => [
            'name' => 'Language',
            'source' => 'lom',
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
        '4' => [
            'name' => 'Technical',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '4_1' => 'list',
                '4_2' => 'single',
                '4_3' => 'list',
               /* '4_4' => 'list',
                '4_5' => 'single',
                '4_6' => 'single',
                '4_7' => 'single' */
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '4_1' => [
            'name' => 'Format',
            'source' => 'lom',
            'type' => 'codetext',
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
            'name' => 'Size',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'numeric',
        ],
        '4_3' => [
            'name' => 'Location',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['ecosystemerechercharge',
                'systemessoussystemes',
                'electriciteelectronique',
                'habilitations',
                'diagnostic',
                'parametragelogiciel',
                'chainedetraction',
                'entretiencourant',
                'resourcespartagees'],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'select',
        ],
        /*
        '4_3' => [
            'name' => 'Location',
            'source' => 'lom',
            'type' => 'codetext',
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
        '4_4' => [
            'name' => 'Requirement',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '4_4_1' => 'list'
            ),
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ]
        ],
        '4_4_1' => [
            'name' => 'OrComposite',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '4_4_1_1' => 'single',
                '4_4_1_2' => 'single',
                '4_4_1_3' => 'single',
                '4_4_1_4' => 'single'
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ]
        ],
        '4_4_1_1' => [
            'name' => 'Type',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['operating system', 'browser'),
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'select',
        ],
        '4_4_1_2' => [
            'name' => 'Name',
            'source' => 'lom',
            'type' => 'sortedselect',
            'values' => ['pc-dos', 'ms-windows', 'macos', 'unix', 'multi-os', 'none', 'linux', 'any', 'netscape communicator',
            'ms-internet explorer', 'opera', 'amaya', 'firefox', 'safari'],
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
        '4_4_1_3' => [
            'name' => 'Minimum Version',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '4_4_1_4' => [
            'name' => 'Maximum Version',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '4_5' => [
            'name' => 'Installation Remarks',
            'source' => 'lom',
            'type' => 'longtext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '4_6' => [
            'name' => 'Other Platform Requirements',
            'source' => 'lom',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '4_7' => [
            'name' => 'Duration',
            'source' => 'lom',
            'type' => 'duration',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'duration',
        ],
        */
        '5' => [
            'name' => 'Educational',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
        /*                '5_1' => 'single',   */
                '5_2' => 'list',
        /*                '5_3' => 'single',
                '5_4' => 'single',
                '5_5' => 'list',    */
                '5_6' => 'list',
        /*                '5_7' => 'list',
                '5_8' => 'single',     */
                '5_9' => 'single',
                '5_10' => 'list',
                '5_11' => 'list',
        /*                '5_12' => 'list',
                '5_13' => 'list'   */
                '5_14' => 'list',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        /*
        '5_1' => [
            'name' => 'Interactivity Type',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['active', 'expositive', 'mixed'),
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
        */
        '5_2' => [
            'name' => 'Learning Resource Type',
            'source' => 'lom',
            'type' => 'sortedselect',
            /*
            'values' => ['exercise', 'simulation', 'questionnaire', 'diagram', 'figure', 'graph', 'index', 'slide',
            'table', 'narrative text', 'exam', 'experiment', 'problem statement', 'self assessment', 'lecture', 'démonstration',
            'animation', 'tutoriel', 'glossaire', 'guide', 'matériel de référence', 'méthodologie', 'outil', 'scénario pédagogique'],
            */
            'values' => ['Cours théorique', 'Cours en intégralité', 'Travaux pratiques', 'Evaluations', 'Quiz', 'Documents interactifs',
                         'Vidéos interactives', 'Modélisations 3D', 'Réalité virtuelle',
                          'Réalité augmentée', 'Autres'],
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
        /*
        '5_3' => [
            'name' => 'Interactivity Level',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['very low', 'low', 'medium', 'high', 'very high' ],
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
        '5_4' => [
            'name' => 'Semantic Density',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['very low', 'low', 'medium', 'high', 'very high' ],
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
        '5_5' => [
            'name' => 'Intended End User Role',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['teacher', 'author', 'learner', 'manager'),
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
        */
        '5_6' => [
            'name' => 'Context',
            'source' => 'lom',
            'type' => 'select',
            /*
            'values' => ['school', 'higher education', 'training', 'other', 'enseignement primaire', 'enseignement secondaire',
            'license', 'master', 'mastère', 'doctorat', 'formation continue', 'formation en entreprise'],
            */
            'values' => ['CAP', 'Bac pro', 'BTS', 'Autres'],
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
        /*
        '5_7' => [
            'name' => 'Typical Age Range',
            'source' => 'lom',
            'type' => 'text',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '5_8' => [
            'name' => 'Difficulty',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['very easy', 'easy', 'medium', 'difficult', 'very difficult'],
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
        */
        '5_9' => [
            'name' => 'Typical Learning Time',
            'source' => 'lom',
            'type' => 'select', /* LOM type : duration */
            'values' => [
                'Moins de 30 minutes',
                'Moins d\'une heure',
                'Une heure',
                'Une heure et demie',
                'Deux heures',
                'Trois heures',
                'Quatre heures',
                'Une journée'],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'select',
        ],
        '5_10' => [
            'name' => 'Objectifs pédagogiques',
            'source' => 'anfa',
            'type' => 'longtext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '5_11' => [
            'name' => 'Language',
            'source' => 'lom',
            'type' => 'select', /* LOM codetext */
            'values' => ['Français', 'Anglais', 'Autre'],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'select',
        ],
        /*
        '5_12' => [
            'name' => 'Activity',
            'source' => 'lomfr',
            'type' => 'sortedselect',
            'values' => ['animer', 'apprendre', 'collaborer', 'communiquer', 'coopérer', 'créer', 'échanger', 'lire',
            'observer', 'organiser', 'produire', 'publier', 'rechercher', 's\'auto-former', 's\'exercer', 's\'informer',
            'se former', 'simuler', 's\'évaluer'],
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
        '5_13' => [
            'name' => 'Assessment', // Rajouter fichier langue.
            'source' => 'lomfr',
            'type' => 'codetext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        */
        '5_14' => [
            'name' => 'Diplôme', // Rajouter fichier langue.
            'source' => 'anfa',
            'type' => 'sortedselect',
            'values' => ['cap', 'cap_a', 'cap_b', 'cap_c', 'bacpro', 'bacpro_a', 'bacpro_b', 'bacpro_c', 'bts', 'bts_a', 'bts_b',
            'bts_c', 'other'],
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
        '6' => [
            'name' => 'Rights',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '6_1' => 'single',
                '6_2' => 'single',
                '6_3' => 'single',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '6_1' => [
            'name' => 'Cost',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['needsfee', 'needsnofee'],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'select',
        ],
        '6_2' => [
            'name' => 'Copyright And Other Restrictions',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['yes', 'no'],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'select',
        ],
        '6_3' => [
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'longtext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '7' => [
            'name' => 'Relation',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '7_1' => 'single',
                '7_2' => 'single',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '7_1' => [
            'name' => 'Kind',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['ispartof', 'haspart', 'isversionof', 'hasversion', 'isformatof', 'hasformat', 'references',
            'isreferencedby', 'isbasedon', 'isbasisfor', 'requires', 'isrequiredby', 'est associée à', 'est la traduction de',
            'fait l\'objet d\'une traduction', 'est prérequis de', 'a pour prérequis'],
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
        '7_2' => [
            'name' => 'Resource',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '7_2_1' => 'list',
                '7_2_2' => 'list',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '7_2_1' => [
            'name' => 'Identifier',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '7_2_1_1' => 'single',
                '7_2_1_2' => 'single',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '7_2_1_1' => [
            'name' => 'Catalog',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '7_2_1_2' => [
            'name' => 'Entry',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '7_2_2' => [
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'longtext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        /*
        '8' => [
            'name' => 'Annotation',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '8_1' => 'single',
                '8_2' => 'single',
                '8_3' => 'single'
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ]
        ],
        '8_1' => [
            'name' => 'Entity',
            'source' => 'lom',
            'type' => 'vcard',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '8_2' => [
            'name' => 'Date',
            'source' => 'lom',
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
        '8_3' => [
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'longtext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        */
        '9' => [
            'name' => 'Classification',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '9_1' => 'single',
                '9_2' => 'list',
                '9_3' => 'single',
                '9_4' => 'list',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 1,
                'indexer_read' => 1,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '9_1' => [
            'name' => 'Purpose',
            'source' => 'lom',
            'type' => 'select',
            'values' => ['discipline', 'idea', 'prerequisite', 'educational objective', 'accessibility restrictions',
            'educational level', 'skill level', 'security level', 'competency'],
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
            'name' => 'Taxon Path',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '9_2_1' => 'single',
                '9_2_2' => 'list',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '9_2_1' => [
            'name' => 'Source',
            'source' => 'lom',
            'type' => 'select',
            'func' => ['class' => '\local_sharedresources\browser\navigation', 'method' => 'get_taxonomies_menu'],
            'extraclass' => 'taxonomy-source',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '9_2_2' => [
            'name' => 'Taxum',
            'source' => 'lom',
            'type' => 'category',
            'childs' => [
                '9_2_2_1' => 'single',
                '9_2_2_2' => 'single',
            ],
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
        ],
        '9_2_2_1' => [
            'name' => 'Id',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'treeselect',
        ],
        '9_2_2_2' => [
            'name' => 'Entry',
            'source' => 'lom',
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
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'longtext',
            'checked' => [
                'system_write'  => 1,
                'system_read'  => 1,
                'indexer_write' => 0,
                'indexer_read' => 0,
                'author_write'  => 0,
                'author_read'  => 0,
            ],
            'widget' => 'freetext',
        ],
        '9_4' => [
            'name' => 'Keyword',
            'source' => 'lom',
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
    ];

    public function __construct($entryid = 0) {
        $this->entryid = $entryid;
        $this->context = context_system::instance();
        $this->pluginname = 'anfa';
        $this->namespace = 'anfa';
    }

    /**
     * Provides lom metadata fragment header
     */
    public function lomheader() {
        return "
            <anfa:anfa xmlns:lom=\"http://ltsc.ieee.org/xsd/LOM\"
                        xmlns:anfa=\"http://www.digitalgreta.fr/xsd/ANFA\">";
    }

    /**
     * Generates metadata element as XML
     *
     */
    public function generate_xml($elem, &$metadata, &$languageattr, &$fatherstr, &$cardinality, $pathcode) {

        $value = $this->metadatatree[$elem];
        $tmpname = str_replace(' ', '', $value['name']);
        $name = strtolower(substr($tmpname, 0, 1)).substr($tmpname, 1);
        $valid = 0;
        $namespace = $value['source'] ?? '';
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
        } else if (count($metadata[$elem] ?? []) > 0) {
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
                            if (in_array($metadata[$elem][$path], $this->OTHERSOURCES['LOMFRv1.0'])) {
                                $source = 'LOMFRv1.0';
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
     * @param entry $shrentry sharedresource_entry object including metadata
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
     * @param entry $shrentry sharedresource_entry object including metadata
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
     * keyword have a special status in metadata form, so a function to find the keyword values
     */
    public function get_keyword_values($metadata) {
        $keyelm = $this->get_keyword_element();
        $keykeys = preg_grep("/{$keyelm->node}:.*/", array_keys($metadata));
        $kwlist = [];
        foreach ($keykeys as $k) {
            $kwlist[] = $metadata[$k]->get_value();
        }
        return implode(', ', $kwlist);
    }

    /**
     * title is mapped to sharedresource info, so we'll need to get the element often.
     */
    public function get_title_element() {
        $element = (object)$this->metadatatree['1_2'];
        $element->node = '1_2';
        return $element;
    }

    /**
     * description is mapped to sharedresource info, so we'll need to get the element often.
     */
    public function get_description_element() {
        $element = (object)$this->metadatatree['1_4'];
        $element->node = '1_4';
        return $element;
    }

    /**
     * keyword have a special status in metadata form, so a function to find the keyword field is necessary
     */
    public function get_keyword_element() {
        $element = (object)$this->metadatatree['1_5'];
        $element->node = '1_5';
        return $element;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    public function get_file_format_element() {
        $element = (object)$this->metadatatree['4_1'];
        $element->node = '4_1';
        return $element;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    public function get_size_element() {
        $element = (object)$this->metadatatree['4_2'];
        $element->node = '4_2';
        return $element;
    }

    /**
     * location have a special status in metadata form, so a function to find the location field is necessary
     * DO NOT expose this method. Anfa has derrogated the use of location element to something else that MUST NOT
     * be postprocessed.
     */
    /*
    public function get_location_element() {
        $element = (object)$this->metadatatree['4_3'];
        $element->node = '4_3';
        return $element;
    }
    */

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    public function get_taxonomy_purpose_element() {
        $element = (object)$this->metadatatree['9_1'];
        $element->node = '9_1';
        return $element;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    public function get_taxonomy_value_element() {
        $element = (object)$this->metadatatree['9_2_2_1'];
        $element->node = '9_2_2_1';
        return $element;
    }

    /**
     * Get the metadata elements identifiers that stores a taxon index binding
     * for a resource. the "main" designates the root of a complete taxon
     * entry in metadata. Taxon may be composed of a set of subproperties.
     * The "source" holds a reference to a classification source, @see table mdl_sharedresource_classif
     * The "id" points to the local id to the taxon in the taxonoy source table
     * The "entry" contains a textual recomposed full path to the taxon from taxonomy root.
     */
    public function get_taxum_path() {
        $element = [];
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
    public function get_classification() {
        $element = "9";
        return $element;
    }

    /**
     * Tells if the node's value is assimilable to a sharedresource entry.
     */
    public function is_resource_index($nodeid) {
        return $nodeid == '7_2_1_2';
    }

    /**
     * versionned sharedresources entry must use Relation elements to link each other.
     */
    public function get_version_support_element() {
        $element = [];
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
    public function set_keywords($keywords) {
        global $DB;

        if (empty($this->entryid)) {
            throw new coding_exception('setLocation() : sharedresource entry is null or empty. This should not happen. Please inform developers.');
        }

        $keywordsource = $this->metadatatree['1_5']['source'];
        $select = " namespace = '{$keywordsource}' AND element LIKE '1_5:0_%' AND entryid = ? ";
        $DB->delete_records_select('sharedresource_metadata', $select, [$this->entryid]);
        if ($keywordsarr = explode(',', $keywords)) {
            $i = 0;
            foreach ($keywordsarr as $aword) {
                $aword = trim($aword);
                $mtdrec = new StdClass();
                $mtdrec->entryid = $this->entryid;
                $mtdrec->element = '1_5:0_'.$i;
                $mtdrec->namespace = $keywordsource;
                $mtdrec->value = $aword;
                $DB->insert_record('sharedresource_metadata', $mtdrec);
                $i++;
            }
        }
    }
}
