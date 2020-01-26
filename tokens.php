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
 * Provides a backoffice administration page for managing classification tokens
 * in a classification source.
 *
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/ddllib.php');

$id = optional_param('id', 0, PARAM_TEXT); // Classification instance.
$action = optional_param('what', 0, PARAM_ALPHA);

// Security.

$systemcontext = context_system::instance();
require_login();
require_capability('repository/sharedresources:manage', $systemcontext);

// Build page.

$url = new moodle_url('/mod/sharedresource/tokens.php', array('id' => $id));
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('classificationconfiguration', 'sharedresource'));

if (!empty($action)) {
    include($CFG->dirroot.'/mod/sharedresource/tokens.controller.php');
    $controller = new \mod_sharedresource\classification\tokens_controller();
    $controller->receive($action);
    $controller->process($action);
}

$taxonomy = $DB->get_record('sharedresource_classif', array('id' => $id));

$navigator = \local_sharedresources\browser\navigation($taxonomy);
$tokentree = $navigator->get_full_tree();

if (empty($tokentree)) {
    echo '<center>'.get_string('noclassification','sharedresource').'</center>';
} else {

    $table = new html_table();

    $parentstr = get_string('parent');
    $labelstr = get_string('tablename', 'sharedresource');

    $table->head = array($parentstr, $labelstr, '');
    $table->width = '100%';
    $table->size = array('50%', '40%', '10%');
    $table->align = array('left', 'left', 'right');

    $attrs = array('class' => 'mod-sharedresource-classif-ctl');
    $upicon = $OUTPUT->pix_icon('t/up', '', 'core', $attrs);

    $attrs = array('class' => 'mod-sharedresource-classif-ctl shadowed');
    $upicondisabled = $OUTPUT->pix_icon('t/up', '', 'core', $attrs);

    $attrs = array('class' => 'mod-sharedresource-classif-ctl');
    $downicon = $OUTPUT->pix_icon('t/down', '', 'core', $attrs);

    $attrs = array('class' => 'mod-sharedresource-classif-ctl shadowed');
    $downicondisabled = $OUTPUT->pix_icon('t/down', '', 'core', $attrs);

    $attrs = array('class' => 'mod-sharedresource-classif-ctl');
    $editicon = $OUTPUT->pix_icon('t/edit', get_string('edit'), 'core', $attrs);

    $attrs = array('class' => 'mod-sharedresource-classif-ctl');
    $deleteicon = $OUTPUT->pix_icon('t/delete', get_sting('delete'), 'core', $attrs);

    foreach ($tokens as $token) {
        $data = array();
        $data[] = $classif->name;
        $data[] = $classif->tablename;
        $data[] = $classif->sqlrestriction;
        $taxoncount = count(explode(',', $classif->taxonselection));
        $data[] = (!empty($classif->taxonselection)) ? $filteredicon.' ('.$taxoncount.')' : '';

        // Down and up have inverted semantic related to sortordering.
        if ($token->sortorder > $classification->sqlsortorderstart) {
            $params = array('id' => $classif->id, 'what' => 'up');
            $upurl = new moodle_url('/mod/sharedresource/tokens.php', $params);
            $cmds = html_writer::link($upurl, $upicon, $attrs);
        } else {
            $cmds = $upicondisabled;
        }

        if ($token->sortorder < $maxclassiforder) {
            $params = array('id' => $classif->id, 'what' => 'down');
            $downurl = new moodle_url('/mod/sharedresource/tokens.php', $params);
            $cmds = html_writer::link($downurl, $downicon, $attrs);
        } else {
            $cmds = $downicondisabled;
        }

        $params = array('id' => $classif->id);
        $editurl = new moodle_url('/mod/sharedresource/token.php', $params);
        $attrs = array('alt' => get_string('updatetoken', 'sharedresource'),
                       'title' => get_string('updatetoken', 'sharedresource'));
        $cmds .= '&nbsp;'.html_writer::link($editurl, $editicon, $attrs);

        $params = array('what' => 'delete', 'id' => $classif->id);
        $deleteurl = new moodle_url('/mod/sharedresource/tokens.php', $params);
        $attrs = array('alt' => get_string('delete', 'sharedresource'),
                       'title' => get_string('delete', 'sharedresource'));
        $cmds .= '&nbsp;'.html_writer::link($deleteurl, $deleteicon, $attrs);

        $data[] = $cmds;
        $table->data[] = $data;
    }

    echo html_writer::table($table);

    echo '<div style="text-align:right">';
    $addurl = new moodle_url('/mod/sharedresource/token.php');
    echo $OUTPUT->single_button($addurl, get_string('addtoken', 'sharedresource'));
    echo '</div>';
}

$label = get_string('backadminpage','sharedresource');
$buttonurl = new moodle_url('/admin/settings.php', array('section' => 'modsettingsharedresource'));
echo $OUTPUT->single_button($buttonurl, $label);

echo $OUTPUT->footer();
