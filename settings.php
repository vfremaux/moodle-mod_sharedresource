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
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived
 * from Moodle mod/resource
 * @package sharedresource
 */

require_once($CFG->dirroot.'/mod/sharedresource/lib.php');

<<<<<<< HEAD
global $SHAREDRESOURCE_WINDOW_OPTIONS; // make sure we have the pesky global

// configuration for rss feed
=======
global $SHAREDRESOURCE_WINDOW_OPTIONS; // Make sure we have the pesky global.

// Configuration for rss feed.
>>>>>>> MOODLE_32_STABLE
if (empty($CFG->enablerssfeeds)) {
    $options = array(0 => get_string('rssglobaldisabled', 'admin'));
    $str = get_string('configenablerssfeeds', 'sharedresource').'<br />'.get_string('configenablerssfeedsdisabled2', 'admin');
} else {
    $options = array(0 => get_string('no'), 1=>get_string('yes'));
    $str = get_string('configenablerssfeeds', 'sharedresource');
}
<<<<<<< HEAD

$settings->add(new admin_setting_configselect('sharedresource_enablerssfeeds', get_string('enablerssfeeds', 'admin'),
                   $str, 0, $options));

$settings->add(new admin_setting_configtext('sharedresource_article_quantity', get_string('articlequantity', 'sharedresource'),
                   get_string('configarticlequantity', 'sharedresource'), 10, PARAM_INT));

$checkedyesno = array('' => get_string('no'), 'checked' => get_string('yes')); // not nice at all
=======

$key = 'sharedresource/enablerssfeeds';
$label = get_string('enablerssfeeds', 'admin');
$settings->add(new admin_setting_configselect($key, $label, $str, 0, $options));

$key = 'sharedresource/article_quantity';
$label = get_string('articlequantity', 'sharedresource');
$desc = get_string('configarticlequantity', 'sharedresource');
$settings->add(new admin_setting_configtext($key, $label, $desc, 10, PARAM_INT));

$checkedyesno = array('' => get_string('no'), 'checked' => get_string('yes')); // Not nice at all.
>>>>>>> MOODLE_32_STABLE

$key = 'sharedresource/freeze_index';
$label = get_string('freeze_index', 'sharedresource');
$desc = get_string('config_freeze_index', 'sharedresource');
$settings->add(new admin_setting_configcheckbox($key, $label, $desc, '0'));

$key = 'sharedresource/backup_index';
$label = get_string('backup_index', 'sharedresource');
$desc = get_string('config_backup_index', 'sharedresource');
$settings->add(new admin_setting_configcheckbox($key, $label, $desc, '0'));

$key = 'sharedresource/restore_index';
$label = get_string('restore_index', 'sharedresource');
$desc = get_string('config_restore_index', 'sharedresource');
$settings->add(new admin_setting_configcheckbox($key, $label, $desc, '0'));

$key = 'sharedresource/framesize';
$label = get_string('framesize', 'sharedresource');
$desc = get_string('configframesize', 'sharedresource');
$settings->add(new admin_setting_configtext($key, $label, $desc, 130, PARAM_INT));

$key = 'sharedresource/defaulturl';
$label = get_string('resourcedefaulturl', 'sharedresource');
$desc = get_string('configdefaulturl', 'sharedresource');
$settings->add(new admin_setting_configtext($key, $label, $desc, 'http://'));

$key = 'sharedresource/foreignurl';
$label = get_string('resourceaccessurlasforeign', 'sharedresource');
$desc = get_string('configforeignurlsheme', 'sharedresource');
$settings->add(new admin_setting_configtext($key, $label, $desc, ''), PARAM_RAW, 80);

$woptions = array('' => get_string('newwindow', 'sharedresource'), 'checked' => get_string('pagewindow', 'sharedresource'));
$key = 'sharedresource/popup';
$label = get_string('display', 'sharedresource');
$desc = get_string('configpopup', 'sharedresource');
$settings->add(new admin_setting_configselect($key, $label, $desc, '', $woptions));

foreach ($SHAREDRESOURCE_WINDOW_OPTIONS as $optionname) {
    $popupoption = "sharedresource/popup$optionname";
    if ($popupoption == 'sharedresource_popupheight') {
        $key = 'sharedresource/popupheight';
        $label = get_string('newheight', 'sharedresource');
        $desc = get_string('configpopupheight', 'sharedresource');
        $settings->add(new admin_setting_configtext($key, $label, $desc, 600, PARAM_INT));
    } else if ($popupoption == 'sharedresource_popupwidth') {
        $key = 'sharedresource/popupwidth';
        $label = get_string('newwidth', 'sharedresource');
        $desc = get_string('configpopupwidth', 'sharedresource');
        $settings->add(new admin_setting_configtext($key, $label, $desc, 800, PARAM_INT));
    } else {
        $label = get_string('new'.$optionname, 'sharedresource');
        $desc = get_string('configpopup'.$optionname, 'sharedresource');
        $settings->add(new admin_setting_configselect($popupoption, $label, $desc, 'checked', $checkedyesno));
    }
}

// Get plugins list for enabling/disabling.

$pluginscontrolstr = get_string('pluginscontrol', 'sharedresource');
$pluginscontrolinfostr = get_string('pluginscontrolinfo', 'sharedresource');
$settings->add(new admin_setting_heading('plugincontrol', $pluginscontrolstr, $pluginscontrolinfostr));

$checkedoptions = array(0 => get_string('no'), 1 => get_string('yes'));

$pluginsoptions['0'] = get_string('noplugin', 'sharedresource');
$sharedresourcesplugins = core_component::get_plugin_list('sharedmetadata');
foreach ($sharedresourcesplugins as $p => $ppath) {
    $pluginsoptions[$p] = get_string('pluginname', 'sharedmetadata_'.$p);
}
<<<<<<< HEAD
$item = new admin_setting_configselect('pluginchoice', get_string('pluginchoice', 'sharedresource'), get_string('basispluginchoice', 'sharedresource'), @$CFG->pluginchoice, $pluginsoptions);
$item->set_updatedcallback('redirectmetadata');
$settings->add($item);

if (!function_exists('redirectmetadata')){

	function redirectmetadata(){
		global $CFG;
		redirect($CFG->wwwroot.'/mod/sharedresource/metadataconfigure.php?action=reinitialize');
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
=======
$key = 'pluginchoice';
$label = get_string('pluginchoice', 'sharedresource');
$desc = get_string('basispluginchoice', 'sharedresource');
$item = new admin_setting_configselect($key, $label, $desc, @$CFG->pluginchoice, $pluginsoptions);
$settings->add($item);

$key = 'metadataconfig';
$label = get_string('metadataconfiguration', 'sharedresource');
$desc = get_string('medatadaconfigurationdesc', 'sharedresource', $CFG->wwwroot.'/mod/sharedresource/metadataconfigure.php');
$settings->add(new admin_setting_heading($key, $label, $desc));

$key = 'sharedresource/classificationconfig';
$label = get_string('classificationconfiguration', 'sharedresource');
$desc = get_string('classificationconfigurationdesc', 'sharedresource', $CFG->wwwroot.'/mod/sharedresource/classificationconfigure.php');
$settings->add(new admin_setting_heading($key, $label, $desc));
>>>>>>> MOODLE_32_STABLE
