<?php

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
    require_js($CFG->wwwroot.'/mod/sharedresource/js/mtdform.js');
	$action = optional_param('action', PARAM_ALPHA);
    
    print_header_simple($SITE->fullname, $SITE->fullname, '', build_navigation(array()));
	$plugins = sharedresource_get_plugins();
	if (empty($plugins)){
		error("No Metadata plugins installed");
	}
	
	if (empty($CFG->pluginchoice)){
		error("Metadata cannot be configured as no plugin is activated as schema");
	}

	$plugin = $plugins[$CFG->pluginchoice];
		
	$name = $plugin->pluginname;
	$config = array();
    if ($currentconfig = get_records_select('config_plugins', "plugin LIKE 'sharedresource_{$name}'")){
    	foreach($currentconfig as $conf){
    		$config[$conf->name] = $conf->value;
    	}
    }    
    if($config == array() || $action == 'reinitialize'){
		foreach($plugin->METADATATREE as $key => $value) {
			if($key != 0){
				if($value['checked']['system'] == 1){
					$config['config_'.$name.'_system_'.$key.''] = 1;	
					set_config('config_'.$name.'_system_'.$key.'', 1, 'sharedresource_'.$name);					
				}
				if($value['checked']['indexer'] == 1){
					$config['config_'.$name.'_indexer_'.$key.''] = 1;
					set_config('config_'.$name.'_indexer_'.$key.'', 1, 'sharedresource_'.$name);					
				}
				if($value['checked']['author'] == 1){
					$config['config_'.$name.'_author_'.$key.''] = 1;
					set_config('config_'.$name.'_author_'.$key.'', 1, 'sharedresource_'.$name);					
				}
				set_config('activewidgets',serialize(array()));
			}
		}
	}
    if ($data = data_submitted()){
    	delete_records_select('config_plugins', "plugin LIKE 'sharedresource_{$name}'");
		$activewidgets = array();
    	foreach($data as $key => $value){
    		if (preg_match('/config_(\w+?)_/', $key, $matches)){
	    		$pluginname = $matches[1];
	    		set_config($key, $value, 'sharedresource_'.$pluginname);
	    	}
			elseif (preg_match('/widget_(\w+?)_()/', $key, $matches)){
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

	print_heading(get_string('metadataconfiguration', 'sharedresource'));
	echo '<form name="mtdconfigurationform" method="post">';

	echo '<fieldset style="margin:0 auto;width:90%;">';
	if (!empty($plugin)){
		$plugin->configure($config);
	} else {
		print_string('nometadataplugin', 'sharedresource');
	}
	echo '</fieldset><br/>';

	echo '<center><br/><a style="text-decoration:none;" href="metadataconfigure.php?action=reinitialize"><input type="button" value="'.get_string('defaultselect', 'sharedresource').'" /></a>&nbsp;&nbsp;<input type="submit" value="'.get_string('update').'" /></center><br/>';
	echo '<center><hr><br/><input type="button" value="'.get_string('backadminpage','sharedresource').'" OnClick="window.location.href=\''.$CFG->wwwroot.'/admin/settings.php?section=modsettingsharedresource\'"/></center><br/>';
	echo '</form>';
	
	print_footer();

?>