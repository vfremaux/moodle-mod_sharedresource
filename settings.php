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

defined('MOODLE_INTERNAL') || die();

/**
 * @author  Piers Harding  piers@catalyst.net.nz
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package mod_sharedresource
 * @category
 */
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');

global $SHAREDRESOURCE_WINDOW_OPTIONS; // make sure we have the pesky global

// configuration for rss feed
if (empty($CFG->enablerssfeeds)) {
    $options = array(0 => get_string('rssglobaldisabled', 'admin'));
    $str = get_string('configenablerssfeeds', 'sharedresource').'<br />'.get_string('configenablerssfeedsdisabled2', 'admin');
} else {
    $options = array(0 => get_string('no'), 1=>get_string('yes'));
    $str = get_string('configenablerssfeeds', 'sharedresource');
}

$settings->add(new admin_setting_configselect('sharedresource_enablerssfeeds', get_string('enablerssfeeds', 'admin'),
                   $str, 0, $options));

$settings->add(new admin_setting_configtext('sharedresource_article_quantity', get_string('articlequantity', 'sharedresource'),
                   get_string('configarticlequantity', 'sharedresource'), 10, PARAM_INT));

$checkedyesno = array('' => get_string('no'), 'checked' => get_string('yes')); // not nice at all

$settings->add(new admin_setting_configcheckbox('sharedresource_freeze_index', get_string('freeze_index', 'sharedresource'),
                   get_string('config_freeze_index', 'sharedresource'), '0'));

$settings->add(new admin_setting_configcheckbox('sharedresource_backup_index', get_string('backup_index', 'sharedresource'),
                   get_string('config_backup_index', 'sharedresource'), '0'));

$settings->add(new admin_setting_configcheckbox('sharedresource_restore_index', get_string('restore_index', 'sharedresource'),
                   get_string('config_restore_index', 'sharedresource'), '0'));

$settings->add(new admin_setting_configtext('sharedresource_framesize', get_string('framesize', 'sharedresource'),
                   get_string('configframesize', 'sharedresource'), 130, PARAM_INT));

$settings->add(new admin_setting_configtext('sharedresource_defaulturl', get_string('resourcedefaulturl', 'sharedresource'),
                   get_string('configdefaulturl', 'sharedresource'), 'http://'));

$settings->add(new admin_setting_configtext('sharedresource_foreignurl', get_string('resourceaccessurlasforeign', 'sharedresource'),
                   get_string('configforeignurlsheme', 'sharedresource'), ''), PARAM_RAW, 80);

$woptions = array('' => get_string('newwindow', 'sharedresource'), 'checked' => get_string('pagewindow', 'sharedresource'));
$settings->add(new admin_setting_configselect('sharedresource_popup', get_string('display', 'sharedresource'),
                   get_string('configpopup', 'sharedresource'), '', $woptions));

foreach ($SHAREDRESOURCE_WINDOW_OPTIONS as $optionname) {
    $popupoption = "sharedresource_popup$optionname";
    if ($popupoption == 'sharedresource_popupheight') {
        $settings->add(new admin_setting_configtext('sharedresource_popupheight', get_string('newheight', 'sharedresource'),
                           get_string('configpopupheight', 'sharedresource'), 600, PARAM_INT));
    } else if ($popupoption == 'sharedresource_popupwidth') {
        $settings->add(new admin_setting_configtext('sharedresource_popupwidth', get_string('newwidth', 'sharedresource'),
                           get_string('configpopupwidth', 'sharedresource'), 800, PARAM_INT));
    } else {
        $settings->add(new admin_setting_configselect($popupoption, get_string('new'.$optionname, 'sharedresource'),
                           get_string('configpopup'.$optionname, 'sharedresource'), 'checked', $checkedyesno));
    }
}

/// get plugins list for enabling/disabling 

$pluginscontrolstr = get_string('pluginscontrol', 'sharedresource');
$pluginscontrolinfostr = get_string('pluginscontrolinfo', 'sharedresource');
$settings->add(new admin_setting_heading('plugincontrol', $pluginscontrolstr, $pluginscontrolinfostr));

$checkedoptions = array(0 => get_string('no'), 1 => get_string('yes'));

$pluginsoptions['0'] = get_string('noplugin', 'sharedresource');
$sharedresourcesplugins = core_component::get_plugin_list('sharedmetadata');
foreach($sharedresourcesplugins as $p => $ppath){
    $pluginsoptions[$p] = get_string('pluginname', 'sharedmetadata_'.$p);
}
$item = new admin_setting_configselect('pluginchoice', get_string('pluginchoice', 'sharedresource'), get_string('basispluginchoice', 'sharedresource'), @$CFG->pluginchoice, $pluginsoptions);
if (empty($CFG->running_installer)) {
    $item->set_updatedcallback('redirectmetadata');
}
$settings->add($item);

if (!function_exists('redirectmetadata')) {

    function redirectmetadata(){
        global $CFG;
        redirect(new moodle_url('/mod/sharedresource/metadataconfigure.php', array('action' => 'reinitialize')));
    }

}

/*was used to configure each plugin, not used anymore because admin have to choose only one plugin
foreach($sharedresourcesplugins as $plugin){
    $pluginkey = "sharedresource_plugin_hide_{$plugin}";
    $settings->add(new admin_setting_configselect($pluginkey, get_string('plugin_'.$plugin, 'sharedresource'),
                       get_string($pluginkey, 'sharedresource'), 0, $checkedoptions));
}*/

$settings->add(new admin_setting_heading('metadataconfig', get_string('metadataconfiguration', 'sharedresource'),
                   get_string('medatadaconfigurationdesc', 'sharedresource', $CFG->wwwroot.'/mod/sharedresource/metadataconfigure.php')));

$settings->add(new admin_setting_heading('classificationconfig', get_string('classificationconfiguration', 'sharedresource'),
                   get_string('classificationconfigurationdesc', 'sharedresource', $CFG->wwwroot.'/mod/sharedresource/classificationconfigure.php')));
