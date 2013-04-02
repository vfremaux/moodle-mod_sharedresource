<?php
	/**
	 *
	 * @author  Frederic GUILLOU
	 * @version 0.0.1
	 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
	 * @package sharedresource
	 *
	 * This php script display the admin part of the classification
	 * configuration. You can add, delete or apply a restriction
	 * on a classification, or configure a specific classification
	 * by accessing another page
	 *-----------------------------------------------------------
	 */

    require_once("../../config.php");
    require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
    require_once($CFG->libdir.'/formslib.php');
	require_once($CFG->libdir.'/ddllib.php');

	$id 			= optional_param('id', 0, PARAM_TEXT);
	$classname 		= optional_param('classificationname', '', PARAM_TEXT);
	$mode 			= optional_param('mode', 0, PARAM_ALPHA);
	$target 		= optional_param('target', '', PARAM_ALPHANUM);
	$table 			= optional_param('table', '', PARAM_TEXT);
	$parent 		= optional_param('parent', 0, PARAM_TEXT);
	$label 			= optional_param('label', '', PARAM_TEXT);
	$ordering 		= optional_param('ordering', 0, PARAM_TEXT);
	$orderingmin 	= optional_param('orderingmin', 0, PARAM_INT);

/// Security

	$systemcontext = context_system::instance();
	require_login();
	require_capability('moodle/site:config', $systemcontext);

/// Build page

	$url = $CFG->wwwroot.'/mod/sharedresource/classificationconfigure.php';
    $PAGE->set_url($url);
    $PAGE->set_context($systemcontext);
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);
    $PAGE->set_focuscontrol(build_navigation(array()));

    echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('classificationconfiguration', 'sharedresource'));

	$recordclassif = true;
	$erroradd = '';
	$errorrestrict = '';
	$classifarray = unserialize(get_config(NULL, 'classifarray'));

	if (!empty($mode)){
		include $CFG->dirroot.'/mod/sharedresource/classificationconfigure.controller.php';
	}	

	echo '<br/><form name="classconfform" action="classificationconfigure.php?mode=add" method="post">';
	echo '<fieldset id="ClassList">';
	echo '<legend align="center">';
	echo get_string('addclassificationtitle', 'sharedresource');
	echo $OUTPUT->help_icon('addclassification', 'addclassification', 'sharedresource');
	echo '</legend><br/>';

	if($erroradd != ''){
		echo '<center style="color:red;">'.$erroradd.'</center>';
		echo '<br/>';
	}

	echo '<table class="generaltable">';
	echo '<tr>';
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
	echo '</td><td>';
	$orderingopts['0'] = '0';
	$orderingopts['1'] = '1';
	echo html_writer:select('orderingmin', $orderingopts);
	echo '</td></tr>';
	echo '</table><br/>';
	echo '<center><input type="submit" value="'.get_string('addclassification','sharedresource').'"/></center>';
	echo '</form>';
	echo '</fieldset><br/>';

	echo '<fieldset id="ClassSelect">';
	echo '<legend align="center">';
	echo get_string('selectclassification','sharedresource');
	echo $OUTPUT->help_icon('selectclassification', 'selectclassification', 'sharedresource');
	echo '</legend><br/>';

	if(!get_config(NULL, 'classifarray') || unserialize(get_config(NULL, 'classifarray')) == array()){
		echo '<center>'.get_string('noclassification','sharedresource').'</center>';
	} else {
		echo '<form name="classselectform" action="classificationconfigure.php?mode=select" method="post">';
		echo '<table align="center" width="65%">';
		foreach($classifarray as $table => $contenu){
			echo '<tr height="50px">';
			echo '<td width="25%">
				<input type="checkbox"';
			if($classifarray[$table]['select'] == 1){
				echo 'checked="yes"';
			}
			echo 'name="'.$table.'" value"'.$table.'"> '.$table.'</td>';
			echo '<td align="left" width="10%">';
			$deleteconfirmstr = get_string('deleteconfim', 'sharedresource');
			$deletestr = get_string('delete');
			echo "<a title=\"$deletestr\" href=\"classificationconfigure.php?mode=delete&target={$table}\" onclick=\"return(confirm('$deleteconfirmstr'));\">";
			echo "<img src=\"{$OUTPUT->pix_url('t/delete','sharedresource')}\" class=\"iconsmall\" alt=\"{$deletestr}\"/></a>";
			echo '</td>';
			echo '<td align="right" width="25%"><input type="button" value="'.get_string('configclassification','sharedresource').'" OnClick="window.location.href=\'classificationconfigure2.php?classification='.$table.'\'"></td>';
			echo '</tr>';
		}
		echo '</table><br/>';
		echo '<center><input type="submit" value="'.get_string('saveselection', 'sharedresource').'"/></center>';
		echo '</form>';
	}
	echo '</fieldset><br/>';
	echo '<fieldset id="Bddselect">';
	echo '<legend align="center">';
	echo get_string('restrictclassification','sharedresource');
	echo $OUTPUT->help_icon('restrictclassification', 'restrictclassification', 'sharedresource');
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
				if($DB->execute($restrictclause) == true){
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
			echo '<td align="center" width="25%"><input type="submit" value="'.get_string('saveSQLrestrict','sharedresource').'"/></td>';
			echo '</tr>';
			if($contenu['restriction'] != ''){
			echo '<tr>';
			echo '<td>'.get_string('appliedSQLrestrict','sharedresource').'</td>';
			echo '<td align="center" width="40%">';
			echo $contenu['restriction'];
			echo '</td>';
			echo '<td align="center" width="10%"><a title="Supprimer" href="classificationconfigure.php?mode=delete&target=restrict'.$table.'"><img src="'.$OUTPUT->pix_url('t/delete').'" class="iconsmall" alt="'.$deletestr.'"></a></td>';
			echo '</tr>';
			echo '<tr height="40px">';
			echo '</tr>';
			}
			echo '</form>';
		}
		echo '</table><br/>';
	}
	echo '</fieldset>';
	echo '<center><hr><br/><input type="button" value="'.get_string('backadminpage','sharedresource').'" onclick="window.location.href=\''.$CFG->wwwroot.'/admin/settings.php?section=modsettingsharedresource\'"/></center><br/>';
	echo $OUTPUT->footer();
