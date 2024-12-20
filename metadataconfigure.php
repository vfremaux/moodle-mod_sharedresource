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
 * This is a separate configuration screen to configure any metadata stub that is attached to a shared resource.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/sharedresources/classes/search_widget.class.php');

$config = get_config('sharedresource');

// DO not rely on moodle classloader.
if ($searchplugins = glob($CFG->dirroot.'/local/sharedresources/classes/searchwidgets/*')) {
    foreach ($searchplugins as $sp) {
        include_once($sp);
    }
}

$url = new moodle_url('/mod/sharedresource/metadataconfigure.php');
$PAGE->set_url($url);

// Security.

require_login();
$systemcontext = context_system::instance();
require_capability('repository/sharedresources:manage', $systemcontext);

$action = optional_param('action', null, PARAM_ALPHA);
$strtitle = get_string('metadata_configure', 'sharedresource');
$PAGE->set_pagelayout('standard');
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($strtitle, 'metadataconfigure.php', 'misc');
$PAGE->requires->js_call_amd('mod_sharedresource/metadataform', 'init', [$config->schema]);

$renderer = $PAGE->get_renderer('mod_sharedresource', 'metadata');

if (empty($config->schema)) {
    set_config('schema', 'lom', 'sharedresource');
    $config->schema = 'lom';
    purge_all_caches();
}

$plugin = sharedresource_get_plugin($config->schema);
$namespace = $plugin->pluginname;
$config = [];
if ($currentconfig = $DB->get_records_sql(' SELECT * from {config_plugins} WHERE  plugin = "sharedmetadata_'.$namespace.'"')) {
    foreach ($currentconfig as $conf) {
        $config[$conf->name] = $conf->value;
    }
}

// Load configuration from hard coded defaults.
if ($config == [] || $action == 'reinitialize') {
    foreach ($plugin->metadatatree as $key => $value) {
        if ($key != 0) {
            if ((@$value['checked']['system'] == 1) || ($value['checked']['system_write'] == 1)) {
                $config['config_'.$namespace.'_system_write_'.$key.''] = 1;
                set_config('config_'.$namespace.'_system_write_'.$key.'', 1, 'sharedmetadata_'.$namespace);
            }
            if ((@$value['checked']['system'] == 1) || ($value['checked']['system_read'] == 1)) {
                $config['config_'.$namespace.'_system_read_'.$key.''] = 1;
                set_config('config_'.$namespace.'_system_read_'.$key.'', 1, 'sharedmetadata_'.$namespace);
            }
            if ((@$value['checked']['indexer'] == 1) || ($value['checked']['indexer_write'] == 1)) {
                $config['config_'.$namespace.'_indexer_write_'.$key.''] = 1;
                set_config('config_'.$namespace.'_indexer_write_'.$key.'', 1, 'sharedmetadata_'.$namespace);
            }
            if ((@$value['checked']['indexer'] == 1) || ($value['checked']['indexer_read'] == 1)) {
                $config['config_'.$namespace.'_indexer_read_'.$key.''] = 1;
                set_config('config_'.$namespace.'_indexer_read_'.$key.'', 1, 'sharedmetadata_'.$namespace);
            }
            if ((@$value['checked']['author'] == 1) || ($value['checked']['author_write'] == 1)) {
                $config['config_'.$namespace.'_author_write_'.$key.''] = 1;
                set_config('config_'.$namespace.'_author_write_'.$key.'', 1, 'sharedmetadata_'.$namespace);
            }
            if ((@$value['checked']['author'] == 1) || ($value['checked']['author_read'] == 1)) {
                $config['config_'.$namespace.'_author_read_'.$key.''] = 1;
                set_config('config_'.$namespace.'_author_read_'.$key.'', 1, 'sharedmetadata_'.$namespace);
            }
            set_config('activewidgets', serialize([]), 'sharedresource');
        }
    }
}

// Get actual configuration data from form.
if ($data = data_submitted()) {

    $DB->execute('delete from {config_plugins} where plugin = "sharedmetadata_'.$namespace.'"');

    $activewidgets = [];

    foreach ($data as $key => $value) {
        if (preg_match('/config_(\w+?)_/', $key, $matches)) {
            $pluginname = $matches[1];
            set_config($key, $value, 'sharedmetadata_'.$pluginname);
        } else if (preg_match('/widget_(\w+?)_()/', $key, $matches)) {
            $idwidget = substr($key, strlen($matches[0]));
            $wtype = $plugin->metadatatree[$idwidget]['widget'];
            $classname = '\\local_sharedresources\\search\\'.$wtype.'_widget';
            $widget = new $classname($idwidget, $plugin->metadatatree[$idwidget]['name'], $wtype);
            array_push($activewidgets, $widget);
        }
    }

    set_config('activewidgets', serialize($activewidgets), 'sharedresource');
    redirect(new moodle_url('/mod/sharedresource/metadataconfigure.php'), get_string('datachanged', 'sharedresource'), 2);
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('metadataconfiguration', 'sharedresource'));

echo $renderer->metadata_configuration();

echo $OUTPUT->footer();
