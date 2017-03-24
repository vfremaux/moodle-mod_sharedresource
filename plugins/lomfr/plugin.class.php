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
 * @author  Valery Fremaux valery@valeisti.fr
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 * @subpackage sharedresource_lomfr
 */

/**
 * Extend the base resource class for file resources.
 */
require_once($CFG->dirroot.'/mod/sharedresource/sharedresource_plugin_base.class.php');
require_once($CFG->dirroot.'/lib/accesslib.php');

class sharedresource_plugin_lomfr extends sharedresource_plugin_base {

<<<<<<< HEAD
	var $namespace;
	
	var $context;
=======
    // we may setup a context in which we can decide where users 
    // can be assigned role regarding metadata

    protected $namespace;
>>>>>>> MOODLE_32_STABLE

    protected $context;

    public $ALLSOURCES = array('lomfr', 'lom');

    public $DEFAULTSOURCE = 'LOMv1.0';

<<<<<<< HEAD
	function __construct($entryid = 0){
		$this->entryid = $entryid;
		$this->context = context_system::instance();
		$this->pluginname = 'lomfr';
		$this->namespace = 'lomfr';
	}	
=======
    public $OTHERSOURCES = array(
        'LOMFRv1.0' => array(
        'collection', 'ensemble de données', 'événement', 'image', 'image en mouvement', 'image fixe', 'logiciel', 'objet physique', 'ressource interactive', 'service', 'son', 'texte',
        'contributeur',
        'linux', 'firefox', 'safari',
        'démonstration', 'animation', 'tutoriel', 'glossaire', 'guide', 'matériel de référence', 'méthodologie', 'outil', 'scénario pédagogique',
        'enseignement primaire', 'enseignement secondaire', 'licence', 'master', 'mastère', 'doctorat', 'formation continue', 'formation en entreprise',
        'animer', 'apprendre', 'collaborer', 'communiquer', 'coopérer', 'créer', 'échanger', 'lire', 'observer', 'organiser', 'produire', 'publier', 'rechercher', 's\'auto-former', 's\'exercer', 's\'informer', 'se former', 'simuler', 's\'évaluer',
        'est associée à', 'est la traduction de', 'fait l\'objet d\'une traduction', 'est prérequis de', 'a pour prérequis'
        )
    );
>>>>>>> MOODLE_32_STABLE

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
                '5' => 'list',
                '6' => 'single',
                '7' => 'list',
                '8' => 'list',
                '9' => 'list'
            )
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
                '1_8' => 'single',
                '1_9' => 'list'
            ),
            'checked' => array(
                'system'  => 1,
                'indexer' => 1,
                'author'  => 1,
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
                'system'  => 1,
                'indexer' => 1,
                'author'  => 1,
            )
        ),
        '1_1_1' => array(
            'name' => 'Catalog',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 1,
                'author'  => 1,
            ),
            'widget' => 'freetext',
        ),
        '1_1_2' => array(
            'name' => 'Entry',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 1,
                'author'  => 1,
            ),
            'widget' => 'freetext',
        ),
        '1_2' => array(
            'name' => 'Title',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 1,
                'author'  => 1,
            ),
            'widget' => 'freetext',
        ),
        '1_3' => array(
            'name' => 'Language',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 1,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '1_4' => array(
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 1,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '1_5' => array(
            'name' => 'Keyword',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '1_6' => array(
            'name' => 'Coverage',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '1_7' => array(
            'name' => 'Structure',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('atomic', 'collection', 'networked', 'hierarchical', 'linear'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '1_8' => array(
            'name' => 'Aggregation Level',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('1', '2', '3', '4'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '1_9' => array(
            'name' => 'Document Type',
            'source' => 'lomfr',
            'type' => 'select',
            'values' => array('collection', 'ensemble de données', 'événement', 'image', 'image en mouvement', 'image fixe', 'logiciel', 'objet physique', 'ressource interactive', 'service', 'son', 'texte'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 1,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 1,
                'author'  => 0,
            )
        ),
        '2_1' => array(
            'name' => 'Version',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 1,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '2_2' => array(
            'name' => 'Status',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('draft', 'final', 'revised', 'unavailable'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 1,
                'author'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '2_3' => array(
            'name' => 'Contribute',
            'source' => 'lom',
            'type' => 'category',
            'childs' => array(
                '2_3_1' => 'single',
                '2_3_2' => 'list',
                '2_3_3' => 'single'
            ),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            )
        ),
        '2_3_1' => array(
            'name' => 'Role',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('author', 'publisher', 'unknown', 'initiator', 'terminator', 'validator', 'editor', 'graphical designer', 'technical implementer', 'content provider', 'technical validator', 'educational validator', 'script writer', 'instructional designer', 'subject matter expert', 'contributor'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '2_3_2' => array(
            'name' => 'Entity',
            'source' => 'lom',
            'type' => 'vcard',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '2_3_3' => array(
            'name' => 'Date',
            'source' => 'lom',
            'type' => 'date',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            )
        ),
        '3_1_1' => array(
            'name' => 'Catalog',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '3_1_2' => array(
            'name' => 'Entry',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            )
        ),
        '3_2_1' => array(
            'name' => 'Role',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('creator', 'validator'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'select',
        ),
        '3_2_2' => array(
            'name' => 'Entity',
            'source' => 'lom',
            'type' => 'vcard',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '3_2_3' => array(
            'name' => 'Date',
            'source' => 'lom',
            'type' => 'date',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'date',
        ),
        '3_3' => array(
            'name' => 'Metadata Schema',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            )
        ),
        '3_4' => array(
            'name' => 'Language',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 1,
                'author'  => 0,
            )
        ),
        '4_1' => array(
            'name' => 'Format',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 1,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '4_2' => array(
            'name' => 'Size',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'numeric',
        ),
        '4_3' => array(
            'name' => 'Location',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 1,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            )
        ),
        '4_4_1_1' => array(
            'name' => 'Type',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('operating system', 'browser'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'select',
        ),
        '4_4_1_2' => array(
            'name' => 'Name',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('pc-dos', 'ms-windows', 'macos', 'unix', 'multi-os', 'none', 'linux', 'any', 'netscape communicator', 'ms-internet explorer', 'opera', 'amaya', 'firefox', 'safari'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '4_4_1_3' => array(
            'name' => 'Minimum Version',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '4_4_1_4' => array(
            'name' => 'Maximum Version',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '4_5' => array(
            'name' => 'Installation Remarks',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '4_6' => array(
            'name' => 'Other Platform Requirements',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '4_7' => array(
            'name' => 'Duration',
            'source' => 'lom',
            'type' => 'duration',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                '5_11' => 'list',
                '5_12' => 'list',
                '5_13' => 'list'
            ),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            )
        ),
        '5_1' => array(
            'name' => 'Interactivity Type',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('active', 'expositive', 'mixed'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_2' => array(
            'name' => 'Learning Resource Type',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('exercise', 'simulation', 'questionnaire', 'diagram', 'figure', 'graph', 'index', 'slide', 'table', 'narrative text', 'exam', 'experiment', 'problem statement', 'self assessment', 'lecture', 'démonstration', 'animation', 'tutoriel', 'glossaire', 'guide', 'matériel de référence', 'méthodologie', 'outil', 'scénario pédagogique'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_3' => array(
            'name' => 'Interactivity Level',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('very low', 'low', 'medium', 'high', 'very high' ),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_4' => array(
            'name' => 'Semantic Density',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('very low', 'low', 'medium', 'high', 'very high' ),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_5' => array(
            'name' => 'Intended End User Role',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('teacher', 'author', 'learner', 'manager'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_6' => array(
            'name' => 'Context',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('school', 'higher education', 'training', 'other', 'enseignement primaire', 'enseignement secondaire', 'license', 'master', 'mastère', 'doctorat', 'formation continue', 'formation en entreprise'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_7' => array(
            'name' => 'Typical Age Range',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '5_8' => array(
            'name' => 'Difficulty',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('very easy', 'easy', 'medium', 'difficult', 'very difficult'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_9' => array(
            'name' => 'Typical Learning Time',
            'source' => 'lom',
            'type' => 'duration',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'duration',
        ),
        '5_10' => array(
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '5_11' => array(
            'name' => 'Language',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '5_12' => array(
            'name' => 'Activity',
            'source' => 'lomfr',
            'type' => 'select',
            'values' => array('animer', 'apprendre', 'collaborer', 'communiquer', 'coopérer', 'créer', 'échanger', 'lire', 'observer', 'organiser', 'produire', 'publier', 'rechercher', 's\'auto-former', 's\'exercer', 's\'informer', 'se former', 'simuler', 's\'évaluer'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'selectmultiple',
        ),
        '5_13' => array(
            'name' => 'Assessment',//rajouter fichier langue
            'source' => 'lomfr',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 1,
                'author'  => 0,
            )
        ),
        '6_1' => array(
            'name' => 'Cost',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('yes', 'no'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'select',
        ),
        '6_2' => array(
            'name' => 'Copyright And Other Restrictions',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('yes', 'no'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 1,
                'author'  => 0,
            ),
            'widget' => 'select',
        ),
        '6_3' => array(
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            )
        ),
        '7_1' => array(
            'name' => 'Kind',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('ispartof', 'haspart', 'isversionof', 'hasversion', 'isformatof', 'hasformat', 'references', 'isreferencedby', 'isbasedon', 'isbasisfor', 'requires', 'isrequiredby', 'est associée à', 'est la traduction de', 'fait l\'objet d\'une traduction', 'est prérequis de', 'a pour prérequis'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            )
        ),
        '7_2_1_1' => array(
            'name' => 'Catalog',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '7_2_1_2' => array(
            'name' => 'Entry',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '7_2_2' => array(
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            )
        ),
        '8_1' => array(
            'name' => 'Entity',
            'source' => 'lom',
            'type' => 'vcard',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'freetext',
        ),
        '8_2' => array(
            'name' => 'Date',
            'source' => 'lom',
            'type' => 'date',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'date',
        ),
        '8_3' => array(
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 1,
                'author'  => 0,
            )
        ),
        '9_1' => array(
            'name' => 'Purpose',
            'source' => 'lom',
            'type' => 'select',
            'values' => array('discipline', 'idea', 'prerequisite', 'educational objective', 'accessibility restrictions', 'educational level', 'skill level', 'security level', 'competency'),
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
        ),
        '9_2_1' => array(
            'name' => 'Source',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
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
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            )
        ),
        '9_2_2_1' => array(
            'name' => 'Id',
            'source' => 'lom',
            'type' => 'codetext',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            )
        ),
        '9_2_2_2' => array(
            'name' => 'Entry',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            ),
            'widget' => 'treeselect',
        ),
        '9_3' => array(
            'name' => 'Description',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 0,
                'author'  => 0,
            )
        ),
        '9_4' => array(
            'name' => 'Keyword',
            'source' => 'lom',
            'type' => 'text',
            'checked' => array(
                'system'  => 1,
                'indexer' => 1,
                'author'  => 0,
            )
        )
    );

    public function __construct($entryid = 0) {
        $this->entryid = $entryid;
        $this->context = context_system::instance();
        $this->pluginname = 'lomfr';
        $this->namespace = 'lomfr';
    }

    /**
<<<<<<< HEAD
    * Special form handler for Taxum
    *
    */
    function sharedresource_entry_definition_taxum(&$mform, $table, $idfield, $entryfield, $context){
        global $DB;
    	if (empty($idfield) || empty($entryfield)){    	
	    	$optionsrec = $DB->get_records_select($table, "$context", "$idfield, $entryfield", "$idfield");
	    	foreach($optionssrec as $id => $option){
	    		$options[$id] = " $id $option";
	    	}
	    	$mform->addElement('select', 'lom_TaxonPath', get_string('TaxonPath', 'sharedresource'), $options); 
	    }
    }

    /**
    * prints a full configuration form allowing element by element selection against the user profile
    * regarding to metadata
    */
    function configure($config){
    	// initiate
    	$selallstr = get_string('selectall', 'sharedresource');
    	$selnonestr = get_string('selectnone', 'sharedresource');
		echo '<legend><b>&nbsp;'.get_string('lomfrformat', 'sharedresource').'</b></legend>';
		echo "<br/><center>";
		echo '<table border="1px" width="90%"><tr><td colspan="4">';
		echo '</td></tr>';    	
		echo '<tr><td width="30%"><b>&nbsp;'.get_string('fieldname', 'sharedresource').'</b></td>';
		echo '<td width="15%" align=\'center\'><b>'.get_string('system', 'sharedresource').'</b><br/><a href="javascript:selectall(\'system\', \'lomfr\')">'.$selallstr.'</a>/<a href="javascript:selectnone(\'system\', \'lomfr\')">'.$selnonestr.'</a></td>';
		echo '<td width="15%" align=\'center\'><b>'.get_string('indexer', 'sharedresource').'</b><br/><a href="javascript:selectall(\'indexer\', \'lomfr\')">'.$selallstr.'</a>/<a href="javascript:selectnone(\'indexer\', \'lomfr\')">'.$selnonestr.'</a></td>';
		echo '<td width="15%" align=\'center\'><b>'.get_string('author', 'sharedresource').'</b><br/><a href="javascript:selectall(\'author\', \'lomfr\')">'.$selallstr.'</a>/<a href="javascript:selectnone(\'author\', \'lomfr\')">'.$selnonestr.'</a></td>';
		echo '<td width="15%" align=\'center\'><b>'.get_string('widget', 'sharedresource').'</b></td></tr>';
    	echo '</table>';
    	foreach(array_keys($this->METADATATREE['0']['childs']) as $fieldid){
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
    function print_configure_rec($fieldid, $parentnode = '0'){
    	static $indent = 0;
		if (!array_key_exists($fieldid, $this->METADATATREE)){
			print_error('metadatastructureerror', 'sharedresource');
		} 
    	$field = $this->METADATATREE[$fieldid];
		$checked_system = (get_config('sharedresource_lomfr', "config_lomfr_system_{$fieldid}")) ? 'checked="checked"' : '' ;
		$checked_indexer = (get_config('sharedresource_lomfr', "config_lomfr_indexer_{$fieldid}")) ? 'checked="checked"' : '' ;
		$checked_author = (get_config('sharedresource_lomfr', "config_lomfr_author_{$fieldid}")) ? 'checked="checked"' : '' ;
		$activewidgets = unserialize(get_config(NULL,'activewidgets'));
		$checked_widget = '';
		if (!empty($activewidgets)){
			foreach($activewidgets as $key => $widget){
				if($widget->id == $fieldid){
					$checked_widget = 'checked="checked"';
				}
			}
		}
		$indentsize = 15 * $indent;
		$lowername = strtolower($field['name']);
		$fieldname = get_string(clean_string_key($lowername), 'sharedresource');
		if ($field['type'] == 'category'){
			echo "<tr";
			if($parentnode == '0'){
				echo " bgcolor='#E1E2E2'";
			}
			echo "><td width='30%' align=\"left\" style=\"padding-left:{$indentsize}px\"><b>&nbsp;{$fieldname}</b></td>";
		} else {
			echo "<tr><td width='30%' align=\"left\" style=\"padding-left:{$indentsize}px\">&nbsp;{$fieldname}</td>";
		}
		if($parentnode == '0'){
			echo "<td width='15%' align='center'><input id=\"lomfr_system_{$fieldid}\" type=\"checkbox\" name=\"config_lomfr_system_{$fieldid}\" $checked_system value=\"1\" onclick=\"toggle_childs('lomfr', 'system', '{$fieldid}')\" /></td>";
			echo "<td width='15%' align='center'><input id=\"lomfr_indexer_{$fieldid}\" type=\"checkbox\" name=\"config_lomfr_indexer_{$fieldid}\" $checked_indexer value=\"1\" onclick=\"toggle_childs('lomfr', 'indexer', '{$fieldid}')\" /></td>";
			echo "<td width='15%' align='center'><input id=\"lomfr_author_{$fieldid}\" type=\"checkbox\" name=\"config_lomfr_author_{$fieldid}\" $checked_author value=\"1\" onclick=\"toggle_childs('lomfr', 'author', '{$fieldid}')\" /></td>";
			if(isset($field['widget'])){
				echo "<td width='15%' align='center'><input id=\"lomfr_widget_{$fieldid}\" type=\"checkbox\" name=\"widget_lomfr_{$fieldid}\" $checked_widget value=\"1\"/></td></tr>";
			} else {
				echo "<td width='15%' align='center'></td></tr>";
			}
		} else {
			if($checked_system=='checked="checked"'){
				echo "<td align='center'><input id=\"lomfr_system_{$fieldid}\" type=\"checkbox\" name=\"config_lomfr_system_{$fieldid}\" $checked_system value=\"1\" onclick=\"toggle_childs('lomfr', 'system', '{$fieldid}')\"/></td>";
			} else {
				echo "<td align='center'><input id=\"lomfr_system_{$fieldid}\" type=\"checkbox\" name=\"config_lomfr_system_{$fieldid}\" $checked_system value=\"1\" onclick=\"toggle_childs('lomfr', 'system', '{$fieldid}')\" DISABLED/></td>";
			}
			if($checked_indexer=='checked="checked"'){
				echo "<td align='center'><input id=\"lomfr_indexer_{$fieldid}\" type=\"checkbox\" name=\"config_lomfr_indexer_{$fieldid}\" $checked_indexer value=\"1\" onclick=\"toggle_childs('lomfr', 'indexer', '{$fieldid}')\" /></td>";
			} else {
				echo "<td align='center'><input id=\"lomfr_indexer_{$fieldid}\" type=\"checkbox\" name=\"config_lomfr_indexer_{$fieldid}\" $checked_indexer value=\"1\" onclick=\"toggle_childs('lomfr', 'indexer', '{$fieldid}')\" DISABLED/></td>";
			}
			if($checked_author=='checked="checked"'){
				echo "<td align='center'><input id=\"lomfr_author_{$fieldid}\" type=\"checkbox\" name=\"config_lomfr_author_{$fieldid}\" $checked_author value=\"1\" onclick=\"toggle_childs('lomfr', 'author', '{$fieldid}')\"/></td>";
			} else {
				echo "<td align='center'><input id=\"lomfr_author_{$fieldid}\" type=\"checkbox\" name=\"config_lomfr_author_{$fieldid}\" $checked_author value=\"1\" onclick=\"toggle_childs('lomfr', 'author', '{$fieldid}')\" DISABLED/></td>";
			}
			if(isset($field['widget'])){
				if($checked_widget=='checked="checked"'){
					echo "<td align='center'><input id=\"lomfr_widget_{$fieldid}\" type=\"checkbox\" name=\"widget_lomfr_{$fieldid}\" $checked_widget value=\"1\"/></td></tr>";
				} else {
					echo "<td align='center'><input id=\"lomfr_widget_{$fieldid}\" type=\"checkbox\" name=\"widget_lomfr_{$fieldid}\" $checked_widget value=\"1\"/></td></tr>";
				}
			} else {
				echo "<td align='center'></td></tr>";
			}
		}
		$i = 1;
		if ($field['type'] == 'category'){
			if (!empty($field['childs'])){
				foreach(array_keys($field['childs']) as $childfieldid){
					$indent++;
					$this->print_configure_rec($childfieldid, $parentnode.'_'.$i);
					$indent--;			
					$i++;
				}
			}
		}
    }

    // a weak implementation using only in resource title and description.
    function search(&$fromform, &$result) {
        global $CFG,$DB;
        $fromform->title        = isset($fromform->title) ? true : false;
        $fromform->description  = isset($fromform->description) ? true : false;
        // if the search criteria is left blank then this is a complete browse
        if ($fromform->search == '') {
            $fromform->search = '*';
        }
        if ($fromform->section == 'block') {
            $fromform->title = true;
            $fromform->description = true;
        }
        $searchterms = explode(" ", $fromform->search);    // Search for words independently
        foreach ($searchterms as $key => $searchterm) {
            if (strlen($searchterm) < 2) {
                unset($searchterms[$key]);
            }
        }
        // no valid search terms so lets just open it up
        if (count($searchterms) == 0) {
            $searchterms[]= '%';
        }
        $search = trim(implode(" ", $searchterms));
        //to allow case-insensitive search for postgesql
        if ($CFG->dbfamily == 'postgres') {
            $LIKE = 'ILIKE';
            $NOTLIKE = 'NOT ILIKE';   // case-insensitive
            $REGEXP = '~*';
            $NOTREGEXP = '!~*';
        } else {
            $LIKE = 'LIKE';
            $NOTLIKE = 'NOT LIKE';
            $REGEXP = 'REGEXP';
            $NOTREGEXP = 'NOT REGEXP';
        }
        $titlesearch        = '';
        $descriptionsearch  = '';
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
        $selectsqlor    = '';
        if($fromform->title && $search){
            $selectsql     .= $titlesearch;
            $selectsqlor    = ' OR ';
        }
        if($fromform->description && $search){
            $selectsql     .= $selectsqlor.$descriptionsearch;
            $selectsqlor    = ' OR ';
        }
        $selectsql .= ')';
        $sort = "title ASC";
        $page = '';
        $recordsperpage = SHAREDRESOURCE_SEARCH_LIMIT;
        if ($fromform->title || $fromform->description) {
            // when given a complete wildcard, then this is browse mode
            if ($fromform->search == '*') {
                $resources = $DB->get_records('sharedresource_entry', null, $sort);	// A VERIFIER !!!
            } else {
				$sql = 'SELECT * FROM '. $selectsql .' ORDER BY '. $sort;
                $resources = $DB->get_records_sql($sql, null, $page, $recordsperpage); // A VERIFIER !!!
            }
        }
        // append the results
        if (!empty($resources)) {
            foreach ($resources as $resource) {
                //$result[] = new sharedresource_entry($resource);
            }
        }
    }

    function get_cardinality($element, &$fields, &$cardinality){
		if(!($this->METADATATREE[$element]['type'] == 'category' || $this->METADATATREE[$element]['type'] == 'root')) return;
		foreach($this->METADATATREE[$element]['childs'] as $elem => $value){
			if($value == 'list'){
				$cardinality[$elem] = 0;
				foreach($fields as $field){
					if(strpos($field->element, "$elem:") === 0){
						$cardinality[$elem]++;
					}
				}
			}
		    $this->get_cardinality($elem, $fields, $cardinality);
		}
    }

    /** 
    * generates a full XML metadata document attached to the resource entry
    */
    function get_metadata(&$sharedresource_entry, $namespace = null){
        global $SITE, $DB, $CFG;
        if (empty($namespace)) ($namespace = $CFG->{'pluginchoice'}) or ($namespace = 'lom');
        // cleanup some values
        if ($sharedresource_entry->description == '$@NULL@$') $sharedresource_entry->description = '';
        // default
        $lang = substr(current_language(), 0, 2);
        $fields = $DB->get_records('sharedresource_metadata' , array('entry_id' => $sharedresource_entry->id, 'namespace' => $namespace));
		// construct cardinality table
		$cardinality = array();
		$this->get_cardinality('0', $fields, $cardinality);
        foreach($fields as $field){
		    $parts = explode(':',$field->element);
			$element = $parts[0];
			$path = @$parts[1];
            if (!isset($metadata[$element])){
                 $metadata[$element] =  array();
            }
            $metadata[$element][$path] = $field->value;
            if($element == '3_4') $lang = $field->value;
        }
        $languageattr = 'language="'.$lang.'"';
        $lom = "
=======
     * Provides lom metadata fragment header
     */
    function lomHeader() {
        return "
>>>>>>> MOODLE_32_STABLE
            <lom:lom xmlns:lom=\"http://ltsc.ieee.org/xsd/LOM\" 
                        xmlns:lomfr=\"http://www.lom-fr.fr/xsd/LOMFR\">";
    }

    /**
     * Generates metadata element as XML
     *
     */
    function generate_xml($elem, &$metadata, &$languageattr, &$fatherstr, &$cardinality, $pathcode) {

        $value = $this->METADATATREE[$elem];
        $tmpname = str_replace(' ','',$value['name']);
        $name = strtolower(substr($tmpname,0,1)).substr($tmpname,1);
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
        } elseif ($value['type'] == 'category') {
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
        } elseif (count(@$metadata[$elem]) > 0) {
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
                            if (in_array($metadata[$elem][$path], $this->OTHERSOURCES['LOMFRv1.0'])){
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
     * @param sharedresource_entry   object, reference to sharedresource_entry object
     *        including metadata
     * @return bool, return true to continue to the next handler
     *        false to stop the running of any subsequent plugin handlers.
     */
    function after_save(&$sharedresource_entry) {
        if (!empty($sharedresource_entry->keywords)) {
            $this->setKeywords($sharedresource_entry->keywords);
        }

        if (!empty($sharedresource_entry->title)) {
            $this->setTitle($sharedresource_entry->title);
        }

        if (!empty($sharedresource_entry->description)) {
            $this->setDescription($sharedresource_entry->description);
        }

        return true;
    }

    function after_update(&$sharedresource_entry) {
        if (!empty($sharedresource_entry->keywords)) {
            $this->setKeywords($sharedresource_entry->keywords);
        }

        if (!empty($sharedresource_entry->title)) {
            $this->setTitle($sharedresource_entry->title);
        }

        if (!empty($sharedresource_entry->description)) {
            $this->setDescription($sharedresource_entry->description);
        }

        return true;
    }

<<<<<<< HEAD
	/**
	* purpose must expose the values, so a function to find the purpose field is usefull
	*/
    function getTaxonomyPurposeElement(){
		$element = new StdClass;
    	$element->name = '9_1';
    	$element->type = 'list';
    	$element->values = $this->METADATATREE['9_1']['values'];
    	return $element;
=======
    /**
     * keyword have a special status in metadata form, so a function to find the keyword values
     */
    function getKeywordValues($metadata) {
        $keyelm = $this->getKeywordElement();
        $keykeys = preg_grep("/{$keyelm->name}:.*/", array_keys($metadata));
        $kwlist = array();
        foreach ($keykeys as $k) {
            $kwlist[] = $metadata[$k]->value;
        }
        return implode(', ', $kwlist);
>>>>>>> MOODLE_32_STABLE
    }

    /**
     * title is mapped to sharedresource info, so we'll need to get the element often.
     */
    function getTitleElement() {
        $element = (object)$this->METADATATREE['1_2'];
        $element->node = '1_2';
        return $element;
    }

    /**
     * description is mapped to sharedresource info, so we'll need to get the element often.
     */
    function getDescriptionElement() {
        $element = (object)$this->METADATATREE['1_4'];
        $element->node = '1_4';
        return $element;
    }

    /**
     * keyword have a special status in metadata form, so a function to find the keyword field is necessary
     */
    function getKeywordElement() {
        $element = (object)$this->METADATATREE['1_5'];
        $element->node = '1_5';
        return $element;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    function getFileFormatElement() {
        $element = (object)$this->METADATATREE['4_1'];
        $element->node = '4_1';
        return $element;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    function getSizeElement() {
        $element = (object)$this->METADATATREE['4_2'];
        $element->node = '4_2';
        return $element;
    }

    /**
     * location have a special status in metadata form, so a function to find the location field is necessary
     */
    function getLocationElement() {
        $element = (object)$this->METADATATREE['4_3'];
        $element->node = '4_3';
        return $element;
    }

    /**
     * purpose must expose the values, so a function to find the purpose field is usefull
     */
    function getTaxonomyPurposeElement() {
        $element = (object)$this->METADATATREE['9_1'];
        $element->node = '9_1';
        return $element;
    }

    /**
     * Allow to get the taxumpath category and other information about its children node.
     */
    function getTaxumpath() {
        $element = array();
        $element['main'] = "Taxon Path";
        $element['source'] = "9_2_1";
        $element['id'] = "9_2_2_1";
        $element['entry'] = "9_2_2_2";
        return $element;
    }

<<<<<<< HEAD
	/**
	* records keywords in metadata flat table
	*/
    function setKeywords($keywords){
    	global $DB;
    	if (empty($this->entryid)) return; // do not affect metadata to unkown entries
    	$DB->delete_records_select('sharedresource_metadata', " namespace = 'lomfr' AND element LIKE '1_5:0_%' AND entry_id =  ?", array($this->entryid));
    	if ($keywordsarr = explode(',', $keywords)){
    		$i = 0;
	    	foreach($keywordsarr as $aword){
	    		$aword = trim($aword);
	    		$mtdrec = new StdClass;
	    		$mtdrec->entry_id = $this->entryid;
	    		$mtdrec->element = '1_5:0_'.$i;
	    		$mtdrec->namespace = 'lomfr';
	    		$mtdrec->value = $aword;
	    		$DB->insert_record('sharedresource_metadata', $mtdrec);
	    		$i++;
	    	}
	    }
    }
=======
    /**
     * Allow to get the taxumpath category and other information about its children node.
     */
    function getClassification() {
        $element = "9";
        return $element;
    }

    /**
     * records keywords in metadata flat table
     */
    function setKeywords($keywords) {
        global $DB;

        if (empty($this->entryid)) {
            throw new coding_exception('setLocation() : sharedresource entry is null or empty. This should not happen. Please inform developers.');
        }

        $keywordSource = $this->METADATATREE['1_5']['source'];
        $DB->delete_records_select('sharedresource_metadata', " namespace = '{$keywordSource}' AND element LIKE '1_5:0_%' AND entry_id = ? ", array($this->entryid));
        if ($keywordsarr = explode(',', $keywords)) {
            $i = 0;
            foreach ($keywordsarr as $aword) {
                $aword = trim($aword);
                $mtdrec = new StdClass;
                $mtdrec->entry_id = $this->entryid;
                $mtdrec->element = '1_5:0_'.$i;
                $mtdrec->namespace = $keywordSource;
                $mtdrec->value = $aword;
                $DB->insert_record('sharedresource_metadata', $mtdrec);
                $i++;
            }
        }
    }
>>>>>>> MOODLE_32_STABLE
}
