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
 * @package    sharedresource
 * @category   mod
 *
 * This php script display the admin part of a specific 
 * classification. You can modify the classification and
 * select the taxon path which are displayed.
 *
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');

$classification = required_param('classification', PARAM_TEXT);
$mode = optional_param('mode', 0, PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_ALPHANUM);
$parent = optional_param('parent', 0, PARAM_TEXT);
$label = optional_param('label', '', PARAM_TEXT);
$ordering = optional_param('ordering', 0, PARAM_TEXT);
$orderingmin = optional_param('orderingmin', 0, PARAM_INT);

// Security.

$systemcontext = context_system::instance();
require_login();
require_capability('moodle/site:config', $systemcontext);

// Build page.

$url = new moodle_url('/mod/sharedresource/classificationconfigure.php');
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->requires->js('/mod/sharedresource/js/taxon.js');

$strtitle = get_string('classificationconfiguration', 'sharedresource');
$PAGE->set_pagelayout('standard');
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($strtitle,'classificationconfigure.php','misc');
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');

echo $OUTPUT->header();

$classifarray = unserialize(get_config('sharedresource', 'classifarray'));

if ($classifarray[$classification]['restriction'] == '') {
    $classtable = $DB->get_records($classification);
} else {
    $classtable = $DB->get_records_sql(' SELECT * FROM {'.$classification.'} WHERE '.$classifarray[$classification]['restriction']);
}

$error = '';
$updatecomplete = '';
if (!empty($_POST)) {
    if ($mode == 'update') {
        $updateclassif = true;
        $metatables = $DB->MetaTables();
        $metatables = array_flip($metatables);
        $metatables = array_change_key_case($metatables, CASE_LOWER);
        if (strstr($classification, $CFG->prefix) == false) {
            $tablename = $CFG->prefix.$classification;
        }
        $listfield = array();
        foreach ($db->MetaColumns($tablename) as $key => $value) {
            array_push($listfield, $value->name);
        }
        if (!isset($id) || $id == '') {
            $updateclassif = false;
            $error .= get_string('missingnameid','sharedresource');
        } else if (!in_array($id,$listfield)) {
            $updateclassif = false;
            $error .= get_string('missingid', 'sharedresource');
        }
        if (!isset($parent) || $parent == '') {
            $updateclassif = false;
            $error .= get_string('missingnameparent', 'sharedresource');
        } else if (!in_array($parent, $listfield)) {
            $updateclassif = false;
            $error .= get_string('missingparent', 'sharedresource');
        }
        if (!isset($label) || $label == '') {
            $updateclassif = false;
            $error .= get_string('missingnamelabel', 'sharedresource');
        } else if (!in_array($label,$listfield)) {
            $updateclassif = false;
            $error .= get_string('missinglabel', 'sharedresource');
        }
        if (isset($ordering) && !in_array($ordering, $listfield)) {
            $updateclassif = false;
            $error .= get_string('missingordering', 'sharedresource');
        }
        if ($updateclassif) {
            $classifarray = unserialize(get_config('sharedresource', 'classifarray'));
            $classifarray[$classification]['id'] = $id;
            $classifarray[$classification]['parent'] = $parent;
            $classifarray[$classification]['label'] = $label;
            $classifarray[$classification]['ordering'] = $ordering;
            $classifarray[$classification]['orderingmin'] = $orderingmin;
            set_config('classifarray', serialize($classifarray), 'sharedresource');
            $updatecomplete = get_string('successfulmodification', 'sharedresource');
        }
    } else if ($mode == 'select') {
        if (!empty($_POST['selection'])) {
            $classifarray[$classification]['taxonselect'] = array();
            foreach ($_POST['selection'] as $key => $value) {
                array_push($classifarray[$classification]['taxonselect'], $value);
            }
            set_config('classifarray', serialize($classifarray), 'sharedresource');
        } else {
            $classifarray[$classification]['taxonselect'] = array();
            set_config('classifarray', serialize($classifarray), 'sharedresource');
        }
    }
}
echo '<center>';
$params = array('classification' => $classification, 'mode' => 'update');
$formurl = new moodle_url('/mod/sharedresource/classificationconfigure2.php', $params);
echo '<form name="classifmodif" action="'.$formurl.'" method="post class="mform">';
echo '<fieldset id="ClassList">';
echo '<legend align="center">';
echo get_string('classificationupdate','sharedresource');
print $OUTPUT->help_icon('classificationupdate', 'sharedresource');
echo '</legend><br/>';
if ($error != '') {
    echo '<center style="color:red;">'.$error.'</center><br/>';
}
if ($updatecomplete != '') {
    echo '<center style="color:blue;">'.$updatecomplete.'</center><br/>';
}

echo '<table border="1" class="generaltable">';

echo '<tr>';
echo '<td align="center" width="200px">';
echo get_string('idname','sharedresource');
echo '</td>';
echo '<td><input type="text" name="id" size="50" value="'.$classifarray[$classification]['id'].'"/></td>';
echo '</tr>';

echo '<tr>';
echo '<td align="center" width="200px">';
echo get_string('parentname','sharedresource');
echo '</td>';
echo '<td><input type="text" name="parent" size="50" value="'.$classifarray[$classification]['parent'].'"/></td>';
echo '</tr>';

echo '<tr>';
echo '<td align="center" width="200px">';
echo get_string('labelname','sharedresource');
echo '</td>';
echo '<td><input type="text" name="label" size="50" value="'.$classifarray[$classification]['label'].'"/></td>';
echo '</tr>';

echo '<tr>';
echo '<td align="center" width="200px">';
echo get_string('orderingname','sharedresource');
echo '</td>';
echo '<td><input type="text" name="ordering" size="50" value="'.$classifarray[$classification]['ordering'].'"/></td>';
echo '</tr>';

echo '<tr>';
echo '<td align="center" width="200px">';
echo get_string('orderingmin','sharedresource');
echo '</td><td><select name="orderingmin">';
if ($classifarray[$classification]['orderingmin'] == 0) {
    echo '<option selected value="0">0</option>';
    echo '<option value="1">1</option>';
} else if ($classifarray[$classification]['orderingmin'] == 1) {
    echo '<option value="0">0</option>';
    echo '<option selected value="1">1</option>';
}
echo '</select></td></tr>';

echo '</table><br/>';
echo '<center><input type="submit" value="'.get_string('updatebutton','sharedresource').'"/></center>';
echo '</fieldset><br/>';
echo '</form>';

echo '<fieldset id="ClassSelect" style="margin:0 auto;width:75%;">';

echo '<legend align="center">';
echo get_string('taxonchoicetitle','sharedresource');
echo $OUTPUT->help_icon('selecttaxon', 'sharedresource');
echo '</legend><br/>';
echo '<center>';

$params = array('classification' => $classification, 'mode' => 'select');
$formurl = new moodle_url('/mod/sharedresource/classificationconfigure2.php', $params);
echo '<form name="taxonselect" action="'.$formurl.'" method="post" onSubmit="return select_all(this)">';

echo '<table>';
echo '<tr>';
echo '<td align="center">'.get_string('notselectable', 'sharedresource').'</td>';
echo '<td></td>';
echo '<td align="center">'.get_string('selectable', 'sharedresource').'</td>';
echo '</tr>';

echo '<tr>';
echo '<td>';
$jshandler = 'javascript:selection_champs(this.form.liste_champs,this.form.selection)';
echo '<select class="multiple" style="width:250px;height:200px;" name="liste_champs" multiple OnDblClick="'.$jshandler.'" >';
foreach ($classtable as $classif) {
    if (!in_array($classif->$classifarray[$classification]['id'], $classifarray[$classification]['taxonselect'])) {
        echo '<option value="'.$classif->$classifarray[$classification]['id'].'">'.$classif->$classifarray[$classification]['label'].'</option>';
    }
}
echo '</select>';
echo '</td>';
echo '<td>';

echo '<table>';
echo '<tr>';
$jshandler = 'javascript:selection_champs(this.form.liste_champs,this.form.selection)';
echo '<td><input class="btn" type="button" name="selectionner" value=" >> " OnClick="'.$jshandler.'"></td>';
echo '</tr>';
$jshandler = 'javascript:selection_champs(this.form.selection,this.form.liste_champs)';
echo '<tr><td><input class="btn" type="button" name="deselect" value=" << " OnClick="'.$jshandler.'"></td>';
echo '</tr>';
echo '</table>';

echo '</td>';
echo '<td>';
$jshandler = 'javascript:selection_champs(this.form.selection,this.form.liste_champs)';
echo '<select class="multiple" style="width:250px;height:200px;" name="selection" multiple OnDblClick="'.$jshandler.'">';
foreach ($classtable as $classif) {
    if (in_array($classif->$classifarray[$classification]['id'], $classifarray[$classification]['taxonselect'])) {
        echo '<option value="'.$classif->$classifarray[$classification]['id'].'">'.$classif->$classifarray[$classification]['label'].'</option>';
    }
}
echo '</select>';
echo '</td>';
echo '</tr>';

echo '</table>';

echo '<br/><input type="submit" value="'.get_string('saveselection','sharedresource').'"/>';
echo '</form>';
echo '</center>';
echo '</fieldset><br/>';
$jshandler = 'window.location.href=\''.$CFG->wwwroot.'/mod/sharedresource/classificationconfigure.php\'';
$label = get_string('backclassifpage', 'sharedresource');
echo '<center><br/><input class="btn" type="button" value="'.$label.'" OnClick="'.$jshandler.'"/></center><br/>';
echo '</center>';

echo $OUTPUT->footer();
