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
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package mod_sharedresource
 * @category
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');

global $SHAREDRESOURCE_WINDOW_OPTIONS; // Make sure we have the pesky global.

$hasmetadata = false;
$hasclassification = false;

// Configuration for rss feed.

if (empty($CFG->enablerssfeeds)) {
    $options = array(0 => get_string('rssglobaldisabled', 'admin'));
    $desc = get_string('configenablerssfeeds', 'sharedresource').'<br />'.get_string('configenablerssfeedsdisabled2', 'admin');
} else {
    $options = array(0 => get_string('no'), 1 => get_string('yes'));
    $desc = get_string('configenablerssfeeds', 'sharedresource');
}

$managecap = sharedresources_has_capability_somewhere('repository/sharedresources:manage', false, false, false, CONTEXT_COURSECAT.','.CONTEXT_COURSE);

if ($namespace = get_config('sharedresource', 'schema')) {
    $hasmetadata = true;
    $plugin = sharedresource_get_plugin($namespace);

    if (!is_null($plugin->getClassification())) {
        $hasclassification = true;
    }
}

if ($ADMIN->fulltree) {

    $label = get_string('repository', 'sharedresource');
    $settings->add(new admin_setting_heading('h0', $label, ''));

    $checkedyesno = array('' => get_string('no'), 'checked' => get_string('yes')); // Not nice at all.

    $key = 'sharedresource/freeze_index';
    $label = get_string('freeze_index', 'sharedresource');
    $desc = get_string('configfreezeindex', 'sharedresource');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, '0'));

    $key = 'sharedresource/backup_index';
    $label = get_string('backup_index', 'sharedresource');
    $desc = get_string('configbackupindex', 'sharedresource');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, '0'));

    $key = 'sharedresource/restore_index';
    $label = get_string('restore_index', 'sharedresource');
    $desc = get_string('configrestoreindex', 'sharedresource');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, '0'));

    $key = 'sharedresource/defaulturl';
    $label = get_string('resourcedefaulturl', 'sharedresource');
    $desc = get_string('configdefaulturl', 'sharedresource');
    $settings->add(new admin_setting_configtext($key, $label, $desc , 'http://'));

    $key = 'sharedresource/foreignurl';
    $label = get_string('resourceaccessurlasforeign', 'sharedresource');
    $desc = get_string('configforeignurlsheme', 'sharedresource');
    $settings->add(new admin_setting_configtext($key, $label, $desc, ''), PARAM_RAW, 80);

    $label = get_string('layout', 'sharedresource');
    $settings->add(new admin_setting_heading('h1', $label, ''));

    $key = 'sharedresource/framesize';
    $label = get_string('framesize', 'sharedresource');
    $desc = get_string('configframesize', 'sharedresource');
    $settings->add(new admin_setting_configtext($key, $label, $desc, 130, PARAM_INT));

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
            $settings->add(new admin_setting_configtext($key, $label, $desc , 600, PARAM_INT));
        } else if ($popupoption == 'sharedresource/popupwidth') {
            $key = 'sharedresource/popupwidth';
            $label = get_string('newwidth', 'sharedresource');
            $desc = get_string('configpopupwidth', 'sharedresource');
            $settings->add(new admin_setting_configtext($key, $label, $desc , 800, PARAM_INT));
        } else {
            $label = get_string('new'.$optionname, 'sharedresource');
            $desc = get_string('configpopup'.$optionname, 'sharedresource');
            $settings->add(new admin_setting_configselect($popupoption, $label, $desc, 'checked', $checkedyesno));
        }
    }

    $label = get_string('libraryengine', 'sharedresource');
    $settings->add(new admin_setting_heading('h2', $label, ''));

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
    $key = 'sharedresource/schema';
    $label = get_string('schema', 'sharedresource');
    $desc = get_string('schema_desc', 'sharedresource');
    $item = new admin_setting_configselect($key, $label, $desc, '', $pluginsoptions);
    $settings->add($item);

    $key = 'sharedresource/accesscontrol';
    $label = get_string('accesscontrol', 'sharedresource');
    $desc = get_string('configaccesscontrol', 'sharedresource');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, '0'));

    /*
    $disabledarr = array('' => get_string('disabled', 'sharedresource'));
    $userfieldoptions = $DB->get_records_menu('user_info_field', array(), 'id, name', 'id, name');
    $userfieldoptions = $disabledarr + $userfieldoptions;
    $key = 'sharedresource/defaultuserfield';
    $label = get_string('defaultuserfield', 'sharedresource');
    $desc = get_string('configdefaultuserfield', 'sharedresource');
    $settings->add(new admin_setting_configselect($key, $label, $desc, '', $userfieldoptions));

    $key = 'sharedresource/allowmultipleaccessvalues';
    $label = get_string('allowmultipleaccessvalues', 'sharedresource');
    $desc = get_string('configallowmultipleaccessvalues', 'sharedresource');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, '0'));
    */

    $key = 'sharedresource/hidemetadatadesc';
    $label = get_string('hidemetadatadesc', 'sharedresource');
    $desc = '';
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, '0'));

    if ($namespace = get_config('sharedresource', 'schema')) {
        $key = 'sharedresource/metadataconfig';
        $label = get_string('metadataconfiguration', 'sharedresource');
        $desc = get_string('medatadaconfiguration_desc', 'sharedresource', $CFG->wwwroot.'/mod/sharedresource/metadataconfigure.php');
        $settings->add(new admin_setting_heading($key, $label, $desc));

        $plugin = sharedresource_get_plugin($namespace);

        if (!is_null($plugin->getClassification())) {
            $key = 'sharedresource/classificationconfig';
            $label = get_string('classificationconfiguration', 'sharedresource');
            $desc = get_string('classificationconfiguration_desc', 'sharedresource', $CFG->wwwroot.'/mod/sharedresource/classifications.php');
            $settings->add(new admin_setting_heading($key, $label, $desc));
        }
    }

    if (empty($CFG->enablerssfeeds)) {
        $label = get_string('rss', 'sharedresource');
        $settings->add(new admin_setting_heading('h10', $label, ''));

        $key = 'sharedresource/enablerssfeeds';
        $label = get_string('enablerssfeeds', 'admin');
        $settings->add(new admin_setting_configselect($key, $label, $desc, 0, $options));

        $key = 'sharedresource/article_quantity';
        $label = get_string('articlequantity', 'sharedresource');
        $desc = get_string('configarticlequantity', 'sharedresource');
        $settings->add(new admin_setting_configtext($key, $label, $desc, 10, PARAM_INT));
    }

    if (mod_sharedresource_supports_feature('emulate/community')) {
        // This will accept any.
        $settings->add(new admin_setting_heading('plugindisthdr', get_string('plugindist', 'sharedresource'), ''));

        $key = 'mod_sharedresource/emulatecommunity';
        $label = get_string('emulatecommunity', 'sharedresource');
        $desc = get_string('emulatecommunity_desc', 'sharedresource');
        $settings->add(new admin_setting_configcheckbox($key, $label, $desc, 0));
    } else {
        $label = get_string('plugindist', 'sharedresource');
        $desc = get_string('plugindist_desc', 'sharedresource');
        $settings->add(new admin_setting_heading('plugindisthdr', $label, $desc));
    }
}

if ($hasmetadata || $hasclassification) {

    if ($DB->get_field('modules', 'visible', array('name' => 'sharedresource'))) {

        $label = new lang_string('pluginname', 'mod_sharedresource');
        $ADMIN->add('modsettings', new admin_category('modsharedresourcefolder', $label));

        if (!$ADMIN->locate('resources')) {
            $ADMIN->add('root', new admin_category('resources', get_string('resources', 'local_sharedresources')));
        }

        if ($hasmetadata) {
            $label = get_string('metadata', 'sharedresource');
            $pageurl = new moodle_url('/mod/sharedresource/metadataconfigure.php');
            $settingspage = new admin_externalpage('resourcemetadata', $label, $pageurl, 'repository/sharedresources:manage');
            $ADMIN->add('modsharedresourcefolder', $settingspage);
        }

        if ($hasclassification) {
            $label = get_string('classifications', 'sharedresource');
            $pageurl = new moodle_url('/mod/sharedresource/classifications.php');
            $settingspage = new admin_externalpage('resourceclassification', $label , $pageurl, 'repository/sharedresources:manage');
            $ADMIN->add('modsharedresourcefolder', $settingspage);
        }
    }
}