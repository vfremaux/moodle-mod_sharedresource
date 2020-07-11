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
 * @author  Valery Fremaux valery.fremaux@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 * @subpackage sharedresource_lom
 */

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shared Meta Data Lom does not directly store any personal data about any user.';

$string['pluginname'] = 'Learning Object Model (LOM)';
$string['accessibilityrestrictions'] = 'Accessibility restrictions';
$string['aggregationlevel'] = 'Aggregation level';
$string['annotation'] = 'Annotation';
$string['browserenvironment'] = 'Browser Environment';
$string['catalog'] = 'Catalog';
$string['classification'] = 'Classification';
$string['classificationtaxonpath'] = 'Classification taxon path';
$string['context'] = 'Context';
$string['contribute'] = 'Contribute';
$string['contributerole'] = 'Contribute Role';
$string['contribution'] = 'Contribution';
$string['contributor'] = 'Contributor';
$string['copyright'] = 'Copyright';
$string['copyrightandotherrestrictions'] = 'Copyright and other restrictions';
$string['cost'] = 'Cost';
$string['coverage'] = 'Coverage';
$string['date'] = 'Date';
$string['difficulty'] = 'Difficulty';
$string['discipline'] = 'Discipline';
$string['duration'] = 'Duration';
$string['educational'] = 'Educational';
$string['enduserrole'] = 'End User Role';
$string['entity'] = 'Entity';
$string['entry'] = 'Entry';
$string['environmenttype'] = 'Environment Type';
$string['format'] = 'Format';
$string['general'] = 'General';
$string['id'] = 'Id';
$string['identifier'] = 'Identifier';
$string['installationremarks'] = 'Installation remarks';
$string['intendedenduserrole'] = 'Intended end user role';
$string['interactivitylevel'] = 'Level of interactivity';
$string['interactivitytype'] = 'Interactivity Type';
$string['issuedate'] = 'Issue date';
$string['keyword'] = 'Keyword';
$string['kind'] = 'Kind';
$string['learningresourcetype'] = 'Learning resource type';
$string['lifecycle'] = 'Life Cycle';
$string['lomformat'] = 'Learning Object Model (LOM)';
$string['maximumversion'] = 'Maximum version';
$string['meta-metadata'] = 'Meta-metadata';
$string['metadataschema'] = 'Metadata schema';
$string['minimumversion'] = 'Minimum version';
$string['orcomposite'] = 'OrComposite';
$string['osenvironment'] = 'OS Environment';
$string['otherplatformrequirements'] = 'Other platform requirements';
$string['purpose'] = 'Purpose';
$string['relation'] = 'Relation';
$string['requirement'] = 'Requirement';
$string['resource'] = 'Resource';
$string['rights'] = 'Rights';
$string['rightsdescription'] = 'Rights description';
$string['role'] = 'Role';
$string['semanticdensity'] = 'Semantic Density';
$string['size'] = 'Size';
$string['source'] = 'Source';
$string['status'] = 'Status';
$string['structure'] = 'Structure';
$string['taxonpath'] = 'Taxon Path';
$string['title'] = 'Title';
$string['language'] = 'Language';
$string['description'] = 'Description';
$string['location'] = 'Location';
$string['name'] = 'Name';
$string['metametadata'] = 'Meta-metadata';
$string['taxum'] = 'Taxum';
$string['technical'] = 'Technical';
$string['type'] = 'Type';
$string['typicalagerange'] = 'Typical age range';
$string['typicallearningtime'] = 'Typical learning time';
$string['version'] = 'Version';

$string['standarddescription'] = 'Learning Object Metadata is a data model, usually encoded in XML, used to describe a learning
object and similar digital resources used to support learning. The purpose of learning object metadata is to support the reusability
of learning objects, to aid discoverability, and to facilitate their interoperability, usually in the context of online learning
management systems (LMS). <br/><br/>

The IEEE 1484.12.1 - 2002 Standard for Learning Object Metadata is an internationally-recognised open standard (published by the
Institute of Electrical and Electronics Engineers Standards Association, New York) for the description of "learning objects".
Relevant attributes of learning objects to be described include: type of object; author; owner; terms of distribution; format;
and pedagogical attributes, such as teaching or interaction style. <br/><br>
<center>More informations here : <a href="http://en.wikipedia.org/wiki/Learning_Object_Metadata">Wikipedia page of LOM</a></center>';

// Aggregation level.
$string['1'] = '1';
$string['2'] = '2';
$string['3'] = '3';
$string['4'] = '4';

// Difficulty
$string['veryeasy'] = 'Very easy';
$string['easy'] = 'Easy';
$string['medium'] = 'Medium';
$string['difficult'] = 'Difficult';
$string['verydifficult'] = 'Very difficult';

// Level
$string['verylow'] = 'Very low';
$string['low'] = 'Low';
$string['medium'] = 'Medium';
$string['high'] = 'High';
$string['veryhigh'] = 'Very high';

// Context
$string['school'] = 'School';
$string['highereducation'] = 'Higher education';
$string['training'] = 'Training';
$string['other'] = 'Other';

// Structure
$string['atomic'] = 'Atomic';
$string['collection'] = 'Collection';
$string['networked'] = 'Networked';
$string['hierarchical'] = 'Hierarchical';
$string['linear'] = 'Linear';

// Status
$string['draft'] = 'Draft';
$string['final'] = 'Final';
$string['revised'] = 'Revised';
$string['unavailable'] = 'Unavailable';

// Role
$string['author'] = 'Author';
$string['publisher'] = 'Publisher';
$string['unknown'] = 'Unknown';
$string['initiator'] = 'Initiator';
$string['terminator'] = 'Terminator';
$string['validator'] = 'Validator';
$string['editor'] = 'Editor';
$string['graphicaldesigner'] = 'Graphical designer';
$string['technicalimplementer'] = 'Technical implementer';
$string['contentprovider'] = 'Content provider';
$string['technicalvalidator'] = 'Technical validator';
$string['educationalvalidator'] = 'Educational validator';
$string['scriptwriter'] = 'Script writer';
$string['instructionaldesigner'] = 'Designer instructional';
$string['subjectmatterexpert'] = 'Subject matter expert';

// Contribute role
$string['creator'] = 'Creator';
$string['validator'] = 'Validator';

// Environment type
$string['operatingsystem'] = 'Operating system';
$string['browser'] = 'Browser';

// OSEnvironment
$string['pcdos'] = 'PC DOS';
$string['mswindows'] = 'Microsoft Windows';
$string['macos'] = 'Mac OS';
$string['unix'] = 'Unix';
$string['multios'] = 'Multi-OS';
$string['none'] = 'None';

// BrowserEnvironment
$string['any'] = 'Any';
$string['netscapecommunicator'] = 'Netscape Communicator';
$string['msinternetexplorer'] = 'Internet Explorer';
$string['opera'] = 'Opera';
$string['amaya'] = 'Amaya';
$string['firefox'] = 'Firefox';
$string['googlechrome'] = 'Google Chrome';

// Type d'interactivité
$string['active'] = 'Active';
$string['expositive'] = 'Expositive';
$string['mixed'] = 'Mixed';

// LearningResourceType
$string['exercise'] = 'Exercice';
$string['simulation'] = 'Simulation';
$string['questionnaire'] = 'Questionnaire';
$string['diagram'] = 'Diagram';
$string['figure'] = 'Figure';
$string['graph'] = 'Graph';
$string['index'] = 'Index';
$string['slide'] = 'Slide';
$string['table'] = 'Table';
$string['narrativetext'] = 'Narrative text';
$string['exam'] = 'Exam';
$string['experiment'] = 'Experiment';
$string['problemstatement'] = 'Problem statement';
$string['selfassessment'] = 'Self assessment';
$string['lecture'] = 'Lecture';


// End User Role
$string['teacher'] = 'Teacher';
$string['author'] = 'Author';
$string['learner'] = 'Learner';
$string['manager'] = 'Manager';

// yes,no
$string['yes'] = 'Yes';
$string['no'] = 'No';

// Purpose
$string['discipline'] = 'Discipline';
$string['idea'] = 'Idea';
$string['prerequisite'] = 'Prerequisite';
$string['educationalobjective'] = 'Educational objective';
$string['accessibility'] = 'Accessibility';
$string['restrictions'] = 'Restrictions';
$string['educationallevel'] = 'Educational level';
$string['skilllevel'] = 'Skill level';
$string['securitylevel'] = 'Security level';
$string['competency'] = 'Competency';

// Kind 
$string['ispartof'] = 'is part of';
$string['haspart'] = 'has part';
$string['isversionof'] = 'is version of';
$string['hasversion'] = 'has version';
$string['isformatof'] = 'is format of';
$string['hasformat'] = 'has format';
$string['references'] = 'references';
$string['isreferencedby'] = 'is referenced by';
$string['isbasedon'] = 'is based on';
$string['isbasisfor'] = 'is basis for';
$string['requires'] = 'requires';
$string['isrequiredby'] = 'is required by';
