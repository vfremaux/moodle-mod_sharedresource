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
 * @author  Valery Fremaux  valery.fremaux@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package mod_sharedresource
 * @category mod
 */
require('../../config.php');
require_once($CFG->dirroot."/mod/sharedresource/lib.php");
require_once($CFG->dirroot."/mod/sharedresource/classes/sharedresource_plugin_base.class.php");

$identifier = required_param('resid', PARAM_RAW);
$navlinks[] = array('name' => get_string('remotesubmission', 'sharedresource'),
                    'url' => '',
                    'type' => 'title');

$PAGE->set_title(get_string('remotesubmission', 'sharedresource'));
$PAGE->set_heading($COURSE->fullname);
$PAGE->set_pagelayout('embedded');

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('remotesubmission', 'sharedresource'));
$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_INT);
$identifier = required_param('resid', PARAM_RAW);
$repos = optional_param('repos', '', PARAM_TEXT);

if ($confirm == 1) {
    $shrentry =  $DB->get_record('sharedresource_entry', array('identifier' => $identifier));
    $plugins = sharedresource_get_plugins();
    foreach ($plugins as $plugin) {
        $pluginclass = get_class($plugin);
        preg_match('/sharedresource_plugin_(.*)/', $pluginclass, $matches); 
        $pluginname = $matches[1];
        if (!empty($repos) && !preg_match("/\\b$pluginname\\b/", $repos)) {
            continue;
        }
        if ($plugin->remotesubmit($shrentry)) {
            redirect(new moodle_url('/course/view.php', array('id' => $id, 'action' => 'remoteindex');
        } else {
            print_error('errornnoticecreation', 'sharedresource');
        }
    }
} else {
    if ($confirm === 0) {
        redirect(new moodle_url('/course/view.php', array('id' => $id, 'action' => 'remoteindex');
    } else {
        $options['id'] = $id;
        $options['resid'] = $identifier;
        $options['confirm'] = 1;
        $options['repos'] = $repos;
        echo $OUTPUT->single_button(new moodle_url('/mod/sharedresource/remotesubmit.php', $options), get_string('confirm'), 'get');
        $canceloptions['id'] = $id;
        $canceloptions['action'] = 'remoteindex';
        echo $OUTPUT->single_button(new moodle_url('/course/view.php', $canceloptions), get_string('cancel'), 'get');
    }
}
