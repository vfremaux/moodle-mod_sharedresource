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
 * @author  Valery Fremaux
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package    sharedresource
 * @subpackage mod_sharedresource
 * @category   mod
 *
 * Allows editing taxons in the taxonomy source table.
 * In first approach, is limited to the sharedresource_taxonomy internal table.
 *
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/local/sharedresources/classes/navigator.class.php');

$id = optional_param('id', 0, PARAM_TEXT); // The classification id.
$parent = optional_param('parent', 0, PARAM_TEXT); // The parent id.
$action = optional_param('what', '', PARAM_TEXT); // The controller action.

// Security.

$systemcontext = context_system::instance();
require_login();
require_capability('repository/sharedresources:manage', $systemcontext);

// Build page.

$url = new moodle_url('/mod/sharedresource/classificationvalues.php', array('id' => $id, 'parent' => $parent));
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');

if (!empty($action)) {
    include($CFG->dirroot.'/mod/sharedresource/classificationvalues.controller.php');
    $controller = new \mod_sharedresource\classification\classificationvalues_controller();
    $controller->receive($action);
    $controller->process($action);
    redirect(new moodle_url('/mod/sharedresource/classificationvalues.php', array('id' => $id, 'parent' => $parent)));
}

$classification = $DB->get_record('sharedresource_classif', array('id' => $id));

if ($parent) {
    $parenttoken = $DB->get_record('sharedresource_taxonomy', array('id' => $parent));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('classificationvalues', 'sharedresource'));
echo $OUTPUT->heading(get_string('classification', 'sharedresource').' '.format_string($classification->name), 3);

if ($classification->tablename != 'sharedresource_taxonomy') {
    echo $OUTPUT->notification(get_string('notsupportedyet', 'sharedresource'));
} else {

    $navigation = new \local_sharedresources\browser\navigation($classification);

    if ($parent == 0) {
        $params = array('classificationid' => $id, 'parent' => 0);
        $tokens = $DB->get_records('sharedresource_taxonomy', $params, 'sortorder');
    } else {
        $tokens = $navigation->get_children($parent);
    }

    $table = new html_table();

    $tokenstr = get_string('token', 'sharedresource');

    $table->head = array($tokenstr, '');
    $table->width = '100%';
    $table->size = array('70%', '30%');
    $table->align = array('left', 'right');

    $attrs = array('class' => 'mod-sharedresource-tokens-ctl');
    $editicon = $OUTPUT->pix_icon('t/edit', get_string('edit'), 'core', $attrs);
    $attrs = array('class' => 'mod-sharedresource-tokens-ctl');
    $deleteicon = $OUTPUT->pix_icon('t/delete', get_string('delete'), 'core', $attrs);
    $attrs = array('class' => 'mod-sharedresource-tokens-ctl');
    $upicon = $OUTPUT->pix_icon('t/up', '', 'core', $attrs);
    $attrs = array('class' => 'mod-sharedresource-tokens-ctl');
    $downicon = $OUTPUT->pix_icon('t/down', '', 'core', $attrs);

    foreach ($tokens as $tk) {
        $data = array();
        $params = array('parent' => $tk->id, 'id' => $id);
        $suburl = new moodle_url('/mod/sharedresource/classificationvalues.php', $params);
        $tokenstr = '';
        if (!empty($tk->idnumber)) {
            $tokenstr .= '['.$tk->idnumber.']';
        }
        $tokenstr .= '<a href="'.$suburl.'">'.$tk->value.'</a> ';
        $children = count($navigation->get_children($tk->id));
        if ($children) {
            $tokenstr .= '('.$children.')';
        }
        $data[] = $tokenstr;

        $cmds = '';

        $params = array('classificationid' => $id, 'id' => $tk->id, 'parent' => $parent);
        $editurl = new moodle_url('/mod/sharedresource/classificationvalue.php', $params);
        $attrs = array('alt' => get_string('update'),
                       'title' => get_string('update'));
        $cmds .= '&nbsp;'.html_writer::link($editurl, $editicon, $attrs);

        $params = array('what' => 'delete', 'tokenid' => $tk->id, 'id' => $id, 'parent' => $parent, 'sesskey' => sesskey());
        $deleteurl = new moodle_url('/mod/sharedresource/classificationvalues.php', $params);
        $attrs = array('alt' => get_string('delete', 'sharedresource'),
                       'title' => get_string('delete', 'sharedresource'));
        $cmds .= '&nbsp;'.html_writer::link($deleteurl, $deleteicon, $attrs);

        if ($tk->sortorder > 1) {
            $params = array('what' => 'up', 'tokenid' => $tk->id, 'id' => $id, 'parent' => $parent, 'sesskey' => sesskey());
            $upurl = new moodle_url('/mod/sharedresource/classificationvalues.php', $params);
            $attrs = array('alt' => get_string('up', 'sharedresource'),
                           'title' => get_string('up', 'sharedresource'));
            $cmds .= '&nbsp;'.html_writer::link($upurl, $upicon, $attrs);
        }

        $select = " parent = ?  AND classificationid = ? ";
        $params = array($tk->parent, $id);
        $maxsortorder = $DB->get_field_select('sharedresource_taxonomy', " MAX(sortorder) ", $select, $params);

        if ($tk->sortorder < $maxsortorder) {
            $params = array('what' => 'down', 'tokenid' => $tk->id, 'id' => $id, 'parent' => $parent, 'sesskey' => sesskey());
            $downurl = new moodle_url('/mod/sharedresource/classificationvalues.php', $params);
            $attrs = array('alt' => get_string('down', 'sharedresource'),
                           'title' => get_string('down', 'sharedresource'));
            $cmds .= '&nbsp;'.html_writer::link($downurl, $downicon, $attrs);
        }

        $data[] = $cmds;
        $table->data[] = $data;
    }

    if ($parent) {
        echo '<div class="parent-link">';
        $params = array('id' => $id, 'parent' => $parenttoken->parent);
        $upurl = new moodle_url('/mod/sharedresource/classificationvalues.php', $params);
        echo html_writer::tag('a', get_string('goup', 'sharedresource'), array('href' => $upurl));
        echo '</div>';
    }

    if (!empty($parent)) {
        echo '<div class="navigation-up-path">';
        echo $navigation->get_printable_taxon_path($parent);
        echo '</div>';
    }

    echo html_writer::table($table);

    echo '<div style="text-align:right">';
    $params = array('classificationid' => $id, 'parent' => $parent);
    $addurl = new moodle_url('/mod/sharedresource/classificationvalue.php', $params);
    echo $OUTPUT->single_button($addurl, get_string('addtoken', 'sharedresource'));
    echo '</div>';
}

$label = get_string('backtoclassifications','sharedresource');
$buttonurl = new moodle_url('/mod/sharedresource/classifications.php');
echo $OUTPUT->single_button($buttonurl, $label);

echo $OUTPUT->footer();
