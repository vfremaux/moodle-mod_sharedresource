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
 * @author  Piers Harding  piers@catalyst.net.nz
 * @author  Valery Fremaux  valery.fremaux@gmail.com
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package sharedresource
 */
global $SITE;

$string['privacy:metadata'] = "The Sharedresources module do not manipulate any data belonging to users";

// Capabilities.
$string['sharedresource:addinstance'] = 'Can add an instance';
$string['sharedresource:manageblocks'] = 'Manage blocks in the library';
$string['sharedresource:manageclassifications'] = 'Manage classifications';
$string['sharedresource:manageclassificationtokens'] = 'Manage classification tokens';

$string['accessuserfield'] = 'Access user field';
$string['accessuserfieldvalues'] = 'Acceptance values';
$string['add'] = 'Add';
$string['addclassification'] = 'Add a classification';
$string['addclassification_help'] = 'A classification allows a taxonomy instance tio be tagged to a token official tree';
$string['addclassificationtitle'] = 'Add classification';
$string['addclassificationvalue'] = 'Add a classification value';
$string['adddeploy'] = 'Deploying a stored activity';
$string['addfile'] = 'Add to the course files';
$string['addfiletocourse'] = 'Add to course files';
$string['addheader'] = 'Adding A New Resource';
$string['addinstance'] = 'Add a shared resource';
$string['addlocal'] = 'Relocalize a distant resource';
$string['addltiinstall'] = 'Install an LTI Tool from the library';
$string['addmetadataform'] = 'Add Metadata Form';
$string['addremote'] = 'Add a remote resource';
$string['addshared'] = 'Add a shared resource';
$string['addsharedresource'] = 'Add shared resource';
$string['addsharedresourcetypefile'] = 'Add a shared resource';
$string['addtocourse'] = 'Add the resource to the course';
$string['addtoken'] = 'Add a classification entry';
$string['aggregation_level'] = 'Aggregation Level';
$string['all'] = 'All source';
$string['allowmultipleaccessvalues'] = 'Allow multiple selection';
$string['appliedsqlrestrict'] = 'Applied clause : ';
$string['configarticlequantity'] = 'Number of items';
$string['attributes'] = 'Attributes provided in the form and stored : ';
$string['author'] = 'Author';
$string['backadminpage'] = 'Back to the settings page';
$string['backclassifpage'] = 'Back to the classifications configuration page';
$string['backtocourse'] = 'Back to the course';
$string['badcourseid'] = 'Incorrect course id';
$string['badsqlrestrict'] = 'Statement should be the content of a SQL WHERE clause';
$string['bycapability'] = 'By capability';
$string['byprofilefield'] = 'By profilefield';
$string['byprofilefield'] = 'By profilefield';
$string['cancelform'] = 'Cancel';
$string['cannotrestore'] = 'corresponding sharedresource_entry is missing - restore failed for: {$a}';
$string['choose'] = 'Choose';
$string['chooseparameter'] = 'Choose Parameter';
$string['chooseprovidertopushto'] = 'By sharing the resource to an external provider, you :<ul><li>Move physically the resource</li><li>Delete the local representation for this resource</li><li>Allow other sites using this provider to use the resource</li><li>Will rebind the all locations of use of the resource within the Moodle network.</li></ul></p>';
$string['classification'] = 'Classification:&ensp;';
$string['classificationacls'] = 'Access control';
$string['classificationconfiguration'] = 'Classification configuration';
$string['classificationconfiguration_desc'] = 'This <a href="{$a}">additional page</a> allows the configuration of classifications for the chosen data model.';
$string['classificationname'] = 'Classification usual name';
$string['classifications'] = 'Classification';
$string['classificationsearch'] = 'Research on classification';
$string['classificationupdate'] = 'Classification modification';
$string['clearthumbnail'] = 'Clear';
$string['completeform'] = 'Enter the data in the form below';
$string['configaccesscontrol'] = 'Access control';
$string['configallowlocalfiles'] = 'Allow links to available files on a local storage folder (for instance a CD or a hard disk drive) during the creation of a new file type resource. This can be useful in a classroom where all students have access to a shared network volume or if files on a CD are needed. It is possible that the use of this feature requires a change in the security settings of your browser.';
$string['configallowmultipleaccessvalues'] = 'Multiple values of the control field can be used to validate the access';
$string['configautofilerenamesettings'] = 'Automatically update references to other files and folders during a name change in the management of files.';
$string['configbackupindex'] = 'Backup shared Resource Index';
$string['configblockdeletingfilesettings'] = 'Prevent deleting files and folders which are referenced by resources. Note that images and other files referenced in the HTML code are not protected by this setting.';
$string['configclassification'] = 'Configure';
$string['configdefaulturl'] = 'Default URL';
$string['configdefaultuserfield'] = 'Default value for shared resource access controlled.';
$string['configenablerssfeeds'] = 'Enable RSS resource exposition feeds';
$string['configfilterexternalpages'] = 'Enabling this setting will allow the filtering of external resources (web pages, HTML files deposited) by the filters defined in the site (such as links in glossaries). When this setting is active, the display of your pages will be slowed significantly. Use with caution.';
$string['configforeignurlscheme'] = 'URL to access resources';
$string['configframesize'] = 'Frame size';
$string['configfreezeindex'] = 'Freeze shared Index';
$string['confighidemetadatadesc'] = 'Hide metadata description';
$string['configparametersettings'] = 'Determines if the zone configuration of parameters is displayed or not by default when adding new resources. After the first use, this setting is individual.';
$string['configpluginscontrol'] = 'Metadata Plugins Control';
$string['configpopup'] = 'Window';
$string['configpopupheight'] = 'Default window height (in pixels)';
$string['configpopuplocation'] = 'Show the location bar';
$string['configpopupmenubar'] = 'Show the menu bar';
$string['configpopupresizable'] = 'Allow the window to be resized';
$string['configpopupscrollbars'] = 'Allow the window to be scrolled';
$string['configpopupstatus'] = 'Show the status bar';
$string['configpopuptoolbar'] = 'Show the toolbar';
$string['configpopupwidth'] = 'Default window width (in pixels)';
$string['configrestoreindex'] = 'Restore shared Resource Index';
$string['configschema'] = 'Metadata standard';
$string['configschema_desc'] = 'This choice determine the plugin used in the metadata form';
$string['configsecretphrase'] = 'This secret phrase is used to generate the encrypted code that can be sent as a parameter to some resources. This encrypted code is made by concatenating an MD5 value of the IP address of the current_user and this secret phrase, for example: code = md5(IP.secretphrase). This allows the resource receiving the parameter to check the connection for more security.';
$string['configscormintegration'] = 'Scorm integration mode';
$string['configwebsearch'] = 'URL displayed during the add of a web page or a link, in order to allow the user to search the desired URL.';
$string['configwindowsettings'] = 'Determines if the zone configuration of windows is displayed or not by default when adding new resources. After the first use, this setting is individual.';
$string['confirmclassifdeletion'] = 'Deleting a complete classification may have big impact on resource indexation. Are you sure this is what you want ?';
$string['contentintegration'] = 'Content integration';
$string['conversioncancelled'] = 'conversion cancelled';
$string['conversioncancelledtocourse'] = 'Conversion canceled. You are going to be redirect to the management of activities';
$string['conversioncancelledtolibrary'] = 'Conversion canceled. You are going to be redirect to the library';
$string['convert'] = 'Convert selection';
$string['convert_help'] = 'If sharedresources are used, you can use these links to convert standard resources to sharedlibrary back and forth';
$string['convertall'] = 'Share and index resources';
$string['convertback'] = 'Localize back a resource';
$string['convertingsharedresource'] = 'Converting shared resource {$a->id} : {$a->name}';
$string['copyright_and_other_restrictions'] = 'Copyright and other restrictions';
$string['correctsave'] = '<h2> Resource saved successfully </h2>';
$string['d'] = 'days';
$string['datachanged'] = 'Data updated';
$string['datefmt'] = '%x';
$string['datesearch'] = 'Research on date field';
$string['day'] = '- Day -';
$string['days'] = 'Day(s)';
$string['defaultselect'] = 'Reinitialize to the default selection';
$string['defaultuserfield'] = 'Default user field for access control';
$string['delete'] = 'Delete';
$string['deleteconfirm'] = 'Are you sure you want to delete this classification?';
$string['description'] = 'Description';
$string['directlink'] = 'Direct link to this file';
$string['disabled'] = 'Disabled';
$string['disablednode'] = 'Node {$a} is disabled in this schema application';
$string['discipline'] = 'Discipline';
$string['discouragednode'] = 'Discouraged node';
$string['dmdescription'] = 'Description of the metadata model :';
$string['dmuse'] = 'Use of the metadata model :';
$string['dmused'] = 'Model used';
$string['down'] = 'Down';
$string['durationdescr'] = 'Duration format description';
$string['durationsearch'] = 'Research on duration field';
$string['edit'] = 'Edit';
$string['editclassificationtable'] = 'Edit classification table';
$string['enabled'] = 'Enabled';
$string['entry'] = 'Entry';
$string['erroraclmisconf'] = 'ERROR : Access control error : no taxonomy nor resource id is given.';
$string['erroraddinstance'] = 'ERROR : Sharedresource instance creation error';
$string['errorcmaddition'] = 'ERROR : Could not add the course module';
$string['errorcmsectionbinding'] = 'ERROR : Could not update the course module with the correct section';
$string['errordeletesharedresource'] = 'ERROR : can\'t delete resource file ({$a})';
$string['erroremptytokenvalue'] = 'ERROR : A non blanck token value is expected';
$string['erroremptyurl'] = 'ERROR : Tried to create a Shared Resource without a URL';
$string['errorinvalididentifier'] = 'ERROR : Ressource Identifier {$a} does not match any resource';
$string['errormemberwrongaccess'] = 'ERROR : Accessing a non registered member {$a}';
$string['errormetadata'] = 'Errors found in the following fields (these errors will be printed in red in the form) : ';
$string['errornometadataenabled'] = 'ERROR : Metadata cannot be configured as no plugin is activated as schema';
$string['errornometadataplugins'] = 'ERROR : No Metadata plugins installed';
$string['errornoticecreation'] = 'ERROR : Could not create the remote notice';
$string['errornotinstalled'] = 'ERROR : Sharedresource module not installed !!';
$string['errorscormtypelocalwithnofile'] = 'ERROR : A local scorm type needs a local file.';
$string['errorsectionaddition'] = 'Could not setup a section';
$string['errorupdatecm'] = 'Could not update course module';
$string['existothermetadata'] = 'A metadata form using another data model already exists for this resource.<br/>If this form is validated, a new metadata form will be stored and the old one will be deleted.';
$string['existsignorechanges'] = 'Data exists but not changed';
$string['export'] = 'Export to an external provider';
$string['failadd'] = 'Resource failed to save (add) to the DB';
$string['failupdate'] = 'Resource failed to save (update) to the DB';
$string['fieldname'] = 'Name of the field';
$string['file'] = 'File or link';
$string['fileadvice'] = '<p>The physical representation has been added in local files of the courses. You are going to be redirected to this storage folder.</p>';
$string['filenotfound'] = 'Sorry, the requested file could not be found. Reason: {$a}';
$string['filesharedresource'] = 'Shared resource (file or url)';
$string['fileuploadfailed'] = 'File upload failed';
$string['fillcategory'] = 'All field in this category are empty. Please fill in at least one of these fields.';
$string['fillprevious'] = 'Please fill in the previous field before adding a new one';
$string['filtername'] = 'Filter Name';
$string['forcedownload'] = 'Force Download';
$string['frameifpossible'] = 'Frame if possible';
$string['frameifpossible_help'] = 'Help on Frame if possible';
$string['gometadataform'] = 'Fill in metadata about this shared resource';
$string['gometadataform2'] = 'Fill in metadata about this shared resource';
$string['h'] = 'hours';
$string['hideclassification'] = 'Disable classification';
$string['hours'] = 'Hour(s)';
$string['idname'] = 'Name of the id field';
$string['incorrectdate'] = 'This date is incorrect <br/>';
$string['incorrectday'] = 'The number of days must be superior to 1 <br/>';
$string['incorrecthour'] = 'The number of hours must be superior to 1 <br/>';
$string['incorrectminute'] = 'The number of minutes must be superior to 1 <br/>';
$string['incorrectsecond'] = 'The number of seconds must be superior to 1 <br/>';
$string['incorrectsql'] = 'The SQL SELECT clause is not correct';
$string['indexer'] = 'Librarian';
$string['installation_remarks'] = 'Installation Remarks';
$string['integerday'] = 'The number of days must be an integer <br/>';
$string['integerhour'] = 'The number of hours must be an integer <br/>';
$string['integerminute'] = 'The number of minutes must be an integer <br/>';
$string['intended_end_user_role'] = 'Intended End-user Role';
$string['interactivity_level'] = 'Interactivity Level';
$string['interactivity_type'] = 'Interactivity Type';
$string['keepnavigationvisible'] = 'Keep Navigation Visible';
$string['keyword'] = 'Keyword';
$string['keywordpunct'] = 'No punctuation authorized in keywords <br/>';
$string['keywords'] = 'Keywords';
$string['labelname'] = 'Name of the label field';
$string['language'] = 'Language';
$string['layout'] = 'Layout';
$string['learning_resource_type'] = 'Learning Resourc Type ';
$string['libraryengine'] = 'Resource Library Engine';
$string['license'] = 'License';
$string['life_cycle'] = 'Life Cycle';
$string['local'] = $SITE->shortname.' resources';
$string['localizeadvice'] = '<p>The resource has been relocalize, it means a copy of the original resource is available in the course. This copy is separate from the orginal shared resource. If this resource has a physical representation, the file which represent it is stored in local files of the course.</p>';
$string['localizetocourse'] = 'Localize as a course resource';
$string['location'] = 'Resource location';
$string['m'] = 'mins';
$string['mandatory'] = 'Mandatory';
$string['matchedvalues'] = 'Matching values';
$string['maximum_version'] = 'Maximum Version';
$string['metadata'] = 'Meta data';
$string['metadata_configure'] = 'Metadata Configuration';
$string['metadata_schema'] = 'Metadata Schema';
$string['metadataconfiguration'] = 'Metadata configuration';
$string['metadatadescr'] = 'Metadata description';
$string['metadatanotice'] = 'Metadata Notice';
$string['minimum_version'] = 'Minimum Version';
$string['minutes'] = 'Minute(s)';
$string['missingid'] = 'The name of the id field does not exist in the database table <br/>';
$string['missinglabel'] = 'The name of the label field does not exist in the database table <br/>';
$string['missingnameid'] = 'Please enter an id name in the field "Name of the id field"<br/>';
$string['missingnamelabel'] = 'Please enter a label name in the field "Name of the label field"<br/>';
$string['missingnameparent'] = 'Please enter a parent name in the field "Name of the parent field"<br/>';
$string['missingnametable'] = 'Please enter a name in the field "Table name" <br/>';
$string['missingordering'] = 'The name of the ordering field does not exist in the database table <br/>';
$string['missingparent'] = 'The name of the parent field does not exist in the database table <br/>';
$string['missingresource'] = 'must choose either URL or file';
$string['missingtable'] = 'The table does not exist in the database <br/>';
$string['modulename'] = 'Shared Resource';
$string['modulename_help'] = 'A sharedresource is naturally shared within the whole site or a course category. Sharedresources are stored in a common library with complete indexation information for searching and browsing. Libraries can be exposed to the Moodle network to help sharing learning material in a moodle community.';
$string['modulenameplural'] = 'Shared Resources';
$string['month'] = '- Month -';
$string['mtdfieldid'] = 'Field ID';
$string['mtdfieldname'] = 'Field name';
$string['mtdvalue'] = 'Value';
$string['name'] = 'Name';
$string['configpopupdirectories'] = 'Show the directory links';
$string['newdirectories'] = 'Show the directory links';
$string['newwindow'] = 'New window';
$string['newscrollbars'] = 'Enable scrollbars';
$string['noaccessform'] = 'Your user category do not have access to this form';
$string['noclassification'] = 'No classification found';
$string['node'] = 'Node';
$string['nodescription'] = 'There is no available description for this data model.';
$string['nometadataplugin'] = 'Administrator did not select the applicable metadata schema for resource management.';
$string['none'] = '(no restriction)';
$string['noplugin'] = 'No Plugin';
$string['noprovidertopushto'] = 'Your site is not connected to any Moodle resource provider.';
$string['noresourcesfound'] = 'No resources found';
$string['noresourcestoconvert'] = 'No resource to convert';
$string['nosharedresources'] = 'No shared resources in this course';
$string['notaxonomies'] = 'No active taxonomy available';
$string['notselectable'] = 'Not selectable';
$string['nowidget'] = 'No search widget defined by the admin !';
$string['numericsearch'] = 'Research on numeric field';
$string['onekeyword'] = 'Only one keyword authorized in one text field (no spaces)<br/>';
$string['orderingmin'] = 'Minimum ordering';
$string['orderingname'] = 'Name of the ordering field';
$string['other_platform_requirements'] = 'Other Plateform Requirements';
$string['othersearch'] = 'New search';
$string['pagewindow'] = 'Same window';
$string['pan'] = 'Pan';
$string['parameter'] = 'Parameter';
$string['parameters'] = 'Parameters';
$string['parentname'] = 'Name of the parent field';
$string['pluginadministration'] = 'Plugin Administration';
$string['pluginname'] = 'Shared resource';
$string['predatanotprovided'] = '<- Not provided yet ->';
$string['preview'] = 'Preview';
$string['profilefieldname'] = 'Profile field';
$string['profilefieldplaceholder'] = 'profile_field: or user: prefixed field name';
$string['profilefieldsyntax'] = 'The userfield based rule {$a} does not have the expected syntax';
$string['pushtosingleprovider'] = '<p>Your site only has one provider connected : {$a}.</p><p>By sharing the resource to an external provider, you:<ul><li>Move physically the resource</li><li>Delete the local representation for this resource</li><li>Allow other sites using this provider to use the resource</li><li>Will rebind the all locations of use of the resource within the Moodle network.</li></ul></p>';
$string['readnotice'] = 'Read the notice';
$string['remotesearchquery'] = 'Search in remote resource repositories ';
$string['remotesearchresults'] = 'Search results ';
$string['remotesubmission'] = 'Soumission de ressource';
$string['repository'] = 'Repository';
$string['repositorytoresource'] = 'Repository -> Learning Path';
$string['resource_consumer_description'] = 'By publishing this service, you allow "provider" platforms to check the consumption of their resources on that host.<br/><br/>By subscribing to this service, you can check the consumption of your resources on the remote "consumer" site.<br/><br/>';
$string['resource_consumer_name'] = 'Service of resources consumption';
$string['resource_provider_description'] = 'By publishing this service, you allow remote "consumers" to use the shared resources of your catalogue.<br/><br/>By subscribing to this service, you provide your local catalogue to remote "consumer" platforms.<br/><br/>';
$string['resource_provider_name'] = 'Service of resources providing';
$string['resourceacls'] = 'Access control on resource: {$a}';
$string['resourcebuilt'] = 'New resource built : {$a}';
$string['resourceconversion'] = 'Resource conversion';
$string['resourceexists'] = 'A resource with this signature allready exists';
$string['resourceintheway'] = 'A resource with same signature is already registered in the library.';
$string['resourcenewdescription'] = 'New description';
$string['resourceolddescription'] = 'Previous description';
$string['resources'] = 'Resources';
$string['resourcetorepository'] = 'Learning Path -> Repository';
$string['resourcetypefile'] = 'Resource identification';
$string['resourceupdate'] = 'When confirming you will update the resource description with your new values. Do you confirm ';
$string['restrictclassification'] = 'Restrict a classification';
$string['restrictsql'] = 'Enter a WHERE SQL clause to restrict the classification output: ';
$string['rss'] = 'RSS (in developement)';
$string['s'] = 'secs';
$string['saveselection'] = 'Save the selection';
$string['savesqlrestrict'] = 'Save';
$string['schema'] = 'Schema';
$string['score'] = 'Score';
$string['searchfor'] = 'Search for';
$string['searchheader'] = 'Search criteria';
$string['searchin'] = 'Search in';
$string['searchinlibrary'] = 'Search in Library';
$string['searchinsubs'] = 'Search in sub categories';
$string['searchsharedresource'] = 'Search for A shared resource';
$string['seconds'] = 'Second(s)';
$string['selectable'] = 'Selectable';
$string['selectall'] = 'Select all';
$string['selectclassification'] = 'Selection and configuration of visible classifications';
$string['selectnone'] = 'Select none';
$string['selectsearch'] = 'Research on select field';
$string['selecttaxons'] = 'Select valid taxons';
$string['semantic_density'] = 'Semantic Density';
$string['serverurl'] = 'Server URL';
$string['resourceaskupdate'] = 'Do yo want to reedit this resource?';
$string['sharedresourcedetails'] = 'Sharedresource Details';
$string['sharedresourceintro'] = 'Introduction';
$string['sharedresourcenotice'] = 'Sharedresource Notice for : {$a}';
$string['sharedresourceservice_description'] = 'Allows remote access to providers. Library consumers should subscribe to this service. Library providers should publish this service.';
$string['sharedresourceservice_name'] = 'Sharedresource Module Services';
$string['sharedresourcetypefile'] = 'shared resource';
$string['sharingcontext'] = 'Sharing context';
$string['showclassification'] = 'Enable classification';
$string['somewhere'] = 'Somewhere (site, course or category)';
$string['sqlmapping'] = 'SQL Mapping';
$string['sqloptions'] = 'SQL Options';
$string['sqlrestriction'] = 'SQL Restriction';
$string['step2'] = 'Go to Step2';
$string['successfulmodification'] = 'Successful modification';
$string['system'] = 'Administrator';
$string['systemcontext'] = 'Site wide share';
$string['tablename'] = 'Table name';
$string['taxonchoicetitle'] = 'Selection of visible taxons';
$string['taxonpath'] = 'TAXON Path';
$string['taxons'] = 'Taxons';
$string['taxonselection'] = 'Taxon subset';
$string['textsearch'] = 'Research on text field';
$string['thumbnail'] = 'Thumbnail (35k max)';
$string['title'] = 'Title';
$string['tokenvalue'] = 'Token value';
$string['typical_age_range'] = 'Typical Age Range';
$string['typical_learning_time'] = 'Typical Learning Time';
$string['unselectall'] = 'none';
$string['up'] = 'Up';
$string['updatebutton'] = 'Apply modification';
$string['updateclassification'] = 'Update classification';
$string['updateclassificationvalue'] = 'Update a classification value';
$string['updatemetadata'] = 'Update configuration';
$string['updatemetadataform'] = 'Update the description';
$string['updateresourcepageoff'] = 'Quit edition mode';
$string['updateresourcepageon'] = 'Go to edition mode';
$string['updatesharedresource'] = 'Update shared resource';
$string['updatesharedresourcetypefile'] = 'Update a shared resource';
$string['url'] = 'Shared resource URL';
$string['urlbuilt'] = 'New URL built : {$a}';
$string['used'] = 'Used {$a} times';
$string['validateform'] = 'Validate';
$string['variablename'] = 'Variable Name';
$string['vcard'] = 'Description of the Vcard structure';
$string['view_pageitem_page_embedded_content'] = 'View resource in page content';
$string['view_resource_info'] = 'View resource info';
$string['vol'] = 'Vol';
$string['widget'] = 'Search widgets';
$string['wrongform'] = '<h2>The form was not filled in correctly. Return to the form in 15sec </h2>';
$string['year'] = '- Year -';

$string['taxonpotentialselector'] = 'Potential taxons';
$string['selectedtaxonselector'] = 'Enabled taxons';
$string['selectedtaxons'] = 'Enabled taxons';
$string['pottaxonsmatching'] = 'Potential matching taxons';
$string['pottaxons'] = 'Potential taxons';
$string['potentialtaxons'] = 'Potential taxons';
$string['selectedtaxons'] = 'Selected taxons';
$string['taxonselectorpreserveselected'] = 'Preserve already selected';
$string['taxonselectorautoselectunique'] = 'Select single result automatically';
$string['taxonselectorsearchanywhere'] = 'Search anywhere';
$string['nomatchingtaxons'] = 'No matching taxons';
$string['previouslyselectedtaxons'] = 'Previously selected taxons';
$string['backtoconfig'] = 'Back to config';

$string['backtoclassifications'] = 'Back to classification list';
$string['classificationvalues'] = 'Classification taxons';
$string['token'] = 'Taxon';
$string['addtoken'] = 'Add new taxon';
$string['goup'] = 'Go up one level';
$string['notsupportedyet'] = 'Editing taxons elsewhere than in the sharedresource_taxonomy table is not yet supported.';


$string['configpopupmenubar_desc'] = 'Should popup windows show the menu bar by default?';

$string['configaccesscontrol_desc'] = 'Enable access control using profile field.';

$string['configarticlequantity_desc'] = 'Configures the number of new resources exposed';

$string['configbackupindex_desc'] = 'When the backup of a course is run, should ALL the shared Resource Index entries
be backup too (including local files if any)?';

$string['configdefaulturl_desc'] = 'This value is used to prefill the URL form when creating a new URL-based resource.';

$string['configforeignurlsheme_desc'] = 'General form of the URL. Use \'&lt;\%\%ID\%\%&gt;\' as the site of the Unique Resource Identifier';

$string['configframesize_desc'] = 'When a web page or an uploaded file is displayed within a frame, this value is the size (in pixels) of
the top frame (which contains the navigation).';

$string['configfreezeindex_desc'] = 'When the backup of a course is run, never backup any physical files ?';

$string['configpluginscontrol_desc'] = 'The following parameter control the plugin used in metadata sets while collecting data for indexation';

$string['configpopup_desc'] = 'When adding a new resource which is able to be shown in a popup window, should this option be enabled by default?';

$string['configpopupdirectories_desc'] = 'Should popup windows show directory links by default?';

$string['configpopuplocation_desc'] = 'Should popup windows show the location bar by default?';

$string['configpopupresizable_desc'] = 'Should popup windows be resizable by default?';

$string['configpopupheight_desc'] = 'What height should be the default height for new popup windows?';

$string['configpopupscrollbars_desc'] = 'Should popup windows be scrollable by default?';

$string['configpopupstatus_desc'] = 'Should popup windows show the status bar by default?';

$string['configpopuptoolbar_desc'] = 'Should popup windows show the tool bar by default?';

$string['configpopupwidth_desc'] = 'What width should be the default width for new popup windows?';

$string['configrestoreindex_desc'] = 'When the restore of a course is run, should ALL the shared Resource Index entries
be restored too (including local files if any)?  This will not overwrite exisitng entries in sharedresource_entry, and
sharedresource_metadata.';

$string['configscormintegration_desc'] = 'Chooses how scorms are integrated in course from the shared library location.';

$string['medatadaconfiguration_desc'] = 'This <a href="{$a}">additional page</a> allows the configuration of metadata form for each role,
and to choose the search widgets.';

// ************* Help Strings ******************/

$string['profilefieldname_help'] = '
   Name is given as a prefix:fieldname value. Prefix can be "user" or "profile_field", and respectively addresses core user
   profile attributes, or customized profile fields.
';

$string['matchedvalues_help'] = '
   You can enter an exact text value here, or a regexp value starting with ~ (ex : ~^someprefix).
';

$string['description_help'] = "
The description is a very short summary of the resource.\n\n
For some resource display options, the summary is printed along side
the resource itself, otherwise it appears on the resource index page
making it easier for students searching for particular resources.
";

$string['addclassification_help'] = "
A classification references to a database table, which must contains at least these four fields:\n\n
- id\n
- parent, which is the id of the taxon\'s parent\n
- label, which is the name of the taxon\n
- ordering, to give a specific order for children taxons. A minimum ordering is required to know if the first child have a ordering at 0 or 1.\n\n
The name of the database tale, and names of the fields must be written in the form.\n
You have to notice the name of the table can be written with the prefix of the moodle database tables or not, and that the name of the ordering field is optional (in this case, the order of the children taxons are determined by the id).\n
The configuration of each classification added is then necessary by clicking on the \"Configure\" button.
";

$string['addsharedresource_help'] = "
Resources are content: information the teacher wants to bring into the course.  These can be prepared files uploaded to the course
server; pages edited directly in Moodle; or external web pages made to appear part of this course.\n\n
Shared Resources are course independent, and are created in advance before being attached to a Course.\n\n
Resources are either URLs, or a locally uploaded file. Specify your resource, along with the appropriate metadata, to add it to the searchable index.
";

$string['classificationsearch_help'] = "
Searching through the classification will use successive select selections. You first need to choose which classification will be used (several can be applied to the resoruces), than choose the classification first category. A further select list will appear if there ae any child category in which you can refine selection.\n\n
Leave the subselects unselected if you want to match the whole supercategory, and hit the \"Search\" button.
";

$string['datesearch_help'] = '
A date search will use two date fields: You can define start and/or end of range.
';

$string['durationdescr_help'] = "
The duration combines two parts. The former gives the normalized and formatted expression of the duration, the latter is a textual unformatted description, if it can not be written in an another way, or if the textual information is required to complete the formatted value.\n\n
Here is a sample formatted duration: \"P2Y1M2DT1H20M25.55S\". \"P\" prefixes the calendar part of a duration; \"2Y\" = 2 years; \"1M\" = 1 months; \"2D\" = 2 days; \"T\" prefixes the time part of the duration: \"hour, minute, second\"; \"1H\" = 1 hour; \"20M\" = 20 minutes; \"25.55S\" = 25,55 seconds.
";

$string['durationsearch_help'] = '
Searching for duration will use comparison statements (=, !=, <...) followed by a textual duration expression.
';

$string['classificationupdate_help'] = "
A classification is bound to a database table that must provide four data fields:\n\n
- id, as primary identifier of a classification value\n
- parent, as reference to the taxon\\'s father\n
- label, as name of the taxon\n
- ordering, for defining childs order in a branch. Minimum ordering tells wether ordering starts on 0 or 1.
";

$string['numericsearch_help'] = '
Numeric search combines two parts: Former is a comparison operator. Latter is a numeric constraint.
';

$string['sqlrestriction_help'] = '
Restricting classification extraction allows focussing to a subset of the table\'s values using an additional arbitrary WHERE clause
to the taxon selection SELECT. You need know the table structure and how to write select SQL expressions to write restriction statement.
';

$string['searchsharedresource_help'] = "
All Shared Resources are course independent and created in advance.\n
Search for a resource, and select Choose to add this to the current course, or use the Preview function to examine.
";

$string['selectclassification_help'] = "
All taxons of classification will be provided to the metadata forms when the classification instance is enabled.\n
If a classification is deleted, all metadata which references to taxons of this classification will be deleted for all sharedresources in the library.\n
The \"Configure\" button allows configuring the selection of taxon paths which will be available for the classification instance.
";

$string['selectsearch_help'] = "
Searching in library uses single or multiple selects.\n\n
For multiple searchs, use Ctrl-Click to seelct more than one list options.
";

$string['selecttaxon_help'] = "
A taxon is an particuliar value of the classification. Taxon selection configures the option set that will feed the classification list in metadata forms and search engine.\n\n
Take care your selection query extracts all the parent nodes of a deeper level taxon, or it will not be usable for the classification.
";

$string['textsearch_help'] = "
Search by text combines three parts: a textual comparison operator, such as \"Contains\" (default), \"Starts with\" or \"Ends with\".\n\n
You can provide several values a comma separated list. The search engine will apply a logical OR operator on all subresults.
";

$string['vcard_help'] = "
vCard is an open standard format of personal data exchange (Visit Card).\n\n
It is useful to give personal or professional details to somebody.\n\n
The following example is a file with a unique adress : <br/><br/>
BEGIN:VCARD <br/>
VERSION:3.0 <br/>
N:Gump;Forrest <br/>
FN:Forrest Gump <br/>
ORG:Bubba Gump Shrimp Co. <br/>
TITLE:Shrimp Man <br/>
PHOTO;VALUE=URL;TYPE=GIF:http://www.example.com/dir_photos/my_photo.gif <br/>
TEL;TYPE=WORK,VOICE:(111) 555-1212 <br/>
TEL;TYPE=HOME,VOICE:(404) 555-1212 <br/>
ADR;TYPE=WORK:;;100 Waters Edge;Baytown;LA;30314;United States of America <br/>
LABEL;TYPE=WORK:100 Waters Edge\nBaytown, LA 30314\nUnited States of America <br/>
ADR;TYPE=HOME:;;42 Plantation St.;Baytown;LA;30314;United States of America <br/>
LABEL;TYPE=HOME:42 Plantation St.\nBaytown, LA 30314\nUnited States of America <br/>
EMAIL;TYPE=PREF,INTERNET:forrestgump@example.com <br/>
REV:20080424T195243Z <br/>
END:VCARD <br/>
";

$string['sharingcontext_help'] = "
A sharedresource can be shared at site level or restructed to a category area (and its subcategories).\n\n
Category attached sharedresources cannot be exposed to network sharing.
";

$string['userfieldvaluessingle'] = 'Single value access field';
$string['userfieldvaluessingle_help'] = 'If you choose a value, only people having this value in the associated profile field will access the resource';

$string['userfieldvaluesmultiple'] = 'Multiple values access field';
$string['userfieldvaluesmultiple_help'] = 'You can choose a set of values, one of which opens access to the resource (OR). If left empty set, the
resource will have no access restriction at all.';

$string['addinstance_search_desc'] = '
    Search in the library a shared resource and publish it in the course.
';

$string['addinstance_create_desc'] = '
    Upload a new resource within the shared library and finally publish it in the course.
';

/*Pro section*/

$string['emulatecommunity'] = "Emulate the community version.";
$string['emulatecommunity_desc'] = "If enabled, the plugin will behave as the public community version. This will increase compatibility with other implementations but will loose features !";
$string['licenseprovider'] = "Pro License provider";
$string['licenseprovider_desc'] = "Input here your provider key";
$string['licensekey'] = "Pro license key";
$string['licensekey_desc'] = "Input here the product license key you got from your provider ";

include(__DIR__.'/pro_additional_strings.php');