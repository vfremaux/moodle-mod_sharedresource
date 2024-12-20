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
 * Display the admin part of the classification configuration.
 *
 * @package     mod_sharedresource
 * @author      Frederic GUILLOU, Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/*
 * You can add, delete or apply a restriction
 * on a classification, or configure a specific classification
 * by accessing another page
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

$PAGE->requires->js_call_amd('mod_sharedresource/classifications', 'init');

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
    echo '<center>'.get_string('noclassification', 'sharedresource').'</center>';
} else {

    $table = new html_table();

    $namestr = get_string('name');
    $taxonsstr = get_string('taxons', 'sharedresource');
    $tablestr = get_string('tablename', 'sharedresource');
    $sqlrestrictionsstr = get_string('sqlrestriction', 'sharedresource');
    $taxonselectionstr = get_string('taxonselection', 'sharedresource');

    $table->head = [$namestr, $taxonsstr, $tablestr, $sqlrestrictionsstr, $taxonselectionstr, ''];
    $table->width = '100%';
    $table->size = ['30%', '10%', '20%', '20%', '10%', '10%'];
    $table->align = ['left', 'center', 'left', 'center', 'center', 'right'];

    $attrs = ['class' => 'mod-sharedresource-classif-ctl'];
    $hiddenicon = $OUTPUT->pix_icon('t/show', get_string('hide'), 'core', $attrs);

    $attrs = ['class' => 'mod-sharedresource-classif-ctl'];
    $visibleicon = $OUTPUT->pix_icon('t/hide', get_string('show'), 'core', $attrs);

    $attrs = ['class' => 'mod-sharedresource-classif-ctl'];
    $editicon = $OUTPUT->pix_icon('t/edit', get_string('edit'), 'core', $attrs);

    $attrs = ['class' => 'mod-sharedresource-classif-ctl'];
    $deleteicon = $OUTPUT->pix_icon('t/delete', get_string('delete'), 'core', $attrs);

    $filteredicon = $OUTPUT->pix_icon('i/filter', '', 'core');

    $attrs = ['class' => 'mod-sharedresource-classif-ctl'];
    $editvaluesicon = $OUTPUT->pix_icon('i/withsubcat', '', 'core', $attrs);

    foreach ($classifications as $classif) {
        $navigation = new \local_sharedresources\browser\navigation($classif);
        $data = [];
        $data[] = $classif->name;
        $data[] = $navigation->count_taxons();
        $data[] = $classif->tablename;
        $data[] = $classif->sqlrestriction;
        // Storage form adds one.
        $taxoncount = count(explode(',', $classif->taxonselection)) - 1;
        $data[] = (!empty($classif->taxonselection)) ? $filteredicon.' ('.$taxoncount.')' : '';

        $enabledicon = $classif->enabled ? $visibleicon : $hiddenicon;
        $showstr = $classif->enabled ? 'hideclassification' : 'showclassification';
        $what = $classif->enabled ? 'disable' : 'enable';
        $params = ['id' => $classif->id, 'what' => $what];
        $editurl = new moodle_url('/mod/sharedresource/classifications.php', $params);
        $attrs = ['alt' => get_string($showstr, 'sharedresource'),
                       'title' => get_string($showstr, 'sharedresource')];
        $cmds = html_writer::link($editurl, $enabledicon, $attrs);

        $params = ['id' => $classif->id];
        $editurl = new moodle_url('/mod/sharedresource/classification.php', $params);
        $attrs = ['alt' => get_string('updateclassification', 'sharedresource'),
                       'title' => get_string('updateclassification', 'sharedresource')];
        $cmds .= '&nbsp;'.html_writer::link($editurl, $editicon, $attrs);

        if (has_capability('mod/sharedresource:manageclassifications', $systemcontext)) {
            $params = ['what' => 'delete', 'id' => $classif->id];
            $deleteurl = new moodle_url('/mod/sharedresource/classifications.php', $params);
            $attrs = ['alt' => get_string('delete', 'sharedresource'),
                           'title' => get_string('delete', 'sharedresource'),
                           'class' => 'sharedresource-delete-classification'];
            $cmds .= '&nbsp;'.html_writer::link($deleteurl, $deleteicon, $attrs);
        }

        if (has_capability('mod/sharedresource:manageclassificationtokens', $systemcontext)) {
            $params = ['id' => $classif->id];
            $editvaluesurl = new moodle_url('/mod/sharedresource/classificationvalues.php', $params);
            $attrs = ['alt' => get_string('editclassificationtable', 'sharedresource'),
                           'title' => get_string('editclassificationtable', 'sharedresource')];
            $cmds .= '&nbsp;'.html_writer::link($editvaluesurl, $editvaluesicon, $attrs);

            if (sharedresource_supports_feature('taxonomy/fineselect')) {
                $params = ['id' => $classif->id];
                $selecttaxonsurl = new moodle_url('/mod/sharedresource/pro/classificationtaxonselect.php', $params);
                $attrs = ['alt' => get_string('selecttaxons', 'sharedresource'),
                               'title' => get_string('selecttaxons', 'sharedresource')];
                $cmds .= '&nbsp;'.html_writer::link($selecttaxonsurl, $filteredicon, $attrs);
            }
        }

        if (sharedresource_supports_feature('taxonomy/accessctl')) {
            $params = ['classificationid' => $classif->id];
            $label = get_string('classificationacls', 'sharedresource');
            $aclurl = new moodle_url('/mod/sharedresource/pro/classificationacls.php', $params);
            $attrs = ['alt' => $label, 'title' => $label];
            if (empty($classif->accessctl)) {
                $attrs = ['class' => 'mod-sharedresource-classif-ctl'];
                $aclicon = $OUTPUT->pix_icon('i/permissions', '', 'core', $attrs);
            } else {
                $attrs = ['class' => 'mod-sharedresource-classif-ctl'];
                $aclicon = $OUTPUT->pix_icon('i/permissionlock', '', 'core', $attrs);
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

$label = get_string('backadminpage', 'sharedresource');
$buttonurl = new moodle_url('/admin/settings.php', ['section' => 'modsettingsharedresource']);
echo $OUTPUT->single_button($buttonurl, $label);

echo $OUTPUT->footer();
