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
 * @author  Valery Fremaux valery.fremaux@club-internet.fr
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/taoresource is a work derived from Moodle mod/resoruce
 *
 * This is a separate configuration screen to configure any metadata stub that is attached to a shared resource. 
 * 
 * @package sharedresource
 *
 */
require_once("../../config.php");
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->libdir.'/formslib.php');
require_once $CFG->dirroot.'/mod/sharedresource/search_widget.class.php';
$PAGE->requires->js('/mod/sharedresource/js/mtdform.js');
$action = optional_param('action',null ,PARAM_ALPHA);
$system_context = context_system::instance();
$strtitle = get_string('metadata_configure', 'sharedresource');
$PAGE->set_pagelayout('standard');
$PAGE->set_context($system_context);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($strtitle,'metadataconfigure.php','misc');
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');
$url = new moodle_url('/mod/sharedresource/metadataconfigure.php');
$PAGE->set_url($url);

$plugins = sharedresource_get_plugins();
if (empty($plugins)){
    print_error('errornometadataplugins', 'sharedresource');
}

if (empty($CFG->pluginchoice)){
    set_config('pluginchoice', 'lom');
    purge_all_caches();
}

$plugin = $plugins[$CFG->pluginchoice];
$name = $plugin->pluginname;
$config = array();
if ($currentconfig = $DB->get_records_sql(' SELECT * from {config_plugins} WHERE  plugin LIKE "%sharedresource_'.$name.'%"')){
    foreach($currentconfig as $conf){
        $config[$conf->name] = $conf->value;
    }
}
if ($config == array() || $action == 'reinitialize') {
    foreach ($plugin->METADATATREE as $key => $value) {
        if ($key != 0) {
            if($value['checked']['system'] == 1) {
                $config['config_'.$name.'_system_'.$key.''] = 1;
                set_config('config_'.$name.'_system_'.$key.'', 1, 'sharedresource_'.$name);
            }
            if($value['checked']['indexer'] == 1) {
                $config['config_'.$name.'_indexer_'.$key.''] = 1;
                set_config('config_'.$name.'_indexer_'.$key.'', 1, 'sharedresource_'.$name);
            }
            if($value['checked']['author'] == 1) {
                $config['config_'.$name.'_author_'.$key.''] = 1;
                set_config('config_'.$name.'_author_'.$key.'', 1, 'sharedresource_'.$name);
            }
            set_config('activewidgets',serialize(array()));
        }
    }
}
if ($data = data_submitted()) {
    $DB->execute('delete from {config_plugins} where plugin LIKE "%sharedresource_'.$name.'%"');
    $activewidgets = array();
    foreach ($data as $key => $value) {
        if (preg_match('/config_(\w+?)_/', $key, $matches)) {
            $pluginname = $matches[1];
            set_config($key, $value, 'sharedresource_'.$pluginname);
        } elseif (preg_match('/widget_(\w+?)_()/', $key, $matches)) {
            $idwidget = substr($key, strlen($matches[0]));
            $wtype = $plugin->METADATATREE[$idwidget]['widget'];
            $classname = "{$wtype}_search_widget";
            require_once($CFG->dirroot."/mod/sharedresource/searchwidgets/{$wtype}_search_widget.class.php");
            $widget = new $classname($matches[1], $idwidget, $plugin->METADATATREE[$idwidget]['name'], $wtype);
            array_push($activewidgets, $widget);
        }
    }
    set_config('activewidgets',serialize($activewidgets));
    redirect($CFG->wwwroot.'/mod/sharedresource/metadataconfigure.php', get_string('datachanged', 'sharedresource'), 2);
}

echo $OUTPUT->header();

$OUTPUT->heading(get_string('metadataconfiguration', 'sharedresource'));
echo '<form name="mtdconfigurationform" method="post">';
echo '<fieldset>';
if (!empty($plugin)) {
    $plugin->configure($config);
} else {
    print_string('nometadataplugin', 'sharedresource');
}
echo '</fieldset><br/>';
echo '<center><br/><a href="metadataconfigure.php?action=reinitialize"><input type="button" value="'.get_string('defaultselect', 'sharedresource').'" /></a>&nbsp;&nbsp;<input type="submit" value="'.get_string('updatemetadata', 'sharedresource').'" /></center><br/>';
echo '<center><hr><br/><input type="button" value="'.get_string('backadminpage','sharedresource').'" OnClick="window.location.href=\''.$CFG->wwwroot.'/admin/settings.php?section=modsettingsharedresource\'"/></center><br/>';
echo '</form>';
echo $OUTPUT->footer();
