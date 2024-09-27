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
 * Specific function set for metadata processing.
 *
 * @author  Frederic GUILLOU
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/*
 * Important design note about namespace processing:
 *
 * Namespace designate both field source namespace and actual Moodle active namespace.
 * field namespace must be used when querying or processing metadata values from the mdl_sharedresource_metadata table,
 * while Moodle enabled namespace should be used for getting translations for printing to screen field names. Lang files
 * of metadata plugins should thus contain the entire translation set for all fields and vocabularies used by the metadata
 * schema and all its subschemas.
 * Moodle active namespace should also be used to fetch moodle GUI behaviour configuration regarding the metadata administration.
 */
defined('MOODLE_INTERNAL') || die();

/*
 * Detects a change in the metadata model used and displays a message to a inform the user of the loss of the old metadata
 * in case of validation.
 */
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_metadata.class.php');

use mod_sharedresource\entry_factory;
use mod_sharedresource\entry;
use mod_sharedresource\metadata;

// Ensure thr adequate sharedresource_entry class is pre loaded.
entry_factory::get_entry_class();

/**
 * Function which display and check the metadata submitted by the form
 * @param entry $shrentry
 * @param array $metadataentries
 */
function metadata_display_and_check(entry & $shrentry, $metadataentries) {

    $config = get_config('sharedresource');
    $namespace = $config->schema;
    $mtdstandard = sharedresource_get_plugin($namespace);

    $taxumarray = $mtdstandard->get_taxum_path();
    if ($taxumarray) {
        $standardsourceelm = $mtdstandard->get_element($taxumarray['source']);
        $standardidelm = $mtdstandard->get_element($taxumarray['id']);
        $standardentryelm = $mtdstandard->get_element($taxumarray['entry']);
    }

    $fieldnamestr = get_string('mtdfieldname', 'sharedresource');
    $fieldidstr = get_string('mtdfieldid', 'sharedresource');
    $valuestr = get_string('mtdvalue', 'sharedresource');

    $error = [];
    $template = new StdClass();
    $template->fieldnamestr = $fieldnamestr;
    $template->fieldidstr = $fieldidstr;
    $template->valuestr = $valuestr;

    $keywordelm = $mtdstandard->get_keyword_element();

    foreach ($metadataentries as $htmlkey => $value) {

        // Discard any non-metadata entry.
        if (in_array($htmlkey, ['mode', 'fromlibrary', 'catid', 'catpath', 'course', 'section', 'returnpage',
                'context', 'go-btn'])) {
            continue;
        }

        // We check if the field have been filled for the vcard, select and date.
        if (preg_replace('/[[:space:]]/', '', $value) != 'BEGIN:VCARDVERSION:FN:N:END:VCARD'
                && $value != 'basicvalue'
                    && $value != '-year-'
                        && $value != '-month-'
                            && $value != '-day-'
                                && substr($htmlkey, -9) != 'datemonth'
                                    && substr($htmlkey, -7) != 'dateday'
                                        && substr($htmlkey, -3) != 'Hou'
                                            && substr($htmlkey, -3) != 'Min'
                                                && substr($htmlkey, -3) != 'Sec') {
            $errortemp = '';

            // If the key is a date, we have to process this key.
            if (substr($htmlkey, -3) == 'Day') {
                $htmlkeytemp = substr($htmlkey, 0, -4);
                $temp = 0;
                if ($value != '' && !metadata::is_integer($value)) {
                    $errortemp .= get_string('integerday', 'sharedresource');
                } else if ($value != '' && metadata::is_integer($value) && $value < 0) {
                    $errortemp .= get_string('incorrectday', 'sharedresource');
                }
                if ($value != '' && $value != '0') {
                    $temp = $value * DAYSECS;
                }
                $hourkey = $htmlkeytemp.'_Hou';
                $minkey = $htmlkeytemp.'_Min';
                $seckey = $htmlkeytemp.'_Sec';
                if ($metadataentries->$hourkey != '' &&
                        !metadata::is_integer($metadataentries->$hourkey)) {
                    $errortemp .= get_string('integerhour', 'sharedresource');
                } else if ($metadataentries->$hourkey != '' &&
                                metadata::is_integer($metadataentries->$hourkey) &&
                                        $metadataentries->$hourkey < 0) {
                    $errortemp .= get_string('incorrecthour', 'sharedresource');
                }
                if ($metadataentries->$hourkey != '' && $metadataentries->$hourkey != '0') {
                    $temp += $metadataentries->$hourkey * HOURSECS;
                }
                if ($metadataentries->$minkey != '' &&
                        !metadata::is_integer($metadataentries->$minkey)) {
                    $errortemp .= get_string('integerminute', 'sharedresource');
                } else if ($metadataentries->$minkey != '' &&
                        metadata::is_integer($metadataentries->$minkey) &&
                                $metadataentries->$minkey < 0) {
                    $errortemp .= get_string('incorrectminute', 'sharedresource');
                }
                if ($metadataentries->$minkey != '' && $metadataentries->$minkey != '0') {
                    $temp += $metadataentries->$minkey * MINSECS;
                }
                if ($metadataentries->$seckey != '' && $metadataentries->$seckey < 0) {
                    $errortemp .= get_string('incorrectsecond', 'sharedresource');
                }
                if ($metadataentries->$seckey != '' && $metadataentries->$seckey != '0') {
                    $temp += $metadataentries->$seckey;
                }
                $htmlkey = $htmlkeytemp;
                if ($temp != 'P') {
                    $value = $temp;
                } else {
                    $value = '';
                }
            } else if (substr($htmlkey, -8) == 'dateyear') {
                // If the key is a duration, we have to process this key.
                $yearkey = $htmlkey;
                $htmlkey = substr($htmlkey, 0, -9);
                $monthkey = $htmlkey.'_datemonth';
                $daykey = $htmlkey.'_dateday';
                if ($metadataentries->$monthkey != '-month-') {
                    $value .= '-'.$metadataentries->$monthkey;
                    if ($metadataentries->$daykey != '-day-') {
                        // If date is invalid (fe 30 feb).
                        if (!checkdate($metadataentries->$monthkey, $metadataentries->$daykey, $metadataentries->$yearkey)) {
                            $errortemp = get_string('incorrectdate', 'sharedresource');
                            $value .= '-'.$metadataentries->$daykey;
                        } else {
                            $value .= '-'.$metadataentries->$daykey;
                        }
                    } else {
                        $value .= '-01';
                    }
                } else {
                    $value .= '-01-01';
                }
                $value = mktime(0, 0, 0, substr($value, 5, 2), substr($value, 8, 2), substr($value, 0, 4));
            }

            /*
             * At this point any suffixed htmlkey should be decoded and cleaned. It is safe to
             * convert to strorage keys.
             */
            $elementkey = metadata::html_to_storage($htmlkey);
            list($nodeid, $instanceid)  = explode(':', $elementkey);

            // In case of a keyword element (if we have some), we have to check there is only one keyword, with no punctuation.
            if ($keywordelm) {
                if ($nodeid == $keywordelm->node) {
                    if (preg_match('/[[,;:.\/\\]]/', $value)) {
                        $errortemp .= get_string('keywordpunct', 'sharedresource');
                    }
                }
            }

            $elementtpl = new StdClass;

            /*
             * In case of a taxon path, we have to process the result and divide it into three fields : source, id and entry.
             * taxumarray gives the nodeid references of subtaxons data
             */

            if ($taxumarray && $nodeid == $taxumarray['main']) {

                $sourcename = $standardsourceelm->name;
                // We are in a classification.

                // Value for SOURCE comes directly from the metadata taxonomy source select, or.
                $elementtpl->elmname = $sourcename;
                $sourcenodeid = $taxumarray['source'];
                // Get full source element key from the taxon path branch.
                $sourceelementkey = metadata::to_instance($sourcenodeid, $instanceid);
                $sourcehtmlname = metadata::storage_to_html($sourceelementkey);
                $elementtpl->elmkey = $sourceelementkey;
                if (!empty($metadataentries->$sourcehtmlname)) {
                    $elementtpl->elmvalue = $metadataentries->$sourcehtmlname;
                    $shrentry->add_element($sourceelementkey, $metadataentries->$sourcehtmlname, $namespace);
                }
                $template->elements[] = $elementtpl;

                // Value for ID comes directly from the metadata taxonomy select.
                $elementtpl = new StdClass();
                $elementtpl->elmname = $standardidelm->name;
                $idnodeid = $taxumarray['id'];
                $idelementkey = metadata::to_instance($idnodeid, $instanceid);
                $elementtpl->elmkey = $idelementkey;
                $elementtpl->elmvalue = $value;

                // Actually adds the metadata value to the shared entry.
                $shrentry->add_element($idelementkey, $value, $namespace);

                $template->elements[] = $elementtpl;

                // Value for ENTRY is deduced from the taxonomy source.
                $elementtpl = new StdClass();
                $elementtpl->elmname = $standardentryelm->name;
                $entrynodeid = $taxumarray['entry'];
                $entryelementkey = metadata::to_instance($entrynodeid, $instanceid);
                $elementtpl->elmkey = $entryelementkey;
                $elementtpl->elmvalue = $value;

                // Actually adds the metadata value to the shared entry.
                $shrentry->add_element($entryelementkey, $value, $namespace);

                $template->elements[] = $elementtpl;

            } else {
                // All other cases (standard metadata).
                if ($errortemp != '') {
                    $error[$htmlkey] = $errortemp;
                }
                if ($value != '') {

                    $standardelm = $mtdstandard->get_element($nodeid);
                    $name = $standardelm->name;

                    $elementtpl = new StdClass();
                    $elementtpl->elmname = $name;
                    $elementtpl->elmkey = $elementkey;
                    $elementtpl->elmvalue = $value;
                    $template->elements[] = $elementtpl;

                    // Actually adds the metadata value to the shared entry.
                    $shrentry->add_element($elementkey, $value, $namespace);
                }
            }
        }
    }
    $result['display'] = $template;
    $result['error'] = $error;

    return $result;
}

/**
 * Helper function
 * @param string $value
 */
function clean_string_key($value) {
    $value = strtolower($value);
    $value = str_replace('.', '', $value);
    $value = str_replace('(', '', $value);
    $value = str_replace(')', '', $value);
    $value = str_replace(' ', '', $value);
    $value = str_replace('_', '', $value);
    $value = str_replace('-', '', $value);
    $value = str_replace('\'', '', $value);
    $value = str_replace('/', '', $value);
    $value = mb_ereg_replace("'", '', $value);
    $value = str_replace('é', 'e', $value);
    $value = str_replace('ê', 'e', $value);
    $value = str_replace('è', 'e', $value);
    $value = str_replace('ë', 'e', $value);
    $value = str_replace('î', 'i', $value);
    $value = str_replace('ï', 'i', $value);
    $value = str_replace('û', 'u', $value);
    $value = str_replace('ü', 'u', $value);
    $value = str_replace('ô', 'o', $value);
    $value = str_replace('à', 'a', $value);
    $value = str_replace('â', 'a', $value);
    $value = str_replace('ç', 'c', $value);
    return $value;
}

/**
 * Initialize thos elements who are handled by core in standard resource
 * @param object $mtdstandard the metadata plugin
 * @param object entry $shrentry
 */
function metadata_initialise_core_elements($mtdstandard, entry & $shrentry) {
    global $USER, $DB, $SESSION;

    // Initialise metadata elements from core : description and title.
    $descriptionelement = $mtdstandard->get_description_element();

    $shrentry->update_element($descriptionelement->node.':0_0', $shrentry->description, $mtdstandard->get_namespace());
    $titleelement = $mtdstandard->get_title_element();

    $shrentry->update_element($titleelement->node.':0_0', $shrentry->title, $mtdstandard->get_namespace());

    // If we have a file, find the size element and update value from known size.
    $usercontext = context_user::instance($USER->id);
    $filerecid = $shrentry->file;
    if (!empty($filerecid)) {
        if (method_exists($mtdstandard, 'get_size_element')) {
            $draftsize = $DB->get_field('files', 'filesize', ['id' => $filerecid]);
            $element = $mtdstandard->get_size_element();
            $shrentry->update_element($element->node.':0_0', $draftsize, $mtdstandard->get_namespace());
        }
    }

    // If we have a file, find the format element and update value from known mimetype as technical format.
    if (!empty($filerecid)) {
        if (method_exists($mtdstandard, 'get_file_format_element')) {
            $mimetype = $DB->get_field('files', 'mimetype', ['id' => $filerecid]);
            $element = $mtdstandard->get_file_format_element();
            $shrentry->update_element($element->node.':0_0', $mimetype, $mtdstandard->get_namespace());
        }
    }

    // If we have a file, find the location element and update value from known access url as location.
    $identifier = $shrentry->identifier;
    if ($identifier) {
        if (method_exists($mtdstandard, 'get_location_element')) {
            if (empty($config->foreignurl)) {
                $url = new moodle_url('/local/sharedresources/view.php', ['identifier' => $identifier]);
            } else {
                $url = str_replace('<%%ID%%>', $identifier, $config->foreignurl);
            }
            $element = $mtdstandard->get_location_element();
            $shrentry->update_element($element->node.':0_0', $url, $mtdstandard->get_namespace());
        }
    }

    // Push back in session for metadata_get_stored_value calls.
    $SESSION->sr_entry = serialize($shrentry);
}

/**
 * Get the actual capability of the current user regarding sharedresource edition.
 */
function metadata_get_user_capability() {

    if (has_capability('repository/sharedresources:systemmetadata', context_system::instance())) {
        $capability = 'system';
    } else {
        if (has_capability('repository/sharedresources:indexermetadata', context_system::instance())) {
            $capability = 'indexer';
        } else {
            $capability = 'author';
        }
    }

    return $capability;
}

/**
 * Given an element name as xny_zxw_..._k, returns a tail incremented name (k+1)
 * @param string $elmname
 */
function metadata_increment_name_occurrence($elmname) {
    $parts = explode('_', $elmname);

    $occurrence = array_pop($parts);
    if (is_numeric($occurrence)) {
        array_push($parts, $occurrrence + 1);
    } else {
        // Can be in the xny format.
        list($nodeindex, $occurrence) = explode('n', $occurrence);
        array_push($parts, $nodeindex.'n'.($occurrence + 1));
    }
    return implode('_', $parts);
}

/**
 * Returns an occurrence number that matches the pos level as a parent
 * occurrence index. F.e. : if an entry node has index 0_1_1_1 and we need
 * the corresponding occurence branch for a 9_2 node, that we get the 0_1 prefix
 * @param string $occ the occurence index path
 * @param string $pos the target position node path
 *
 * @todo : Replace by a sharedresource_metadata method using get_parent() and get_instance_id()
 */
function metadata_get_node_occurence($occ, $pos) {

    $level = count(explode('_', $pos));

    $occparts = explode('_', $occ);

    $occout = [];
    for ($i = 0; $i < $level; $i++) {
        $nextpart = 0 + array_shift($occparts);
        $occout[] = $nextpart;
    }

    return implode('_', $occout);
}
