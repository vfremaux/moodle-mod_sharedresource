<?php

/**
* This file maps several Moodle typical definitions (modules) to Metadata concepts (LOM based)
*
*
*
*
*/

// this is used for a build_vcard utility function
require_once $CFG->dirroot.'/local/sharedresources/classes/file_importer_base.php';

// MODRESORUCETYPE addesses LOM 5_2 node (Learning Resource Type)
global $MODRESOURCETYPES;

$MODRESOURCETYPES = array(
<<<<<<< HEAD
	'advmindmap' => 'graph',
	'assign' => 'exam',
	'assignment' => 'exam',
	'book' => 'lecture',
	'certificate' => 'exam',
	'checklist' => 'self assessment',
	'choice' => 'questionnaire',
	'customlabel' => 'narrative text',
	'data' => 'table',
	'etherpad' => 'narrative text',
	'feedback' => 'questionnaire',
	'flashcard' => 'self assessment',
	'questionnaire' => 'questionnaire',
	'folder' => 'lecture',
	'glossary' => 'lecture',
	'label' => 'narrative text',
	'lesson' => 'lecture',
	'magtest' => 'self assessment',
	'mindmap' => 'graph',
	'page' => 'narrative text,lecture',
	'quiz' => 'exercice',
	'resource' => 'narrative text,lecture',
	'scorm' => 'lecture',
	'sharedresource' => 'narrative text,lecture',
	'survey' => 'questionnaire',
	'techproject' => 'experimentation',
	'tracker' => 'exercice',
	'wiki' => 'narrative text,exercice',
	'workshop' => 'exercice,experimentation' );

// allow completing from configuration file
if (isset($CFG->additionalmodresourcetypes) && is_array($CFG->additionalmodresourcetypes)){
	$MODRESOURCETYPES = $MODRESOURCETYPES + $CFG->additionalmodresourcetypes;
}

// MODRESORUCETYPE addesses LOM 5_3 node (Learning Resource Type)
global $MODINTERACTIVITYLEVELS;

$MODINTERACTIVITYLEVELS = array(
	'advmindmap' => 'very high',
	'assign' => 'medium',
	'assignment' => 'medium',
	'book' => 'very low',
	'certificate' => 'low',
	'checklist' => 'very high',
	'choice' => 'medium',
	'customlabel' => 'very low',
	'data' => 'medium',
	'etherpad' => 'very high',
	'feedback' => 'high',
	'flashcard' => 'high',
	'questionnaire' => 'high',
	'folder' => 'medium',
	'glossary' => 'medium',
	'label' => 'very low',
	'lesson' => 'medium',
	'magtest' => 'medium',
	'mindmap' => 'very high',
	'page' => 'low',
	'quiz' => 'high',
	'resource' => 'low',
	'scorm' => 'medium',
	'sharedresource' => 'low',
	'survey' => 'medium',
	'techproject' => 'very high',
	'tracker' => 'very high',
	'wiki' => 'very high',
	'workshop' => 'very high' );

// allow completing from configuration file
if (isset($CFG->additionalmodinteractivitylevel) && is_array($CFG->additionalmodinteractivitylevel)){
	$MODINTERACTIVITYLEVELS = $MODINTERACTIVITYLEVELS + $CFG->additionalmodinteractivitylevel;
=======
    'advmindmap' => 'graph',
    'assign' => 'exam',
    'assignment' => 'exam',
    'book' => 'lecture',
    'certificate' => 'exam',
    'checklist' => 'self assessment',
    'choice' => 'questionnaire',
    'customlabel' => 'narrative text',
    'data' => 'table',
    'etherpad' => 'narrative text',
    'feedback' => 'questionnaire',
    'flashcard' => 'self assessment',
    'questionnaire' => 'questionnaire',
    'folder' => 'lecture',
    'glossary' => 'lecture',
    'label' => 'narrative text',
    'lesson' => 'lecture',
    'magtest' => 'self assessment',
    'mindmap' => 'graph',
    'page' => 'narrative text,lecture',
    'quiz' => 'exercice',
    'resource' => 'narrative text,lecture',
    'scorm' => 'lecture',
    'sharedresource' => 'narrative text,lecture',
    'survey' => 'questionnaire',
    'techproject' => 'experimentation',
    'tracker' => 'exercice',
    'wiki' => 'narrative text,exercice',
    'workshop' => 'exercice,experimentation' );

// allow completing from configuration file
if (isset($CFG->additionalmodresourcetypes) && is_array($CFG->additionalmodresourcetypes)){
    $MODRESOURCETYPES = $MODRESOURCETYPES + $CFG->additionalmodresourcetypes;
}

// MODRESOURCETYPE addesses LOM 5_3 node (Learning Resource Type)
global $MODINTERACTIVITYLEVELS;

$MODINTERACTIVITYLEVELS = array(
    'advmindmap' => 'very high',
    'assign' => 'medium',
    'assignment' => 'medium',
    'book' => 'very low',
    'certificate' => 'low',
    'checklist' => 'very high',
    'choice' => 'medium',
    'customlabel' => 'very low',
    'data' => 'medium',
    'etherpad' => 'very high',
    'feedback' => 'high',
    'flashcard' => 'high',
    'questionnaire' => 'high',
    'folder' => 'medium',
    'glossary' => 'medium',
    'label' => 'very low',
    'lesson' => 'medium',
    'magtest' => 'medium',
    'mindmap' => 'very high',
    'page' => 'low',
    'quiz' => 'high',
    'resource' => 'low',
    'scorm' => 'medium',
    'sharedresource' => 'low',
    'survey' => 'medium',
    'techproject' => 'very high',
    'tracker' => 'very high',
    'wiki' => 'very high',
    'workshop' => 'very high' );

// allow completing from configuration file
if (isset($CFG->additionalmodinteractivitylevel) && is_array($CFG->additionalmodinteractivitylevel)){
    $MODINTERACTIVITYLEVELS = $MODINTERACTIVITYLEVELS + $CFG->additionalmodinteractivitylevel;
>>>>>>> MOODLE_32_STABLE
}

// MODRESORUCETYPE addesses LOM 5_4 node (Learning Resource Type)
global $MODSEMANTICDENSITIES;

$MODSEMANTICDENSITIES = array(
<<<<<<< HEAD
	'advmindmap' => 'high',
	'assign' => 'low',
	'assignment' => 'low',
	'book' => 'very high',
	'certificate' => 'low',
	'checklist' => 'low',
	'choice' => 'low',
	'customlabel' => 'low',
	'data' => 'medium',
	'etherpad' => 'medium',
	'feedback' => 'low',
	'flashcard' => 'low',
	'questionnaire' => 'low',
	'folder' => 'high',
	'glossary' => 'high',
	'label' => 'very low',
	'lesson' => 'medium',
	'magtest' => 'medium',
	'mindmap' => 'high',
	'page' => 'medium',
	'quiz' => 'low',
	'resource' => 'high',
	'scorm' => 'high',
	'sharedresource' => 'high',
	'survey' => 'low',
	'techproject' => 'very high',
	'tracker' => 'high',
	'wiki' => 'high',
	'workshop' => 'high' );

// allow completing from configuration file
if (isset($CFG->additionalmodsemanticdensity) && is_array($CFG->additionalmodsemanticdensity)){
	$MODSEMANTICDENSITIES = $MODSEMANTICDENSITIES + $CFG->additionalmodsemanticdensity;
=======
    'advmindmap' => 'high',
    'assign' => 'low',
    'assignment' => 'low',
    'book' => 'very high',
    'certificate' => 'low',
    'checklist' => 'low',
    'choice' => 'low',
    'customlabel' => 'low',
    'data' => 'medium',
    'etherpad' => 'medium',
    'feedback' => 'low',
    'flashcard' => 'low',
    'questionnaire' => 'low',
    'folder' => 'high',
    'glossary' => 'high',
    'label' => 'very low',
    'lesson' => 'medium',
    'magtest' => 'medium',
    'mindmap' => 'high',
    'page' => 'medium',
    'quiz' => 'low',
    'resource' => 'high',
    'scorm' => 'high',
    'sharedresource' => 'high',
    'survey' => 'low',
    'techproject' => 'very high',
    'tracker' => 'high',
    'wiki' => 'high',
    'workshop' => 'high' );

// allow completing from configuration file
if (isset($CFG->additionalmodsemanticdensity) && is_array($CFG->additionalmodsemanticdensity)){
    $MODSEMANTICDENSITIES = $MODSEMANTICDENSITIES + $CFG->additionalmodsemanticdensity;
>>>>>>> MOODLE_32_STABLE
}

// MODRESOURCETYPE addesses LOMFR 5_12 node (Learning Activity Type)
global $MODLEARNINGACTIVITIES;

// 'animer', 'apprendre', 'collaborer', 'communiquer', 'coopérer', 'créer', 'échanger', 'expérimenter', 
// 'lire', 'observer', 'organiser', 'produire', 'publier', 'rechercher', 's\'auto-former', 's\'exercer', 
// 's\'informer', 'se former', 'simuler', 's\'évaluer'

$MODLEARNINGACTIVITIES = array(
<<<<<<< HEAD
	'advmindmap' => 'collaborer,créer,organiser',
	'assign' => 's\'évaluer',
	'assignment' => 's\'évaluer',
	'book' => 'lire,apprendre',
	'certificate' => 's\'évaluer',
	'checklist' => 'organiser,s\'informer',
	'choice' => 'animer,échanger',
	'customlabel' => 'lire,s\'informer',
	'data' => 'organiser,publier,collaborer',
	'etherpad' => 'collaborer,publier,échanger,collaborer',
	'feedback' => 'communiquer',
	'flashcard' => 'apprendre',
	'questionnaire' => 'communiquer',
	'glossary' => 'rechercher,collaborer,coopérer,publier',
	'label' => 'lire,s\'informer',
	'lesson' => 'apprendre',
	'magtest' => 's\'évaluer,s\auto-former,se former',
	'mindmap' => 'créer,organiser',
	'page' => 'lire,se former',
	'quiz' => 's\'évaluer,apprendre,s\'auto-former,s\'exercer',
	'resource' => 'lire,apprendre',
	'scorm' => 'se former,lire,s\'exercer',
	'sharedresource' => 'lire,apprendre',
	'survey' => 'communiquer',
	'techproject' => 'expérimenter,organiser,produire',
	'tracker' => 'produire,organiser',
	'wiki' => 'coopérer,collaborer,publier',
	'workshop' => 'apprendre,coopérer,expérimenter' );
=======
    'advmindmap' => 'collaborer,créer,organiser',
    'assign' => 's\'évaluer',
    'assignment' => 's\'évaluer',
    'book' => 'lire,apprendre',
    'certificate' => 's\'évaluer',
    'checklist' => 'organiser,s\'informer',
    'choice' => 'animer,échanger',
    'customlabel' => 'lire,s\'informer',
    'data' => 'organiser,publier,collaborer',
    'etherpad' => 'collaborer,publier,échanger,collaborer',
    'feedback' => 'communiquer',
    'flashcard' => 'apprendre',
    'questionnaire' => 'communiquer',
    'glossary' => 'rechercher,collaborer,coopérer,publier',
    'label' => 'lire,s\'informer',
    'lesson' => 'apprendre',
    'magtest' => 's\'évaluer,s\auto-former,se former',
    'mindmap' => 'créer,organiser',
    'page' => 'lire,se former',
    'quiz' => 's\'évaluer,apprendre,s\'auto-former,s\'exercer',
    'resource' => 'lire,apprendre',
    'scorm' => 'se former,lire,s\'exercer',
    'sharedresource' => 'lire,apprendre',
    'survey' => 'communiquer',
    'techproject' => 'expérimenter,organiser,produire',
    'tracker' => 'produire,organiser',
    'wiki' => 'coopérer,collaborer,publier',
    'workshop' => 'apprendre,coopérer,expérimenter' );
>>>>>>> MOODLE_32_STABLE

// MODDOCUMENTTYPE addesses LOMFR 1_9 node (Type de documents)
global $MODDOCUMENTTYPES;

// 'collection', 'ensemble de données', 'événement', 'image', 'image en mouvement', 'image fixe', 
// 'logiciel', 'objet physique', 'ressource interactive', 'service', 'son', 'texte'

$MODDOCUMENTTYPES = array(
<<<<<<< HEAD
	'advmindmap' => 'ressource interactive',
	'assign' => '',
	'assignment' => '',
	'book' => 'ensemble de données',
	'certificate' => 'texte',
	'checklist' => 'service',
	'choice' => 'service',
	'customlabel' => 'texte',
	'data' => 'ensemble de données',
	'etherpad' => 'texte',
	'feedback' => 'service',
	'flashcard' => 'son,texte,image fixe,image en mouvement',
	'questionnaire' => 'service',
	'glossary' => 'ensemble de données',
	'label' => 'texte',
	'folder' => 'collection',
	'lesson' => 'collection',
	'lightboxgallery' => 'image fixe',
	'magtest' => 'service',
	'mindmap' => 'ressource interactive',
	'page' => 'texte',
	'quiz' => 'service',
	'resource' => 'texte',
	'richmedia' => 'image en mouvement',
	'referentiel' => 'ensemble de données',
	'scorm' => 'ressource interactive',
	'sharedresource' => 'texte',
	'survey' => 'service',
	'techproject' => 'ensemble de données',
	'tracker' => 'service',
	'wiki' => 'texte',
	'workshop' => 'collection' );
=======
    'advmindmap' => 'ressource interactive',
    'assign' => '',
    'assignment' => '',
    'book' => 'ensemble de données',
    'certificate' => 'texte',
    'checklist' => 'service',
    'choice' => 'service',
    'customlabel' => 'texte',
    'data' => 'ensemble de données',
    'etherpad' => 'texte',
    'feedback' => 'service',
    'flashcard' => 'son,texte,image fixe,image en mouvement',
    'questionnaire' => 'service',
    'glossary' => 'ensemble de données',
    'label' => 'texte',
    'folder' => 'collection',
    'lesson' => 'collection',
    'lightboxgallery' => 'image fixe',
    'magtest' => 'service',
    'mindmap' => 'ressource interactive',
    'page' => 'texte',
    'quiz' => 'service',
    'resource' => 'texte',
    'richmedia' => 'image en mouvement',
    'referentiel' => 'ensemble de données',
    'scorm' => 'ressource interactive',
    'sharedresource' => 'texte',
    'survey' => 'service',
    'techproject' => 'ensemble de données',
    'tracker' => 'service',
    'wiki' => 'texte',
    'workshop' => 'collection' );
>>>>>>> MOODLE_32_STABLE

// MODGENERALDOCUMENTTYPE addesses ScoLOMFR 1_10 node (Type general de documents)
global $MODGENERALDOCUMENTTYPES;

// 'annuaire', 'archives', 'article', 'atlas', 'bande dessinée', 'banque de vidéos', 
// 'banque d\'images', 'base de données', 'bibliographie/sitographie', 'biographie', 
// 'carte', 'carte heuristique et conceptuelle', 'chronologie', 'collection de documents',
// 'compte rendu', 'conférence', 'diaporama', 'dossier documentaire', 'dossier technique',
// 'exposition', 'feuille de calcul', 'film', 'image numérique', 'livre numérique',
// 'maquette/prototype', 'norme', 'jeu de données', 'objet physique', 'objet 3D',
// 'ouvrage', 'partition musicale', 'périodique', 'photographie', 'podcast',
// 'présentation multimédia', 'programme scolaire', 'rapport', 'référentiel de compétences',
// 'schéma/graphique', 'site web', 'tableau (art)', 'web média'

$MODGENERALDOCUMENTTYPES = array(
<<<<<<< HEAD
	'advmindmap' => 'carte heuristique et conceptuelle',
	'assign' => '',
	'assignment' => '',
	'book' => 'livre numerique',
	'certificate' => '',
	'checklist' => 'rapport',
	'choice' => '',
	'customlabel' => '',
	'data' => 'base de données',
	'etherpad' => 'article',
	'feedback' => '',
	'flashcard' => '',
	'questionnaire' => '',
	'glossary' => 'base de données',
	'label' => '',
	'folder' => 'dossier documentaire',
	'lesson' => '',
	'lightboxgallery' => 'banque d\'images',
	'magtest' => '',
	'mindmap' => 'carte heuristique et conceptuelle',
	'page' => 'article',
	'quiz' => '',
	'resource' => '',
	'richmedia' => 'présentation multimedia',
	'referentiel' => 'référentiel de compétences',
	'scorm' => 'présentation multimédia',
	'sharedresource' => '',
	'survey' => '',
	'techproject' => 'dossier technique',
	'tracker' => '',
	'wiki' => 'site web',
	'workshop' => '' );

// allow completing from configuration file
if (isset($CFG->additionalmodgeneraldocumenttype) && is_array($CFG->additionalmodgeneraldocumenttype)){
	$MODGENERALDOCUMENTTYPES = $MODGENERALDOCUMENTTYPES + $CFG->additionalmodgeneraldocumenttype;
}

function sharedresource_append_metadata_elements(&$elements, $name, $value, $plugin){

	$values = explode(',', $value);
	
	// extracts parts from indexed node name to generate several instances
	if (!preg_match('/^(.*_)(\d+)$/', $name, $matches)){
		return;
	}
	
	$radical = $matches[1];
	$index = $matches[2];
	
	foreach($values as $v){
		$elements[$radical.$index] = (object) array('name' => $radical.$index, 'value' => $v, 'plugin' => $plugin);
		$index++;
	}
=======
    'advmindmap' => 'carte heuristique et conceptuelle',
    'assign' => '',
    'assignment' => '',
    'book' => 'livre numerique',
    'certificate' => '',
    'checklist' => 'rapport',
    'choice' => '',
    'customlabel' => '',
    'data' => 'base de données',
    'etherpad' => 'article',
    'feedback' => '',
    'flashcard' => '',
    'questionnaire' => '',
    'glossary' => 'base de données',
    'label' => '',
    'folder' => 'dossier documentaire',
    'lesson' => '',
    'lightboxgallery' => 'banque d\'images',
    'magtest' => '',
    'mindmap' => 'carte heuristique et conceptuelle',
    'page' => 'article',
    'quiz' => '',
    'resource' => '',
    'richmedia' => 'présentation multimedia',
    'referentiel' => 'référentiel de compétences',
    'scorm' => 'présentation multimédia',
    'sharedresource' => '',
    'survey' => '',
    'techproject' => 'dossier technique',
    'tracker' => '',
    'wiki' => 'site web',
    'workshop' => '');

// allow completing from configuration file
if (isset($CFG->additionalmodgeneraldocumenttype) && is_array($CFG->additionalmodgeneraldocumenttype)){
    $MODGENERALDOCUMENTTYPES = $MODGENERALDOCUMENTTYPES + $CFG->additionalmodgeneraldocumenttype;
}

function sharedresource_append_metadata_elements(&$elements, $name, $value, $plugin) {

    $values = explode(',', $value);
    
    // extracts parts from indexed node name to generate several instances
    if (!preg_match('/^(.*_)(\d+)$/', $name, $matches)) {
        return;
    }
    
    $radical = $matches[1];
    $index = $matches[2];
    
    foreach($values as $v){
        $elements[$radical.$index] = (object) array('name' => $radical.$index, 'value' => $v, 'plugin' => $plugin);
        $index++;
    }
>>>>>>> MOODLE_32_STABLE
}

/**
* This fuction searches for editing teachers and add them all as co-authors. This may not be true in reality, 
* but there is no real mean to know who really did create the activity. We can just guess that in most cases,
* the course has one author/teacher 
*/
<<<<<<< HEAD
function sharedresource_append_author_data(&$backupmetadataelements, $courseid = 0, $authoringdate = -1){
	global $COURSE;
	
	if (!$courseid) $courseid = $COURSE->id;
	if ($authoringdate == -1) $authoringdate = time();
	
	$coursecontext = context_course::instance($courseid);
	$editingteachers = get_users_by_capability($coursecontext, 'moodle/course:anageactivities', 'u.id, u.lastname, u.firstname, u.email, u.institution');

	if (!empty($editingteachers)){
		$i = 0;
		foreach($editingteachers as $et){
			
			$vcard = file_importer_base::build_vcard($et);

		    sharedresource_append_metadata_elements($backupmetadataelements, "2_3_1:0_{$i}_0", 'author', $CFG->pluginchoice);
		    sharedresource_append_metadata_elements($backupmetadataelements, "2_3_2:0_{$i}_0", $vcard, $CFG->pluginchoice);
		    sharedresource_append_metadata_elements($backupmetadataelements, "2_3_3:0_{$i}_0", date('Y-m-d\Th:i:s\Z', $authoringdate), $CFG->pluginchoice);
		}
	}
=======
function sharedresource_append_author_data(&$backupmetadataelements, $courseid = 0, $authoringdate = -1) {
    global $COURSE;

    if (!$courseid) {
        $courseid = $COURSE->id;
    }
    if ($authoringdate == -1) {
        $authoringdate = time();
    }

    $coursecontext = context_course::instance($courseid);
    $editingteachers = get_users_by_capability($coursecontext, 'moodle/course:anageactivities', 'u.id, u.lastname, u.firstname, u.email, u.institution');

    if (!empty($editingteachers)) {
        $i = 0;
        foreach ($editingteachers as $et) {

            $vcard = file_importer_base::build_vcard($et);

            sharedresource_append_metadata_elements($backupmetadataelements, "2_3_1:0_{$i}_0", 'author', $CFG->pluginchoice);
            sharedresource_append_metadata_elements($backupmetadataelements, "2_3_2:0_{$i}_0", $vcard, $CFG->pluginchoice);
            sharedresource_append_metadata_elements($backupmetadataelements, "2_3_3:0_{$i}_0", date('Y-m-d\Th:i:s\Z', $authoringdate), $CFG->pluginchoice);
        }
    }
>>>>>>> MOODLE_32_STABLE
}