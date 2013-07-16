<?php 
/**
 *
 * @author  Valery Fremaux valery.fremaux@gmail.com
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */


/**
* Extend the base resource class for file resources
*/
require_once($CFG->dirroot.'/mod/sharedresource/sharedresource_plugin_base.class.php');
require_once($CFG->dirroot.'/lib/accesslib.php');

class sharedresource_plugin_suplomfr extends sharedresource_plugin_base {

	var $context;

	var $DEFAULTSOURCE = 'LOMv1.0';

	var $OTHERSOURCES = array(
	    'LOMFRv1.0' => array(
		'collection', 'ensemble de données', 'événement', 'image', 'image en mouvement', 'image fixe', 'logiciel', 'objet physique', 'ressource interactive', 'service', 'son', 'texte',
		'contributeur',
		'linux', 'firefox', 'safari',
		'démonstration', 'animation', 'tutoriel', 'glossaire', 'guide', 'matériel de référence', 'méthodologie', 'outil', 'scénario pédagogique',
		'enseignement primaire', 'enseignement secondaire', 'licence', 'master', 'mastère', 'doctorat', 'formation continue', 'formation en entreprise',
		'animer', 'apprendre', 'collaborer', 'communiquer', 'coopérer', 'créer', 'échanger', 'lire', 'observer', 'organiser', 'produire', 'publier', 'rechercher', 's\'auto-former', 's\'exercer', 's\'informer', 'se former', 'simuler', 's\'évaluer',
		'est associée à', 'est la traduction de', 'fait l\'objet d\'une traduction', 'est prérequis de', 'a pour prérequis'
	    ),
	    'ScoLOMFRv1.1' => array(
		'enseignement', 'public cible détaillé', 'label', 'type de diffusion', // Scolomfr-voc-028
		'a pour vignette', 'a pour logo', 'est aperçu de', 'a pour aperçu', // Scolomfr-voc-009		
		'annales', 'cyberquête', 'étude de cas', 'jeu éducatif', 'manuel d\'enseignement', 'méthode de langue', 
			'production d\'élève', 'témoignage pédagogique', // Scolomfr-voc-10 
		'expérimenter', // Scolomfr-voc-19
		'en amphithéâtre', 'en atelier', 'en atelier de pédagogie personnalisée', 'en CDI', 'en salle de classe', 
			'en établissement', 'espace dédié à une pratique spécifique', 'en établissement socioculturel', 
			'en bibliothèque médiathèque', 'en mobilité', 'en musée', 'hors établissement', 'en installation de loisirs', 
			'en installation sportive', 'en laboratoire', 'en laboratoire de langues', 'en milieu familial', 
			'en milieu professionnel', 'en entreprise', 'non précisé', 'en salle informatique', 
			'en salle multimédia', // Scolomfr-voc-17
		'à distance','en alternance', 'en autonomie', 'en classe entière', 'en collaboration','en milieu professionnel', 
			'en groupe', 'en groupe de compétences', 'en ligne', 'en tutorat', 'modalité mixte', 'séjour pédagogique', 
			'sortie pédagogique', 'travail de recherche', 'travail en interdisciplinarité', 'travaux dirigés', 
			'travaux pratiques', // Scolomfr-voc-018
		'diffuseur/distributeur', // Scolomfr-voc-03
		'annuaire', 'archives', 'article', 'atlas', 'bande dessinée', 'banque de vidéos', 
			'banque d\'images', 'base de données', 'bibliographie/sitographie', 'biographie', 
			'carte', 'carte heuristique et conceptuelle', 'chronologier', 'collection de documents',
			'compte rendu', 'conférence', 'diaporama', 'dossier documentaire', 'dossier technique',
			'exposition', 'feuille de calcul', 'film', 'image numérique', 'livre numérique',
			'maquette/prototype', 'norme', 'jeu de données', 'objet physique', 'objet 3D',
			'ouvrage', 'partition musicale', 'périodique', 'photographie', 'podcast',
			'présentation multimédia', 'programme scolaire', 'rapport', 'référentiel de compétences',
			'schéma/graphique', 'site web', 'tableau (art)', 'web média', // Scolomfr-voc-005
	    ),
	    'SupLOMFRv1.0' => array(
				'étude de cas', 'liste de références', 'jeu de données', 'autres', // 5.2
         		'bac+2', 'bac+3', 'bac+4', 'bac+5', // 5.6
	       
	    ),
	);

	var $METADATATREE = array(
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
					'author'  => 1,
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
					'author'  => 1,
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
					'author'  => 1,
				)
	    ),
	    '2_1' => array(
		'name' => 'Version',
		'source' => 'lom',
		'type' => 'text',
		'checked' => array(
					'system'  => 1,
					'indexer' => 0,
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
					'indexer' => 0,
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
					'indexer' => 1,
					'author'  => 1,
				)
	    ),
	    '2_3_1' => array(
		'name' => 'Role',
		'source' => 'lom',
		'type' => 'select',
		'values' => array('author', 'publisher', 'unknown', 'initiator', 'terminator', 'validator', 'editor', 'graphical designer', 'technical implementer', 'content provider', 'technical validator', 'educational validator', 'script writer', 'instructional designer', 'subject matter expert', 'contributor'),
		'checked' => array(
					'system'  => 1,
					'indexer' => 1,
					'author'  => 1,
				),
		'widget' => 'selectmultiple',
	    ),
	    '2_3_2' => array(
		'name' => 'Entity',
		'source' => 'lom',
		'type' => 'vcard',
		'checked' => array(
					'system'  => 1,
					'indexer' => 1,
					'author'  => 1,
				),
		'widget' => 'freetext',
	    ),
	    '2_3_3' => array(
		'name' => 'Date',
		'source' => 'lom',
		'type' => 'date',
		'checked' => array(
					'system'  => 1,
					'indexer' => 1,
					'author'  => 1,
				),
		'widget' => 'date',
	    ),
	    '3' => array(
		'name' => 'Meta-Metadata',
		'source' => 'lom',
		'type' => 'category',
		'childs' => array(
				    /* '3_1' => 'list', */
				    '3_2' => 'list',
				    '3_3' => 'list',
				    /* '3_4' => 'single' */
				),
		'checked' => array(
					'system'  => 1,
					'indexer' => 0,
					'author'  => 0,
				)
		),
/*
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
*/
	    '3_2' => array(
		'name' => 'Contribute',
		'source' => 'lom',
		'type' => 'category',
		'childs' => array(
				    '3_2_1' => 'single',
				    /* '3_2_2' => 'list',
				    '3_2_3' => 'single' */
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
/*
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
*/
	    '3_3' => array(
		'name' => 'Metadata Schema',
		'source' => 'lom',
		'type' => 'codetext',
		'checked' => array(
					'system'  => 1,
					'indexer' => 1,
					'author'  => 0,
				)
	    ),
/*
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
*/
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
					'author'  => 1,
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
					'indexer' => 1,
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
					'author'  => 1,
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
					'indexer' => 1,
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
				    /* '5_1' => 'single', */
				    '5_2' => 'list',
				    /* '5_3' => 'single', */
				    /* '5_4' => 'single', */
				    '5_5' => 'list',
				    '5_6' => 'list',
				    '5_7' => 'list',
				    '5_8' => 'single',
				    '5_9' => 'single',
				    /* '5_10' => 'list',
				    '5_11' => 'list',
				    '5_12' => 'list',
				    '5_13' => 'list', */
				),
		'checked' => array(
					'system'  => 1,
					'indexer' => 1,
					'author'  => 0,
				)
	    ),
	    /*
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
	    ), */
	    '5_2' => array(
		'name' => 'Learning Resource Type',
		'source' => 'lom',
		'type' => 'select',
		'values' => array('exercise', 'annales', 'simulation', 'questionnaire', 'diagram', 'cyberquete', 'étude de cas', 'jeu éducatif', 'figure', 'graph', 'index', 'slide', 'table', 'narrative text', 'exam', 'experiment', 'problem statement', 'self assessment', 'lecture', 'manuel d\'enseignement', 'production d\'élève', 'démonstration', 'animation', 'tutoriel', 'glossaire', 'guide', 'matériel de référence', 'méthodologie', 'outil', 'scénario pédagogique', 'méthode de langues', 'témoignage pédagogique'),
		'checked' => array(
					'system'  => 1,
					'indexer' => 1,
					'author'  => 0,
				),
		'widget' => 'selectmultiple',
	    ),
	    /*
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
	    ), */
	    '5_5' => array(
		'name' => 'Intended End User Role',
		'source' => 'lom',
		'type' => 'select',
		'values' => array('teacher', 'author', 'learner', 'manager'),
		'checked' => array(
					'system'  => 1,
					'indexer' => 1,
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
					'indexer' => 1,
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
/*
	    '5_10' => array(
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
	    '5_11' => array(
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
	    '5_12' => array(
		'name' => 'Activity',
		'source' => 'lomfr',
		'type' => 'select',
		'values' => array('animer', 'apprendre', 'collaborer', 'communiquer', 'coopérer', 'créer', 'échanger', 'expérimenter', 'lire', 'observer', 'organiser', 'produire', 'publier', 'rechercher', 's\'auto-former', 's\'exercer', 's\'informer', 'se former', 'simuler', 's\'évaluer'),
		'checked' => array(
					'system'  => 1,
					'indexer' => 0,
					'author'  => 0,
				),
		'widget' => 'selectmultiple',
	    ),
	    '5_13' => array(
		'name' => 'Assessment',
		'source' => 'lomfr',
		'type' => 'codetext',
		'checked' => array(
					'system'  => 1,
					'indexer' => 0,
					'author'  => 0,
				),
		'widget' => 'freetext',
	    ),
*/
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
					'indexer' => 1,
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
		'values' => array('ispartof', 'haspart', 'isversionof', 'hasversion', 'isformatof', 'hasformat', 'references', 'isreferencedby', 'isbasedon', 'isbasisfor', 'requires', 'isrequiredby', 'est associée à', 'est la traduction de', 'fait l\'objet d\'une traduction', 'est prérequis de', 'a pour prérequis', 'a pour vignette', 'a pour logo', 'est aperçue de', 'a pour aperçu'),
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
				    /*
				    '7_2_1' => 'list',
				    '7_2_2' => 'list'
				    */
				),
		'checked' => array(
					'system'  => 1,
					'indexer' => 0,
					'author'  => 0,
				)
	    ),
/*
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
*/
	    '8' => array(
		'name' => 'Annotation',
		'source' => 'lom',
		'type' => 'category',
		'childs' => array(
				    '8_1' => 'single',
				    /* '8_2' => 'single',
				    '8_3' => 'single' */
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
/*
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
*/
	    '9' => array(
		'name' => 'Classification',
		'source' => 'lom',
		'type' => 'category',
		'childs' => array(
				    '9_1' => 'single',
				    '9_2' => 'list',
				    /* '9_3' => 'single',
				    '9_4' => 'list' */
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
/*
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
*/
	);

	function __construct($entryid = 0){
		$this->entryid = $entryid;
		$this->context = context_system::instance();
		$this->pluginname = 'suplomfr';
	}	

    function sharedresource_entry_definition(&$mform){
        global $CFG, $DB;
        $iterators = array();
    	foreach(array_keys($this->METADATATREE['0']['childs']) as $fieldid){
			if (has_capability('mod/sharedresource:systemmetadata', $this->context)){
	    		$metadataswitch = "config_lom_system_";
			} elseif (has_capability('mod/sharedresource:indexermetadata', $this->context)){
	    		$metadataswitch = "config_lom_indexer_";
	    	} else {
	    		$metadataswitch = "config_lom_author_";
	    	}
	    	$mform->metadataswitch = $metadataswitch;
	    	$metadataswitch .= $fieldid;
    		if (get_config('sharedresource_suplomfr', $metadataswitch)){
    			$fieldtype = $this->METADATATREE['0']['childs'][$fieldid];
    			$generic = $this->METADATATREE[$fieldid]['name'];
    			if ($fieldtype == 'list'){
    				if ($instances = $DB->get_records_select('sharedresource_metadata', " entry_id = ? AND namespace = 'lomfr' AND name LIKE '$generic:%' ", array($this->entryid))){
    					$iterators[] = 0;
    					foreach($instances as $instance){
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

    function sharedresource_entry_definition_rec(&$mform, $nodeid, &$iterators){
        global $CFG, $DB;

		if (!array_key_exists($nodeid, $this->METADATATREE)){
			print_error('metadatastructureerror', 'sharedresource');
		} 

		// special trap : Classification taxon,is made of two fields
		if ($this->METADATATREE[$nodeid]['name'] == 'TaxonPath'){
			$source = $this->METADATATREE['9_2_1'];
			if (!empty($source['internalref']) && preg_match("/table=(.*?)&idfield=(.*?)&entryfield=(.*?)&treefield=(.*?)&treestart=(.*?)(?:&context\{(.*?)\})?/", $source['internalref'], $matches)){
				$table = $matches[1];
				$idfield = $matches[2];
				$entryfield = $matches[3];
				$treefield = $matches[4];
				$treestart = $matches[5];
				$context = @$matches[6];
				// we can get Classification list from internal ref
				sharedresource_entry_definition_taxum($mform, $table, $idfield, $entryfield, $treefield, $treestart, $context);
			}
			return;
		}

		// special traps : Classification 

		// common case
		$generic = $this->METADATATREE[$nodeid]['name'];
		if ($this->METADATATREE[$nodeid]['type'] == 'category'){
			$mform->addElement('header', $generic, get_string(str_replace(' ', '_', strtolower($generic)), 'sharedresource'));
			$mform->addElement('hidden', $generic, 1);
	    	foreach(array_keys($this->METADATATREE[$nodeid]['childs']) as $fieldid){
	    		$metadataswitch = $mform->metadataswitch.$fieldid;
    			if (get_config('sharedresource_suplomfr', $metadataswitch)){    			
	    			$this->sharedresource_entry_definition_rec($mform, $fieldid);
	    		}
	    	}
		} elseif ($this->METADATATREE[$nodeid]['type'] == 'list'){
			// get exiting records in db
			$elementinstances = $DB->get_records_select('sharedresource_metadata', " entry_id = ? AND namespace = 'lomfr' and name LIKE '{$generic}:%' ", array($this->entryid) );
			// iterate on instances
    		$metadataswitch = $mform->metadataswitch.$nodeid;
			if ($instances && get_config('sharedresource_suplomfr', $metadataswitch)){
				$iterators[] = 0;
				foreach($instances as $instance){
	    			$this->sharedresource_entry_definition_rec($mform, $fieldid, $iterators);
	    			$iterztor = array_pop($iterators);
	    			$iterator++;
	    			array_push($iterators, $iterator);
				}
			}
	    } else {
    		$metadataswitch = $mform->metadataswitch.$nodeid;
			if (get_config('sharedresource_suplomfr', $metadataswitch)){    			
				$this->sharedresource_entry_definition_scalar($mform, $this->METADATATREE[$nodeid]);
			}
	    }
    }

	/**
	* Form handler for scalar value (regular case)
	*/
    function sharedresource_entry_definition_scalar(&$mform, &$element){

        if ($element['type'] == 'select'){
            $values = $element['values'];
            $options = array();
            foreach($values as $value){
            	$options[$value] = preg_replace('/\[\[|\]\]/', '', get_string(str_replace(' ', '_', strtolower($value)), 'sharedresource'));
            }
        	$mform->addElement($element['type'], $element['name'], get_string(clean_string_key($element['name']), 'sharedresource'), $options);
        } else {
            $mform->addElement($element['type'], $element['name'], get_string(clean_string_key($element['name']), 'sharedresource'));
        }
    }

    /**
    * Special form handler for Taxum
    *
    */
    function sharedresource_entry_definition_taxum(&$mform, $table, $idfield, $entryfield, $context){
		global $DB;
    	if (empty($idfield) || empty($entryfield)){    	
	    	$optionsrec = $DB->get_records_select($table, "$context", array(), "$idfield, $entryfield", "$idfield");
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

		echo '<legend><b>&nbsp;'.get_string('suplomfrformat', 'sharedresource').'</b></legend>';
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
		$checked_system = (get_config('sharedresource_suplomfr', "config_suplomfr_system_{$fieldid}")) ? 'checked="checked"' : '' ;
		$checked_indexer = (get_config('sharedresource_suplomfr', "config_suplomfr_indexer_{$fieldid}")) ? 'checked="checked"' : '' ;
		$checked_author = (get_config('sharedresource_suplomfr', "config_suplomfr_author_{$fieldid}")) ? 'checked="checked"' : '' ;

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
		$fieldname = strtolower(clean_string_key($field['name']));
		$fieldname = get_string($fieldname, 'sharedresource');
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
			echo "<td width='15%' align='center'><input id=\"lomfr_system_{$fieldid}\" type=\"checkbox\" name=\"config_suplomfr_system_{$fieldid}\" $checked_system value=\"1\" onclick=\"toggle_childs('lomfr', 'system', '{$fieldid}')\" /></td>";
			echo "<td width='15%' align='center'><input id=\"lomfr_indexer_{$fieldid}\" type=\"checkbox\" name=\"config_suplomfr_indexer_{$fieldid}\" $checked_indexer value=\"1\" onclick=\"toggle_childs('lomfr', 'indexer', '{$fieldid}')\" /></td>";
			echo "<td width='15%' align='center'><input id=\"lomfr_author_{$fieldid}\" type=\"checkbox\" name=\"config_suplomfr_author_{$fieldid}\" $checked_author value=\"1\" onclick=\"toggle_childs('lomfr', 'author', '{$fieldid}')\" /></td>";
			if(isset($field['widget'])){
				echo "<td width='15%' align='center'><input id=\"lomfr_widget_{$fieldid}\" type=\"checkbox\" name=\"widget_suplomfr_{$fieldid}\" $checked_widget value=\"1\"/></td></tr>";
			} else {
				echo "<td width='15%' align='center'></td></tr>";
			}
		} else {
			if($checked_system == 'checked="checked"'){
				echo "<td align='center'><input id=\"lomfr_system_{$fieldid}\" type=\"checkbox\" name=\"config_suplomfr_system_{$fieldid}\" $checked_system value=\"1\" onclick=\"toggle_childs('lomfr', 'system', '{$fieldid}')\"/></td>";
			} else {
				echo "<td align='center'><input id=\"lomfr_system_{$fieldid}\" type=\"checkbox\" name=\"config_suplomfr_system_{$fieldid}\" $checked_system value=\"1\" onclick=\"toggle_childs('lomfr', 'system', '{$fieldid}')\" DISABLED/></td>";
			}
			if($checked_indexer == 'checked="checked"'){
				echo "<td align='center'><input id=\"lomfr_indexer_{$fieldid}\" type=\"checkbox\" name=\"config_suplomfr_indexer_{$fieldid}\" $checked_indexer value=\"1\" onclick=\"toggle_childs('lomfr', 'indexer', '{$fieldid}')\" /></td>";
			} else {
				echo "<td align='center'><input id=\"lomfr_indexer_{$fieldid}\" type=\"checkbox\" name=\"config_suplomfr_indexer_{$fieldid}\" $checked_indexer value=\"1\" onclick=\"toggle_childs('lomfr', 'indexer', '{$fieldid}')\" DISABLED/></td>";
			}
			if($checked_author == 'checked="checked"'){
				echo "<td align='center'><input id=\"lomfr_author_{$fieldid}\" type=\"checkbox\" name=\"config_suplomfr_author_{$fieldid}\" $checked_author value=\"1\" onclick=\"toggle_childs('lomfr', 'author', '{$fieldid}')\"/></td>";
			} else {
				echo "<td align='center'><input id=\"lomfr_author_{$fieldid}\" type=\"checkbox\" name=\"config_suplomfr_author_{$fieldid}\" $checked_author value=\"1\" onclick=\"toggle_childs('lomfr', 'author', '{$fieldid}')\" DISABLED/></td>";
			}
			if(isset($field['widget'])){
				if($checked_widget == 'checked="checked"'){
					echo "<td align='center'><input id=\"lomfr_widget_{$fieldid}\" type=\"checkbox\" name=\"widget_suplomfr_{$fieldid}\" $checked_widget value=\"1\"/></td></tr>";
				} else {
					echo "<td align='center'><input id=\"lomfr_widget_{$fieldid}\" type=\"checkbox\" name=\"widget_suplomfr_{$fieldid}\" $checked_widget value=\"1\"/></td></tr>";
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
        global $CFG, $DB;

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
                $resources = $DB->get_records('sharedresource_entry', array(), $sort);	// A VERIFIER !!!
            } else {
				$sql = 'SELECT * FROM '. $selectsql .' ORDER BY '. $sort;
                $resources = $DB->get_records_sql($sql, array(), $page, $recordsperpage); // A VERIFIER !!!
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
        global $SITE, $CFG, $DB;
        if (empty($namespace)) ($namespace = $CFG->{'pluginchoice'}) or ($namespace = 'lom');
        // cleanup some values
        if ($sharedresource_entry->description == '$@NULL@$') $sharedresource_entry->description = '';
        // default
        $lang = substr(current_language(), 0, 2);
        $fields = $DB->get_records('sharedresource_metadata', array('entry_id' => $sharedresource_entry->id, 'namespace' => $namespace));
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
            <lom:lom xmlns:lom=\"http://ltsc.ieee.org/xsd/LOM\" 
                        xmlns:lomfr=\"http://www.lom-fr.fr/xsd/LOMFR\">";
		$tmpstr = '';
		if($this->generate_xml('0', $metadata, $languageattr, $tmpstr, $cardinality, '')){
		   $lom .= $tmpstr;
		}
        $lom .= "
            </lom:lom>
            ";
        return $lom;
    }

    function generate_xml($elem, &$metadata, &$languageattr, &$fatherstr, &$cardinality, $pathcode){

        $value = $this->METADATATREE[$elem];
        $tmpname = str_replace(' ','',$value['name']);
		$name = strtolower(substr($tmpname,0,1)).substr($tmpname,1);
        $valid = 0;
        $namespace = @$value['source'];
        // category/root : we have to call generate_xml on each child
        if($elem == '0'){
            $tab = array();
            $childnum = 0;
            foreach($value['childs'] as $child => $multiplicity){
				$tab[$childnum] = '';
                if(isset($cardinality[$child]) && $cardinality[$child] != 0){
				    for ($i = 0; $i < $cardinality[$child]; $i++){
						$valid = ($this->generate_xml($child, $metadata, $languageattr, $tab[$childnum], $cardinality, $i) || $valid);
						$childnum++;
					}
                } else {
				    $valid = ($this->generate_xml($child, $metadata, $languageattr, $tab[$childnum], $cardinality, '0') || $valid);
				    $childnum++;
				}
            }
		    for ( $i = 0; $i < count($tab); $i++){
				$fatherstr .= $tab[$i];
			}
        }
        elseif($value['type'] == 'category'){
            $tab = array();
            $childnum = 0;
            foreach($value['childs'] as $child => $multiplicity){
				$tab[$childnum] = '';
                if(isset($cardinality[$child]) && $cardinality[$child] != 0){
				    for ($i = 0; $i < $cardinality[$child]; $i++){
						$valid = ($this->generate_xml($child, $metadata, $languageattr, $tab[$childnum], $cardinality, $pathcode.'_'.$i) || $valid);
						$childnum++;
					}
                } else {
				    $valid = ($this->generate_xml($child, $metadata, $languageattr, $tab[$childnum], $cardinality, $pathcode.'_0') || $valid);
				    $childnum++;
				}
            }
            // at least one child has content
            if($valid){
                $fatherstr .= "
                <{$namespace}:{$name}>";
                for ( $i = 0; $i < count($tab); $i++){
                        $fatherstr .= $tab[$i];
                    }
                $fatherstr .= "
                </{$namespace}:{$name}>";
            }
        }
        elseif(count(@$metadata[$elem]) > 0){
            foreach ($metadata[$elem] as $path => $val){
				// a "node" that contains data 
				if(strpos($path, $pathcode) === 0){
						switch($value['type']){
                    case 'text':
                        $fatherstr .= "
                    <{$namespace}:{$name}>
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
    function after_save(&$sharedresource_entry){
		if (!empty($sharedresource_entry->keywords)){ 
			$this->setKeywords($sharedresource_entry->keywords);
		}

		if (!empty($sharedresource_entry->title)){ 
	        $this->setTitle($sharedresource_entry->title);
	    }

		if (!empty($sharedresource_entry->description)){ 
	        $this->setDescription($sharedresource_entry->description);
	    }

        return true;
    }

    function after_update(&$sharedresource_entry){
		if (!empty($sharedresource_entry->keywords)){ 
			$this->setKeywords($sharedresource_entry->keywords);
		}

		if (!empty($sharedresource_entry->title)){ 
	        $this->setTitle($sharedresource_entry->title);
	    }

		if (!empty($sharedresource_entry->description)){ 
	        $this->setDescription($sharedresource_entry->description);
	    }

        return true;
    }

	/**
	* function to get any element only with its number of node
	*/
	function getElement($id){
		$element = new StdClass;
		$element->id = $id;
		$element->name = $this->METADATATREE[$id]['name'];
		$element->type = $this->METADATATREE[$id]['widget'];
		return $element;
	}

	/**
	* keyword have a special status in metadata form, so a function to find the keyword field is necessary
	*/
    function getKeywordElement(){
		$element = new StdClass;
    	$element->name = "1_5";
    	$element->type = "list";
    	return $element;
    }

	/**
	* keyword have a special status in metadata form, so a function to find the keyword field is necessary
	*/
    function getDescriptionElement(){
		$element = new StdClass;
    	$element->name = "1_4";
    	$element->type = "text";
    	return $element;
    }

	/**
	* keyword have a special status in metadata form, so a function to find the keyword values
	*/
    function getKeywordValues($metadata){
    	$keyelm = $this->getKeywordElement();
     	$keykeys = preg_grep("/{$keyelm->name}:.*/", array_keys($metadata));
     	$kwlist = array();
     	foreach($keykeys as $k){
     		$kwlist[] = $metadata[$k]->value;
     	}
    	return implode(', ', $kwlist);
    }

	/**
	* title have a special status in metadata form, so a function to find the keyword field is necessary
	*/
	function getTitleElement(){
		$element = new StdClass;
    	$element->name = "1_2";
    	$element->type = "text";
    	return $element;
    }

	/**
	* location have a special status in metadata form, so a function to find the location field is necessary
	*/
	function getLocationElement(){
		$element = new StdClass;
    	$element->name = "4_3";
    	$element->type = "text";
    	return $element;
    }

	/**
	* Allow to get the taxumpath category and other information about its children node.
	*/
	function getTaxumpath(){
		$element = array();
		$element['main']="Taxon Path";
    	$element['source'] = "9_2_1";
		$element['id'] = "9_2_2_1";
		$element['entry'] = "9_2_2_2";
    	return $element;
    }

	function getClassification(){
		$element = "9";
    	return $element;
    }

	/**
	* records keywords in metadata flat table
	*/
    function setKeywords($keywords){
    	global $DB;
    	if (empty($this->entryid)) return; // do not affect metadata to unkown entries
    	$DB->delete_records_select('sharedresource_metadata', " namespace = 'suplomfr' AND element LIKE '1_5:0_%' AND entry_id = ? ", array($this->entryid));
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

	/**
	* records title in metadata flat table from db attributes
	*/
    function setTitle($title){
		global $DB;
    	if ($this->entryid == 0) return;
		$DB->delete_records('sharedresource_metadata', array('entry_id' => $this->entryid, 'namespace' => 'suplomfr', 'element' => '1_2:0_0'));
		$mtdrec = new StdClass;
		$mtdrec->entry_id = $this->entryid;
		$mtdrec->element = '1_2:0_0';
		$mtdrec->namespace = 'lomfr';
		$mtdrec->value = $title;

		return $DB->insert_record('sharedresource_metadata', $mtdrec);
    }

	/**
	* records title in metadata flat table from db attributes
	*/
    function setDescription($description){
		global $DB;
    	if ($this->entryid == 0) return;

		$DB->delete_records('sharedresource_metadata', array('entry_id' => $this->entryid, 'namespace' => 'suplomfr', 'element' => '1_4:0_0'));

		$mtdrec = new StdClass;
		$mtdrec->entry_id = $this->entryid;
		$mtdrec->element = '1_4:0_0';
		$mtdrec->namespace = 'lomfr';
		$mtdrec->value = $description;

		return $DB->insert_record('sharedresource_metadata', $mtdrec);
    }
}
