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
 * Library for classification manipulations.
 *
 * @package     mod_sharedresource
 * @author      Frederic GUILLOU
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/sharedresources/classes/navigator.class.php');

use local_sharedresources\browser\navigation;

/*
 * This php script contains all the stuff to use classifications
 * This is used in the metadata form of a sharedresource and
 * in the search engine of a sharedresource.
 */

/**
 * get all classification options, recursively
 * @see metadata_form.php
 * @param int $taxonomyid the taxonomy id. If 0, retrieves all active taxonomies.
 * @return an optgroup structure for a list
 */
function metadata_get_classification_options($taxonomyid = 0) {

    $taxonomies = navigation::get_taxonomies();

    if ($taxonomyid != 0) {
        $taxonomy = $taxonomies[$taxonomyid];
        $classification = new \local_sharedresources\browser\navigation($taxonomy);
        $options = $classification->get_full_tree('flat');
        return $options;
    }

    $optgroups = [];
    foreach ($taxonomies as $taxonomy) {
        $classification = new \local_sharedresources\browser\navigation($taxonomy);
        if (!$classification->can_use()) {
            continue;
        }
        $optgroup = $classification->get_full_tree('flat');
        $optgroups[][$taxonomy->name] = $optgroup;
    }

    return $optgroups;
}

/**
 * Recursive exploration of the classification to form a select option set.
 * @param string $name the classification domain name
 * @param object $finalclassif the whole classification tree
 * @param $id
 * @param $path
 * @param $selectedlabel which option is actually selected
 */
function metadata_get_classification_option_rec($name, $finalclassif, $id, $path='', $selectedlabel='') {

    $str = '';

    foreach ($finalclassif[$id]['childs'] as $ordering => $taxonid) {
        if (in_array($taxonid, $classifarray[$name]['taxonselect'])) {
            $temppath = $path.'/'.$finalclassif[$taxonid]['label'];
            if ($name.':'.$taxonid == substr($selectedlabel, 0, strripos($selectedlabel, ':'))) {
                $str .= '<option selected="selected" value="'.$name.':'.$taxonid.':'.$temppath.'">'.$temppath.'</option>';
            } else {
                $str .= '<option value="'.$name.':'.$taxonid.':'.$temppath.'">'.$temppath.'</option>';
            }
            if (!empty($finalclassif[$taxonid]['childs'])) {
                $str .= metadata_print_classification_options_rec($name, $classifarray, $finalclassif, $taxonid, $temppath,
                        $selectedlabel);
            }
        }
    }

    return $str;
}

/**
 * print_classif2 and print_classification_childs print all classifications, displaying successively SELECT (used in
 * the search form)
 * @param array $classifarray
 */
function print_classif2($classifarray) {

    $str = '';

    if (!empty($classifarray)) {
        foreach ($classifarray as $name => $infos) {
            if ($infos['select'] == 1) {
                $str .= '<option class="sharedresource-listsection" value="'.$name.'">'.$infos['classname'].'</option>';
            }
        }
    }

    return $str;
}

/**
 * Print classification childs
 * @param string $name
 * @param int $num
 * @param string $key
 * @param object $classif
 * @param mixed $value
 */
function print_classification_childs($name, $num, $key, $classif, $value) {
    global $CFG, $DB;

    $str = '';

    if ($name == 'defaultvalue') {
        return;
    }

    $config = get_config('sharedresource');

    $classifarray = unserialize($config->classifarray);

    // If we are searching for taxons just after choosing a classification (taxons without parents).
    if (array_key_exists($name, $classifarray)) {
        if ($classifarray[$name]['restriction'] == '') {
            $classtable = $DB->get_records($name);
        } else {
            $classtable = $DB->get_records_sql("SELECT * FROM {{$name}} WHERE ".$classifarray[$name]['restriction']);
        }
        $finalclassif = metadata_create_classification($classtable, $classifarray, $name);
        $restriction = $classifarray[$name]['taxonselect'];
        if (!empty($finalclassif[$name]['childs'])) {
            $str .= '<select name=classif:'.$num;
            $str .= ' onChange="javascript:classif(\''.$CFG->wwwroot.'\', this.options[selectedIndex].text,'.($num + 1).',';
            if ($key != '') {
                 $str .= '\''.$key.'/\'+this.options[selectedIndex].text,\'';
                 $str .= $classif.'\',this.options[this.selectedIndex].value);">';
            } else {
                $str .= 'this.options[selectedIndex].text,\''.$classif.'\',this.options[this.selectedIndex].value);">';
            }
            $str .= '<option selected value="basicvalue"> </option>';
            foreach ($finalclassif[$name]['childs'] as $ordering => $id) {
                if (in_array($id, $restriction)) {
                    if ($key != '') {
                        $tempkey = $key.'/'.$finalclassif[$id]['label'];
                    } else {
                        $tempkey = $finalclassif[$id]['label'];
                    }
                        $str .= '<option value="'.$id.'\\'.$tempkey.'">'.$finalclassif[$id]['label'].'</option>';
                }
            }
            $str .= '</select>';
        }
        // If we are searching the childs of a taxon.
    } else {
        if (!empty($classif)) {
            if ($classifarray[$classif]['restriction'] == '') {
                $classtable = $DB->get_records($classif);
            } else {
                $classtable = $DB->get_records_sql($classifarray[$classif]['restriction']);
            }
            $finalclassif = metadata_create_classification($classtable, $classifarray, $classif);
            $restriction = $classifarray[$classif]['taxonselect'];
            if (!empty($finalclassif[substr($value, 0, strpos($value, '\\'))]['childs'])) {
                $str .= '<select name=classif:'.$num.' onChange="javascript:classif(\'';
                $str .= $CFG->wwwroot.'\', this.options[selectedIndex].text,'.($num + 1).',';
                if ($key != '') {
                    $str .= '\''.$key.'/\'+this.options[selectedIndex].text,\'';
                    $str .= $classif.'\',this.options[this.selectedIndex].value);">';
                } else {
                    $str .= 'this.options[selectedIndex].text,\''.$classif.'\',this.options[this.selectedIndex].value);">';
                }
                $str .= '<option selected value="basicvalue"> </option>';
                foreach ($finalclassif[substr($value, 0, strpos($value, '\\'))]['childs'] as $ordering => $label) {
                    if (in_array($label, $restriction)) {
                        if ($key != '') {
                            $tempkey = $key.'/'.$finalclassif[$label]['label'];
                        } else {
                            $tempkey = $finalclassif[$label]['label'];
                        }
                            $str .= '<option value="'.$label.'\\'.$tempkey.'">'.$finalclassif[$label]['label'].'</option>';
                    }
                }
                $str .= '</select>';
            }
        }
    }

    return $str;
}
