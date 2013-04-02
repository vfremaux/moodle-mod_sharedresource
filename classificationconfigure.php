<?php

	/**
	 *
	 * @author  Frédéric GUILLOU
	 * @version 0.0.1
	 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
	 * @package sharedresource
	 *
	 */

		// This php script display the admin part of the classification
		// configuration. You can add, delete or apply a restriction
		// on a classification, or configure a specific classification
		// by accessing another page
		//-----------------------------------------------------------
	
	
    require_once("../../config.php");
    require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
    require_once($CFG->libdir.'/formslib.php');
	require_once($CFG->libdir.'/ddllib.php');
	
	$classname = optional_param('classificationname', PARAM_TEXT);
	$mode = optional_param('mode', PARAM_ALPHA);
	$target = optional_param('target', PARAM_ALPHANUM);
	$table = optional_param('table', PARAM_TEXT);
	$id = optional_param('id', PARAM_TEXT);
	$parent = optional_param('parent', PARAM_TEXT);
	$label = optional_param('label', PARAM_TEXT);
	$ordering = optional_param('ordering', PARAM_TEXT);
	$orderingmin = optional_param('orderingmin', PARAM_INT);
    
    print_header_simple($SITE->fullname, $SITE->fullname, '', build_navigation(array()));
	print_heading(get_string('classificationconfiguration', 'sharedresource'));
	
	$recordclassif = true;
	$erroradd = '';
	$errorrestrict = '';
	$classifarray = unserialize(get_config(NULL, 'classifarray'));

	if (!empty($mode)){
		include $CFG->dirroot.'/mod/sharedresource/classificationconfigure.controller.php';
	}	

	echo '<br/><form name="classconfform" action="classificationconfigure.php?mode=add" method="post">';
	echo '<fieldset id="ClassList" style="margin:0 auto;width:75%;">';
	echo '<legend align="center">';
	echo get_string('addclassificationtitle','sharedresource');
	helpbutton("addclassification", 'addclassification', "sharedresource");
	echo '</legend><br/>';

	if($erroradd != ''){
		echo '<center style="color:red;">'.$erroradd.'</center>';
		echo '<br/>';
	}

	echo '<table border="1" style="margin:0 auto;width:70%">';
	echo '<tr><td colspan="3"></td></tr><tr>';
	echo '<th align="center" width="200px">';
	echo get_string('classificationname','sharedresource');
	echo '</td><td><input name="classificationname" size="50"/></th>';
	echo '<tr><td colspan="3"></td></tr><tr>';
	echo '<th align="center" width="200px">';
	echo get_string('tablename','sharedresource');
	echo '</td><td><input name="table" size="50"/></th>';
	echo '</tr><tr><td colspan="3"></td></tr><tr/>';
	echo '<td align="center" width="200px">';
	echo get_string('idname','sharedresource');
	echo '</td><td><input name="id" size="50"/></td>';
	echo '</tr><tr>';
	echo '<td align="center" width="200px">';
	echo get_string('parentname','sharedresource');
	echo '</td><td><input name="parent" size="50"/></td>';
	echo '</tr><tr>';
	echo '<td align="center" width="200px">';
	echo get_string('labelname','sharedresource');
	echo '</td><td><input name="label" size="50"/></td>';
	echo '</tr><tr>';
	echo '<td align="center" width="200px">';
	echo get_string('orderingname','sharedresource');
	echo '</td><td><input name="ordering" size="50"/></td>';
	echo '</tr><tr>';
	echo '<td align="center" width="200px">';
	echo get_string('orderingmin','sharedresource');
	echo '</td><td><SELECT name="orderingmin">';
	echo '<option value="0">0</option>';
	echo '<option value="1">1</option>';
	echo '</SELECT></td>';
	echo '</tr>';
	echo '</table><br/>';
	echo '<center><input type="submit" value="'.get_string('addclassification','sharedresource').'"/></center>';
	echo '</form>';
	echo '</fieldset><br/>';
	
	echo '<fieldset id="ClassSelect" style="margin:0 auto;width:75%;">';
	echo '<legend align="center">';
	echo get_string('selectclassification','sharedresource');
	helpbutton("selectclassification", 'selectclassification', "sharedresource");
	echo '</legend><br/>';
	
	if(!get_config(NULL, 'classifarray') || unserialize(get_config(NULL, 'classifarray')) == array()){
		echo '<center>'.get_string('noclassification','sharedresource').'</center>';
	} else {
		echo '<form name="classselectform" action="classificationconfigure.php?mode=select" method="post">';
		echo '<table align="center" width="65%">';
		foreach($classifarray as $table => $contenu){
			echo '<tr height="50px">';
			echo '<td STYLE="width: 25%;"><input type="checkbox"';
			if($classifarray[$table]['select'] == 1){
				echo 'checked="yes"';
			}
			echo 'name="'.$table.'" value"'.$table.'"> '.$table.'</td>';
			echo '<td align="left" STYLE="width: 10%;">';
			echo "<a title=\"Supprimer\" href=\"classificationconfigure.php?mode=delete&target={$table}\" onClick=\"return(confirm('Etes-vous sûr de vouloir supprimer cette classification?'));\">";
			echo "<img src=\"{$CFG->pixpath}/t/delete.gif\" class=\"iconsmall\" alt=\"Supprimer\"/></a>";
			echo '</td>';
			echo '<td align="right" STYLE="width: 25%;"><input STYLE="width: 150px;" type="button" value="'.get_string('configclassification','sharedresource').'" OnClick="window.location.href=\'classificationconfigure2.php?classification='.$table.'\'"></td>';
			echo '</tr>';
		}
		echo '</table><br/>';
		echo '<center><input type="submit" value="'.get_string('saveselection','sharedresource').'"/></center>';
		echo '</form>';
	}
	echo '</fieldset><br/>';
	
	echo '<fieldset id="Bddselect" style="margin:0 auto;width:75%;">';
	echo '<legend align="center">';
	echo get_string('restrictclassification','sharedresource');
	helpbutton("restrictclassification", 'restrictclassification', "sharedresource");
	echo '</legend><br/>';

	if(!get_config(NULL, 'classifarray') || unserialize(get_config(NULL, 'classifarray')) == array()){
		echo '<center>'.get_string('noclassification','sharedresource').'</center>';
	} else {
		echo '<center>'.get_string('SQLrestriction','sharedresource').'</center><br/>';
		echo '<table align="center" width="85%">';
		if ($mode == 'restriction' && !empty($target)){
			$classifarray2 = get_config(NULL, 'classifarray');
			$classifarray = unserialize($classifarray2);
			$restrictclause = optional_param('restrict'.$target, '', PARAM_TEXT);
			if(strtoupper(substr($restrictclause, 0, 6)) == 'SELECT'){
				if(execute_sql($restrictclause, false) == true){
					$classifarray[$target]['restriction'] = $restrictclause;
					set_config('classifarray', serialize($classifarray));
				} else {
					$errorrestrict .= get_string('incorrectSQL','sharedresource');
				}
			} else {
				$errorrestrict .= get_string('noSQLrestrict','sharedresource');
			}			
		}
		if($errorrestrict != ''){
			echo '<br/><center style="color:red;">'.$errorrestrict.'</center>';
		}
		foreach($classifarray as $table => $contenu){
			echo '<form name="'.$table.'restrictionform" action="classificationconfigure.php?mode=restriction&target='.$table.'" method="post">';
			echo '<tr height="45px">';
			echo '<td width="35%"><b>'.$CFG->prefix.$table.'</b></td>';
			echo '<td align="center" width="35%"><input name="restrict'.$table.'" size="65"/></td>';
			echo '<td align="center" width="25%"><input STYLE="width: 80px;" type="submit" value="'.get_string('saveSQLrestrict','sharedresource').'"/></td>';
			echo '</tr>';
			if($contenu['restriction'] != ''){
			echo '<tr>';
			echo '<td>'.get_string('appliedSQLrestrict','sharedresource').'</td>';
			echo '<td align="center" width="40%">';
				echo $contenu['restriction'];
			echo '</td>';
			echo '<td align="center" STYLE="width: 10%;"><a title="Supprimer" href="classificationconfigure.php?mode=delete&target=restrict'.$table.'"><img src="'.$CFG->wwwroot.'/theme/'.current_theme().'/pix/t/delete.gif" class="iconsmall" alt="Supprimer"></a></td>';
			echo '</tr>';
			echo '<tr height="40px">';
			echo '</tr>';
			}
			echo '</form>';
		}
		echo '</table><br/>';
	}
	echo '</fieldset>';
	
	echo '<center><hr><br/><input type="button" value="'.get_string('backadminpage','sharedresource').'" OnClick="window.location.href=\''.$CFG->wwwroot.'/admin/settings.php?section=modsettingsharedresource\'"/></center><br/>';
	print_footer();

?>