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
 * @author  Frederic GUILLOU
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package    mod_sharedresource
 * @category   mod
 */
defined('MOODLE_INTERNAL') || die();

/*
 * This php script contains all the stuff to use classifications
 * This is used in the metadata form of a sharedresource and 
 * in the search engine of a sharedresource.
 */

/**
 * These two functions (create_classif and create_classif_rec) create a useable array representing a classification.
 */
function metadata_create_classification($classtable, $classifarray, $classification) {

    $newclassif = array();
    $tempclassif = $classtable;
    $newclassif[$classification] = array('label' => '',
                                         'ordering' => '',
                                         'childs' => array()
                                    );
    $i = 0;
    foreach ($classtable as $key => $taxon) {
        if ($taxon->$classifarray[$classification]['parent'] == '' ||
                $taxon->$classifarray[$classification]['parent'] == 0) {
            if (($taxon->$classifarray[$classification]['ordering'] != '') &&
                    ($taxon->$classifarray[$classification]['ordering'] != 0)) {
                $ordering = $taxon->$classifarray[$classification]['ordering'];
                $newclassif[$classification]['childs'][$ordering] = $taxon->$classifarray[$classification]['id'];
            } else {
                $newclassif[$classification]['childs']['none'.$i] = $taxon->$classifarray[$classification]['id'];
                $i++;
            }
            $classifkey = $taxon->$classifarray[$classification]['id'];
            $newclassif[$classifkey] = array(
                'label' => $taxon->$classifarray[$classification]['label'],
                'ordering' => $taxon->$classifarray[$classification]['ordering'],
                'childs' => array()
            );
            unset($tempclassif[$key]);
        }
    }
    $finalclassif = metadata_create_classification_rec($tempclassif, $newclassif, $classifarray, $classification);

    return $finalclassif;
}

/**
 * Recursive function in classification tree
 *
 */
function metadata_create_classification_rec($tempclassif, $newclassif, $classifarray, $classification){
    while (!empty($tempclassif)) {
        foreach ($newclassif as $key => $value) {
            $i = 0;
            foreach ($tempclassif as $id => $classif) {
                if ($classif->$classifarray[$classification]['parent'] == $key) {
                    // If the ordering is defined, it is the key in the child array.
                    $minordering = $classifarray[$classification]['orderingmin'];
                    if ($classif->$classifarray[$classification]['ordering'] != '' &&
                            $classif->$classifarray[$classification]['ordering'] >= $minordering) {
                        $childid = $classif->$classifarray[$classification]['ordering'];
                        $newclassif[$key]['childs'][$childid] = $classif->$classifarray[$classification]['id'];
                    } else {
                        // Else, the key "none" followed by a number is given as the key in the child array.
                        $newclassif[$key]['childs']['none'.$i] = $classif->$classifarray[$classification]['id'];
                        $i++;
                    }
                    $classifid = $classif->$classifarray[$classification]['id'];
                    $newclassif[$classifid] = array(
                        'label' => $classif->$classifarray[$classification]['label'], 
                        'ordering' => $classif->$classifarray[$classification]['ordering'], 
                        'childs' => array()
                    );
                    unset($tempclassif[$id]);
                }
            }
            ksort($newclassif[$key]['childs']);
        }
    }
    return $newclassif;
}

/*
 * prints all classification, recursively, in one SELECT
 * @see metadata_form.php
 */
function metadata_print_classification_options($classifarray, $selectedlabel = '') {
    global $DB;

    foreach ($classifarray as $name => $infos) {
        if ($infos['select'] == 1) {
            if ($infos['restriction'] == '') {
                $classtable =  $DB->get_records($name);
            } else {
                $sql = "
                    SELECT
                        *
                    FROM
                        {{$name}}
                    WHERE
                        {$classifarray[$name]['restriction']}
                ";
                $classtable =  $DB->get_records_sql($sql, array());
            }
            $finalclassif = metadata_create_classification($classtable, $classifarray, $name);
            echo '<option class="sharedresource-listsection" disabled="disabled" value="'.$name.'">'.$name.'</option>';
            foreach ($finalclassif[$name]['childs'] as $ordering => $id) {
                if (!empty($infos['taxonselect']) && !in_array($id, $infos['taxonselect'])) {
                    continue;
                }
                if ($name.':'.$id == substr($selectedlabel, 0, strripos($selectedlabel, ':'))) {
                    echo '<option selected value="'.$name.':'.$id.':'.$finalclassif[$id]['label'].'">'.$finalclassif[$id]['label'].'</option>';
                } else {
                    echo '<option value="'.$name.':'.$id.':'.$finalclassif[$id]['label'].'">'.$finalclassif[$id]['label'].'</option>';
                }
                echo metadata_print_classification_options_rec($name, $classifarray, $finalclassif, $id,
                                                               $finalclassif[$id]['label'], $selectedlabel);
            }
        }
    }
}

/**
 * Recursive exploration of the classification
 *
 */
function metadata_print_classification_options_rec($name, $classifarray, $finalclassif, $id, $path='', $selectedlabel='') {
    foreach ($finalclassif[$id]['childs'] as $ordering => $taxonid) {
        if (in_array($taxonid,$classifarray[$name]['taxonselect'])) {
            $temppath = $path.'/'.$finalclassif[$taxonid]['label'];
            if ($name.':'.$taxonid == substr($selectedlabel, 0, strripos($selectedlabel, ':'))) {
                echo '<option selected="selected" value="'.$name.':'.$taxonid.':'.$temppath.'">'.$temppath.'</option>';
            } else {
                echo '<option value="'.$name.':'.$taxonid.':'.$temppath.'">'.$temppath.'</option>';
            }
            if (!empty($finalclassif[$taxonid]['childs'])) {
                metadata_print_classification_options_rec($name, $classifarray, $finalclassif, $taxonid, $temppath, $selectedlabel);
            }
        }
    }
}

/*
 * print a complete classification path
 * @see metadatanotice.php
 */
function metadata_print_classification_value($classifarray, $selectedlabel = '') {
    global $DB;

    if ($classifarray) {
        foreach ($classifarray as $name => $infos) {
            if ($infos['select'] == 1) {
                if ($infos['restriction'] == '') {
                    $classtable = $DB->get_records($name);
                } else {
                    $classtable = $DB->get_records_sql("SELECT * FROM {{$name}} WHERE ".$classifarray[$name]['restriction']);
                }
                $finalclassif = metadata_create_classification($classtable, $classifarray, $name);
                foreach ($finalclassif[$name]['childs'] as $ordering => $id) {
                    if (in_array($id, $infos['taxonselect'])) {
                        if ($name.':'.$id == substr($selectedlabel, 0, strripos($selectedlabel, ':'))) {
                            echo '/ '.$finalclassif[$id]['label'].' ';
                        }
                        echo metadata_print_classification_value_rec($name, $classifarray, $finalclassif, $id, $selectedlabel);
                    }
                }
            }
        }
    }
}

function metadata_print_classification_value_rec($name, $classifarray, $finalclassif, $id, $selectedlabel = '') {
    foreach ($finalclassif[$id]['childs'] as $ordering => $taxonid) {
        if (in_array($taxonid, $classifarray[$name]['taxonselect'])) {
            if ($name.':'.$taxonid == substr($selectedlabel, 0, strripos($selectedlabel, ':'))) {
                echo '/ '.$finalclassif[$taxonid]['label']. ' ';
            }
            if (!empty($finalclassif[$taxonid]['childs'])) {
                metadata_print_classification_value_rec($name, $classifarray, $finalclassif, $taxonid, $selectedlabel);
            }
        }
    }
}

/**
 * print_classif2 and print_classification_childs print all classifications, displaying successively SELECT (used in the search form)
 */
function print_classif2($classifarray) {
    if (!empty($classifarray)) {
        foreach ($classifarray as $name => $infos) {
            if ($infos['select'] == 1) {
                echo '<option class="sharedresource-listsection" value="'.$name.'">'.$infos['classname'].'</option>';
            }
        }
    }
}

function print_classification_childs($name, $num, $key, $classif, $value) {
    global $CFG, $DB;

    if ($name == 'defaultvalue') {
        return ;
    }

    $config = get_config('sharedresource');

    $classifarray = unserialize($config->classifarray);
    // If we are searching for taxons just after choosing a classification (taxons without parents).
    if (array_key_exists($name,$classifarray)) {
        if ($classifarray[$name]['restriction'] == '') {
            $classtable = $DB->get_records($name);
        } else {
            $classtable = $DB->get_records_sql("SELECT * FROM {{$name}} WHERE ".$classifarray[$name]['restriction']);
        }
        $finalclassif = metadata_create_classification($classtable, $classifarray, $name);
        $restriction = $classifarray[$name]['taxonselect'];
        if (!empty($finalclassif[$name]['childs'])) {
            echo '<select name=classif:'.$num.' onChange="javascript:classif(\''.$CFG->wwwroot.'\', this.options[selectedIndex].text,'.($num+1).',';
            if ($key != '') {
                 echo '\''.$key.'/\'+this.options[selectedIndex].text,\''.$classif.'\',this.options[this.selectedIndex].value);">';
            } else {
                echo 'this.options[selectedIndex].text,\''.$classif.'\',this.options[this.selectedIndex].value);">';
            }
            echo '<option selected value="basicvalue"> </option>';
                foreach ($finalclassif[$name]['childs'] as $ordering => $id) {
                    if (in_array($id, $restriction)) {
                        if ($key != '') {
                            $tempkey = $key.'/'.$finalclassif[$id]['label'];
                        } else {
                            $tempkey = $finalclassif[$id]['label'];
                        }
                        echo '<option value="'.$id.'\\'.$tempkey.'">'.$finalclassif[$id]['label'].'</option>';
                    }
                }
            echo '</select>';
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
                echo '<select name=classif:'.$num.' onChange="javascript:classif(\''.$CFG->wwwroot.'\', this.options[selectedIndex].text,'.($num + 1).',';
                if ($key != '') {
                    echo '\''.$key.'/\'+this.options[selectedIndex].text,\''.$classif.'\',this.options[this.selectedIndex].value);">';
                } else {
                    echo 'this.options[selectedIndex].text,\''.$classif.'\',this.options[this.selectedIndex].value);">';
                }
                echo '<option selected value="basicvalue"> </option>';
                    foreach ($finalclassif[substr($value, 0, strpos($value, '\\'))]['childs'] as $ordering => $label) {
                        if (in_array($label, $restriction)) {
                            if ($key != '') {
                                $tempkey = $key.'/'.$finalclassif[$label]['label'];
                            } else {
                                $tempkey = $finalclassif[$label]['label'];
                            }
                            echo '<option value="'.$label.'\\'.$tempkey.'">'.$finalclassif[$label]['label'].'</option>';
                        }
                    }
                echo '</select>';
            }
        }
    }
}
