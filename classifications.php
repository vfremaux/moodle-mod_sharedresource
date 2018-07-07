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
 * @author  Frederic GUILLOU
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package    sharedresource
 * @subpackage mod_sharedresource
 * @category   mod
 *
 * This php script display the admin part of the classification
 * configuration. You can add, delete or apply a restriction
 * on a classification, or configure a specific classification
 * by accessing another page
 *
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/local/sharedresources/classes/navigator.class.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/ddllib.php');

$id = optional_param('id', 0, PARAM_TEXT);
$classname = optional_param('classificationname', '', PARAM_TEXT);
$action = optional_param('what', 0, PARAM_ALPHA);
$target = optional_param('target', '', PARAM_TEXT);
$table = optional_param('table', '', PARAM_TEXT);
$parent = optional_param('parent', 0, PARAM_TEXT);
$label = optional_param('label', '', PARAM_TEXT);
$ordering = optional_param('ordering', 0, PARAM_TEXT);
$orderingmin = optional_param('orderingmin', 0, PARAM_INT);

// Security.

$systemcontext = context_system::instance();
require_login();
require_capability('repository/sharedresources:manage', $systemcontext);

// Build page.

$url = $CFG->wwwroot.'/mod/sharedresource/classifications.php';
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('classificationconfiguration', 'sharedresource'));

if (!empty($action)) {
    include($CFG->dirroot.'/mod/sharedresource/classifications.controller.php');
    $controller = new \mod_sharedresource\classification\classifications_controller();
    $controller->receive($action);
    $controller->process($action);
}

$classifications = $DB->get_records('sharedresource_classif');

if (empty($classifications)) {
    echo '<center>'.get_string('noclassification','sharedresource').'</center>';
} else {

    $table = new html_table();

    $namestr = get_string('name');
    $taxonsstr = get_string('taxons', 'sharedresource');
    $tablestr = get_string('tablename', 'sharedresource');
    $sqlrestrictionsstr = get_string('sqlrestriction', 'sharedresource');
    $taxonselectionstr = get_string('taxonselection', 'sharedresource');

    $table->head = array($namestr, $taxonsstr, $tablestr, $sqlrestrictionsstr, $taxonselectionstr, '');
    $table->width = '100%';
    $table->size = array('30%', '10%', '20%', '20%', '10%', '10%');
    $table->align = array('left', 'center', 'left', 'center', 'center', 'right');

    $attrs = array('src' => $OUTPUT->pix_url('t/show'), 'class' => 'mod-sharedresource-classif-ctl');
    $hiddenicon = html_writer::tag('img', '', $attrs);

    $attrs = array('src' => $OUTPUT->pix_url('t/hide'), 'class' => 'mod-sharedresource-classif-ctl');
    $visibleicon = html_writer::tag('img', '', $attrs);

    $attrs = array('src' => $OUTPUT->pix_url('t/edit'), 'class' => 'mod-sharedresource-classif-ctl');
    $editicon = html_writer::tag('img', '', $attrs);

    $attrs = array('src' => $OUTPUT->pix_url('t/delete'), 'class' => 'mod-sharedresource-classif-ctl');
    $deleteicon = html_writer::tag('img', '', $attrs);

    $attrs = array('src' => $OUTPUT->pix_url('i/filter'));
    $filteredicon = html_writer::tag('img', '', $attrs);

    $attrs = array('src' => $OUTPUT->pix_url('i/withsubcat'), 'class' => 'mod-sharedresource-classif-ctl');
    $editvaluesicon = html_writer::tag('img', '', $attrs);

    foreach ($classifications as $classif) {
        $navigation = new \local_sharedresources\browser\navigation($classif);
        $data = array();
        $data[] = $classif->name;
        $data[] = $navigation->count_taxons();
        $data[] = $classif->tablename;
        $data[] = $classif->sqlrestriction;
        $taxoncount = count(explode(',', $classif->taxonselection));
        $data[] = (!empty($classif->taxonselection)) ? $filteredicon.' ('.$taxoncount.')' : '';

        $enabledicon = $classif->enabled ? $visibleicon : $hiddenicon;
        $showstr = $classif->enabled ? 'hideclassification' : 'showclassification';
        $what = $classif->enabled ? 'disable' : 'enable';
        $params = array('id' => $classif->id, 'what' => $what);
        $editurl = new moodle_url('/mod/sharedresource/classifications.php', $params);
        $attrs = array('alt' => get_string($showstr, 'sharedresource'),
                       'title' => get_string($showstr, 'sharedresource'));
        $cmds = html_writer::link($editurl, $enabledicon, $attrs);

        $params = array('id' => $classif->id);
        $editurl = new moodle_url('/mod/sharedresource/classification.php', $params);
        $attrs = array('alt' => get_string('updateclassification', 'sharedresource'),
                       'title' => get_string('updateclassification', 'sharedresource'));
        $cmds .= '&nbsp;'.html_writer::link($editurl, $editicon, $attrs);

        $params = array('what' => 'delete', 'id' => $classif->id);
        $deleteurl = new moodle_url('/mod/sharedresource/classifications.php', $params);
        $attrs = array('alt' => get_string('delete', 'sharedresource'),
                       'title' => get_string('delete', 'sharedresource'));
        $cmds .= '&nbsp;'.html_writer::link($deleteurl, $deleteicon, $attrs);

        $params = array('id' => $classif->id);
        $editvaluesurl = new moodle_url('/mod/sharedresource/classificationvalues.php', $params);
        $attrs = array('alt' => get_string('editclassificationtable', 'sharedresource'),
                       'title' => get_string('editclassificationtable', 'sharedresource'));
        $cmds .= '&nbsp;'.html_writer::link($editvaluesurl, $editvaluesicon, $attrs);

        if (mod_sharedresource_supports_feature('taxonomy/fineselect')) {
            $params = array('id' => $classif->id);
            $selecttaxonsurl = new moodle_url('/mod/sharedresource/pro/classificationtaxonselect.php', $params);
            $attrs = array('alt' => get_string('selecttaxons', 'sharedresource'),
                           'title' => get_string('selecttaxons', 'sharedresource'));
            $cmds .= '&nbsp;'.html_writer::link($selecttaxonsurl, $filteredicon, $attrs);
        }

        if (mod_sharedresource_supports_feature('taxonomy/accessctl')) {
            $params = array('classificationid' => $classif->id);
            $label = get_string('classificationacls', 'sharedresource');
            $aclurl = new moodle_url('/mod/sharedresource/pro/classificationacls.php', $params);
            $attrs = array('alt' => $label, 'title' => $label);
            if (empty($classif->accessctl)) {
                $attrs = array('src' => $OUTPUT->pix_url('i/permissions'), 'class' => 'mod-sharedresource-classif-ctl');
                $aclicon = html_writer::tag('img', '', $attrs);
            } else {
                $attrs = array('src' => $OUTPUT->pix_url('i/permissionlock'), 'class' => 'mod-sharedresource-classif-ctl');
                $aclicon = html_writer::tag('img', '', $attrs);
            }
            $cmds .= '&nbsp;'.html_writer::link($aclurl, $aclicon, $attrs);
        }

        $data[] = $cmds;
        $table->data[] = $data;
    }

    echo html_writer::table($table);
}

echo '<div style="text-align:right">';
$addurl = new moodle_url('/mod/sharedresource/classification.php');
echo $OUTPUT->single_button($addurl, get_string('addclassification', 'sharedresource'));
echo '</div>';

$label = get_string('backadminpage','sharedresource');
$buttonurl = new moodle_url('/admin/settings.php', array('section' => 'modsettingsharedresource'));
echo $OUTPUT->single_button($buttonurl, $label);

echo $OUTPUT->footer();
