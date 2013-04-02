<?php

/**
 *
 * @author  Frédéric GUILLOU
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */

    // These php functions are used in order to display the
    // metadata form
    //-----------------------------------------------------------

	
/*	
	This function detects a change in the metadata model used et display a message to a inform the user of the loss of the old metadata
	in case of validation.
*/
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');

function DetectChangeDM($sharedresource_entry, $pluginchoice){
	global $CFG;
	$metadata = get_records_select('sharedresource_metadata', "entry_id = {$sharedresource_entry->id} and namespace <> '{$pluginchoice}'" );
	if(!empty($metadata)){
		echo '<h2><center style=\"color:red;\">';
		echo get_string('existothermetadata','sharedresource');
		echo '</center></h2>';
	}
}

/*	
	This function creates tabs.
*/

function metadara_create_tab($capability, &$mtdstandard) {
	$nbrmenu = count($mtdstandard->METADATATREE[0]['childs']);
	for ($i = 1; $i <= $nbrmenu; $i++) {
		echo "\t\t".'<li id="menu_'.$i.'" style="float:left;display: ';
		if(record_exists_select('config_plugins', "name LIKE 'config_{$mtdstandard->pluginname}_{$capability}_{$i}'") == true){
			echo 'inline;">'."\n\t\t";
		} else {
			echo 'none;">'."\n\t\t";
		}
		$lowername = strtolower($mtdstandard->METADATATREE[$i]['name']);
		$tabname = get_string(clean_string_key($lowername), 'sharedresource');
		echo '<a id="_'.$i.'" class="ghost" onclick="multiMenu(this.id,'.$nbrmenu.')" alt="menu'.$i.'"><span>'.$tabname.'</span></a>'."\n";
		echo "\t\t".'</li>'."\n";
	}
}

/*	
* This function creates content of tabs.
*/
function metadata_create_panels($capability, &$mtdstandard) {
	$mode = required_param('mode', PARAM_ALPHA);
	$nbrmenu = count($mtdstandard->METADATATREE[0]['childs']);
	$add           = optional_param('add', 0, PARAM_ALPHA);
	$update        = optional_param('update', 0, PARAM_INT);
	$return        = optional_param('return', 0, PARAM_BOOL); //return to course/view.php if false or mod/modname/view.php if true
	$type          = optional_param('type', '', PARAM_ALPHANUM);
	$section       = optional_param('section', 0, PARAM_INT);
	$mode          = required_param('mode', PARAM_ALPHA);
	$course        = required_param('course', PARAM_INT);
	$pagestep      = optional_param('pagestep', 1, PARAM_INT);
	$insertinpage  = optional_param('insertinpage', false, PARAM_INT);
	echo '<link type="text/css" rel="stylesheet" href="form.css" />';
	echo '<div style="margin-left: 67px;"><form id="monForm" action="metadatarep.php" method="post">';
	echo '<input type="hidden"  name="pluginchoice"  value="'.$mtdstandard->pluginname.'">';
	echo '<input type="hidden"  name="mode"  value="'.$mode.'">';
	echo '<input type="hidden"  name="course"  value="'.$course.'">';
	echo '<input type="hidden"  name="section"  value="'.$section.'">';
	echo '<input type="hidden"  name="type"  value="'.$type.'">';
	echo '<input type="hidden"  name="add"  value="'.$add.'">';
	echo '<input type="hidden"  name="return"  value="'.$return.'">';
	echo '<input type="hidden"  name="insertinpage"  value="'.$insertinpage.'">';
	for ($i = 1; $i <= $nbrmenu; $i++) {
		echo '<div id="tab_'.$i.'" class="off content">';
		echo '<div class="titcontent">';
		$lowername = strtolower($mtdstandard->METADATATREE[$i]['name']);
		$tabname = get_string(clean_string_key($lowername), 'sharedresource');
		echo '<h2 >'.get_string('node', 'sharedresource').' '.$tabname.'</h2>';
		echo '</div>';
		echo '<h3>';
		echo get_string('completeform', 'sharedresource');
		echo '</h3><br/>';
		if($mtdstandard->METADATATREE[0]['childs'][$i] == 'list'){
			echo metadata_make_part_form($mtdstandard, $i, true, 1, $i, $capability);
		} else {
			echo metadata_make_part_form($mtdstandard, $i, false, 1, $i, $capability);
		}
		echo '</div>';  
	}
	echo '</form></div>';
}

/*	
* This function creates content of tabs.
*/
function metadata_create_notice_panels(&$sharedresource_entry, $capability, &$mtdstandard) {
	$nbrmenu = count($mtdstandard->METADATATREE[0]['childs']);
	echo '<link type="text/css" rel="stylesheet" href="form.css" />';
	echo '<div style="margin-left: 67px;">';
	for ($i = 1; $i <= $nbrmenu; $i++) {
		echo '<div id="tab_'.$i.'" class="off content">';
		echo '<div class="titcontent">';
		$lowername = strtolower($mtdstandard->METADATATREE[$i]['name']);
		$tabname = get_string(clean_string_key($lowername), 'sharedresource');
		echo '<h2 >'.get_string('node', 'sharedresource').' '.$tabname.'</h2>';
		echo '</div>';
		echo '<table width="100%">';
		if($mtdstandard->METADATATREE[0]['childs'][$i] == 'list'){
			echo metadata_make_part_view($sharedresource_entry, $mtdstandard, $i, true, 1, $i, $capability);
		} else {
			echo metadata_make_part_view($sharedresource_entry, $mtdstandard, $i, false, 1, $i, $capability);
		}
		echo '</table>';
		echo '</div>';  
	}
	echo '</div>';
}

/**
* This function is used to display the entire form using reccurence. The parameter are in the correct order: 
* @param string $pluginchoice the name of the datamodel choosen (lom for instance)
* @param string $fieldnum the number of the field in the metadata tree
* @param boolean $islist 
* @param int $numoccur the number of occurence of the field displayed, 
* @param string $name the entire name of the field depending of the occurence of parents (1n1_2n2_3 for instance, which represents the field 1_2_3 and occurence 1 and 2 respectively for the fields 1 and 1_2), 
* @param string $capability tells if the field is visible or not depending of the category of the user
* @param boolean $realoccur is used only in the case of classification, when a classification is deleted by an admin and does not appear anymore on the metadata form.
*/

function metadata_make_part_form(&$mtdstandard, $fieldnum, $islist, $numoccur, $name, $capability, $realoccur = 0) {
	global $SESSION, $CFG;

	$lowername = strtolower($mtdstandard->METADATATREE[$fieldnum]['name']);
	$fieldname = get_string(clean_string_key($lowername), 'sharedresource');
	$fieldtype = $mtdstandard->METADATATREE[$fieldnum]['type'];
	$taxumarray = $mtdstandard->getTaxumpath();
	if(record_exists_select('config_plugins', "name LIKE 'config_{$mtdstandard->pluginname}_{$capability}_{$fieldnum}'") == true){
		$newkey = metadata_convert_key($name.'n'.$numoccur);
		$keyid =  $newkey['pos'].':'.$newkey['occ'];
		$sr_entry = $SESSION->sr_entry;
		$sharedresource_entry = unserialize($sr_entry);
		
		$error = @$SESSION->error; // it's an array containing field which contains error and the name of this error
		if($error == 'no error' || !is_array(unserialize($error))){
			$error = array();
		} else {
			$error = unserialize($error);
		}
		$listresult = array();
		if ($fieldtype == 'category'){
			if($mtdstandard->METADATATREE[$fieldnum]['name'] == $taxumarray['main']){ //if the field concerns classification
				echo '<br/><p>';
				if($numoccur == 1){
					echo '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.'</label>';
				} else {
					echo '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.' '.$numoccur.'</label>';
				}
				$classifarray = unserialize(@$CFG->classifarray);
				$fill = metadata_get_stored_value($newkey, $fieldtype, $islist, $mtdstandard, $sharedresource_entry); //we check if there is metadata saved for this field
				echo '<select id="'.$keyid.'" name="'.$name.'n'.$numoccur.'">';
				if($fill != ''){
					echo metadata_print_classification_options($classifarray);
				} else {
					echo metadata_print_classification_options($classifarray, $fill); //the second parameter will make the correct option selected
				}
				echo '</select>';
				echo '</p>';
				$fieldtype = 'select'; // the type of the field is change for the verification in javascript
			} else {
				if($islist){ //if the category is a list, we have to check the number of occurrence of the field
					if(strpos($fieldnum,'_') != FALSE){
						$search = ':'.substr($newkey['occ'], 0, strrpos($newkey['occ'], '_'));
					} else {
						$search = ':';
					}
					$maxoccur =  metadata_find_max_occurrence($fieldnum, $search, $mtdstandard, $sharedresource_entry);
					$listresult = metadata_get_children_nodes($mtdstandard, $fieldnum, $capability);
					if(!empty($listresult)){ //we verify if all children of this category have been filled
						$isfill = metadata_check_subcats_filled($listresult, $newkey['occ'], $mtdstandard->pluginname, $sharedresource_entry);
					}
				}
				if(!isset($isfill) || $isfill || $maxoccur == 1){ // it's ok and we display the category, then display children recursively
					echo '<fieldset><br/>';
					if($numoccur == 1){
						echo '<legend>'.$fieldnum.' '.$fieldname.'</legend>';
					}
					elseif($realoccur != 0){
						if($realoccur == 1){
							echo '<legend>'.$fieldnum.' '.$fieldname.'</legend>';
						} else {
							echo '<legend>'.$fieldnum.' '.$fieldname.' '.$realoccur.'</legend>';
						}
					} else {
						echo '<legend>'.$fieldnum.' '.$fieldname.' '.$numoccur.'</legend>';
					}
					$nbrfils = count($mtdstandard->METADATATREE[$fieldnum]['childs']);
					for ($i = 1; $i <= $nbrfils; $i++) {
						$currentfield = $fieldnum.'_'.$i;
						if($mtdstandard->METADATATREE[$fieldnum]['childs'][$currentfield]=='list'){
							echo metadata_make_part_form($mtdstandard, $currentfield,True,1,$name.'n'.$numoccur.'_'.$i,$capability);
						} else {
							echo metadata_make_part_form($mtdstandard, $currentfield, false, 1, $name.'n'.$numoccur.'_'.$i,$capability);
						}
					}
					echo '</fieldset>';
					$exist = true;
				} else { // in the case we have a category which is empty, so we don't display it
					$exist = false;
				}
			}
		} else {
			if ($fieldtype == 'text' || $fieldtype == 'codetext'){
			echo '<br/><p>';
			if($numoccur == 1){
				echo '<label ';
				if(array_key_exists($keyid, $error)){
					echo 'class="error"';
				}
				echo 'for="'.$keyid.'">'.$fieldnum.' '.$fieldname.'</label>';
			} else {
				echo '<label ';
				if(array_key_exists($keyid,$error)){
					echo 'class="error"';
				}
				echo 'for="'.$keyid.'">'.$fieldnum.' '.$fieldname.' '.$numoccur.'</label>';
			}
			echo '<input type="text" id="'.$keyid.'" name="'.$name.'n'.$numoccur.'"';
			$fill = metadata_get_stored_value($newkey, $fieldtype, $islist, $mtdstandard, $sharedresource_entry);
			if($fieldnum == $mtdstandard->getTitleElement()->name){
				echo 'value="'.$sharedresource_entry->title.'" readonly="readonly"';
			} elseif ($fill != '') {
				echo 'value="'.$fill.'"';
			}
			echo '/>';
			if(array_key_exists($keyid,$error)){
				echo '<br/>'.$error[$keyid];
			}
			echo '</p>';
			} elseif ($fieldtype == 'select'){
				echo '<br/><p>';
				if($numoccur ==1){
					echo '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.'</label>';
				} else {
					echo '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.' '.$numoccur.'</label>';
				}
				echo '<select id="'.$keyid.'" name="'.$name.'n'.$numoccur.'">';
				$fill = metadata_get_stored_value($newkey, $fieldtype, $islist, $mtdstandard, $sharedresource_entry);
				if($fill == ''){
					echo '<option selected value="basicvalue"></option>';
					foreach($mtdstandard->METADATATREE[$fieldnum]['values'] as $value) {
						echo '<option value="'.$value.'">'.get_string(clean_string_key($value), 'sharedresource').'</option>';
					}
				} else {
					echo '<option value="basicvalue"></option>';
					foreach($mtdstandard->METADATATREE[$fieldnum]['values'] as $value) {
						if($value == $fill){
							echo '<option selected value="'.$value.'">'.get_string(clean_string_key($value), 'sharedresource').'</option>';
						} else {
							echo '<option value="'.$value.'">'.get_string(clean_string_key($value), 'sharedresource').'</option>';
						}
					}
				}
				echo '</select>';
				echo '</p>';
			}
			elseif ($fieldtype == 'date'){
				echo '<br/><p>';
				if($numoccur ==1){
					echo '<label ';
					if(array_key_exists($keyid,$error)){
						echo 'class="error"';
					}
					echo 'for="'.$keyid.'_dateyear">'.$fieldnum.' '.$fieldname.'</label>';
				} else {
					echo '<label ';
					if(array_key_exists($keyid,$error)){
						echo 'class="error"';
					}
					echo 'for="'.$keyid.'_dateyear">'.$fieldnum.' '.$fieldname.' '.$numoccur.'</label>';
				}
				$fill = metadata_get_stored_value($newkey, $fieldtype, $islist, $mtdstandard, $sharedresource_entry);
				if($fill != ''){
					$date = date("Y-m-d", $fill);
				}
				echo '<SELECT class="form_input_year" id="'.$keyid.'_dateyear" name="'.$name.'n'.$numoccur.'_dateyear">';
				echo '<option value="-year-">';
				echo get_string('year','sharedresource'); 
				echo '</option>';
				for ($i = date('Y'); $i >= 1970; $i--){
					if($fill != '' && $i == substr($date,0,4)){
						echo '<option selected value="'.$i.'">'.$i.'</option>';
					}
					else{
						echo '<option value="'.$i.'">'.$i.'</option>';
					}
				}
				echo '</select>';
				echo '<select class="form_input_month" id="'.$keyid.'_datemonth" name="'.$name.'n'.$numoccur.'_datemonth">';
				echo '<option value="-month-">';
				echo get_string('month','sharedresource'); 
				echo '</option>';
				for ($i = 1; $i <=12; $i++){
					if($i < 10){
						if($fill != '' && $i == substr($date,5,2)){
							echo '<option selected value="0'.$i.'">0'.$i.'</option>';
						}
						else{
							echo '<option value="0'.$i.'">0'.$i.'</option>';
						}
					}
					else{
						if($fill != '' && $i == substr($date,5,2)){
							echo '<option selected value="'.$i.'">'.$i.'</option>';
						}
						else{
							echo '<option value="'.$i.'">'.$i.'</option>';
						}
					}
				}
				echo '</select>';
				echo '<select class="form_input_day" id="'.$keyid.'_dateday" name="'.$name.'n'.$numoccur.'_dateday">';
				echo '<option value="-day-">';
				echo get_string('day','sharedresource'); 
				echo '</option>';
				for ($i = 1; $i <=31; $i++){
					if($i<10){
						if($fill != '' && $i == substr($date,8,2)){
							echo '<option selected value="0'.$i.'">0'.$i.'</option>';
						}
						else{
							echo '<option value="0'.$i.'">0'.$i.'</option>';
						}
					} else {
						if($fill != '' && $i == substr($date,8,2)){
							echo '<option selected value="'.$i.'">'.$i.'</option>';
						} else {
							echo '<option value="'.$i.'">'.$i.'</option>';
						}
					}
				}
				echo '</select>';
				if(array_key_exists($keyid,$error)){
					echo '<br/>'.$error[$keyid];
				}
				echo '</p>';
			}
			elseif ($fieldtype == 'duration'){
				$fill = metadata_get_stored_value($newkey, $fieldtype, $islist, $mtdstandard, $sharedresource_entry);
				$duration = get_string('durationdescr', 'sharedresource');
				echo '<br/><p>';
				if($numoccur ==1){
					echo '<label ';
					if(array_key_exists($keyid,$error)){
						echo 'class="error"';
					}
					echo 'for="'.$keyid.'_Day">'.$fieldnum.' '.$fieldname.'</label>';
				} else {
					echo '<label ';
					if(array_key_exists($keyid,$error)){
						echo 'class="error"';
					}
					echo 'for="'.$keyid.'_Day">'.$fieldnum.' '.$fieldname.' '.$numoccur.'</label>';
				}
				echo '<input class="form_input_duration" id="'.$keyid.'_Day" name="'.$name.'n'.$numoccur.'_Day" ';
				if($fill != ''){
					$time = metadata_build_time($fill);
					echo 'value = "'.$time['day'].'"';
				}
				echo '/> '.get_string('days', 'sharedresource');
				echo '<input class="form_input_duration" id="'.$keyid.'_Hou" name="'.$name.'n'.$numoccur.'_Hou" ';
				if($fill != ''){
					$time = metadata_build_time($fill);
					echo 'value = "'.$time['hour'].'"';
				}
				echo '/> '.get_string('hours', 'sharedresource');
				echo '<input class="form_input_duration" id="'.$keyid.'_Min" name="'.$name.'n'.$numoccur.'_Min" ';
				if($fill != ''){
					$time = metadata_build_time($fill);
					echo 'value = "'.$time['minute'].'"';
				}
				echo '/> '.get_string('minutes', 'sharedresource');
				echo '<input class="form_input_duration" id="'.$keyid.'_Sec" name="'.$name.'n'.$numoccur.'_Sec" ';
				if($fill != ''){
					$time = metadata_build_time($fill);
					echo 'value = "'.$time['second'].'"';
				}
				echo '/> '.get_string('seconds', 'sharedresource');
				helpbutton('durationdescr', $duration, 'sharedresource');
				if(array_key_exists($keyid,$error)){
					echo '<br/>'.$error[$keyid];
				}
				echo '</p>';
			}
			elseif ($fieldtype == 'vcard'){
				$fill = metadata_get_stored_value($newkey, $fieldtype, $islist, $mtdstandard, $sharedresource_entry);
				echo '<br/><p>';
				$vcard = get_string('vcard', 'sharedresource');
				if($numoccur ==1){
					echo '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.'</label>';
				} else {
					echo '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.' '.$numoccur.'</label>';
				}
				echo '<textarea cols="40" rows="5" id="'.$keyid.'" name="'.$name.'n'.$numoccur.'">';
				if($fill != ''){
					echo $fill;
				} else {
					echo "BEGIN:VCARD\nVERSION:\nFN:\nN:\nEND:VCARD";
				}
				echo '</textarea>';
				helpbutton("vcard", $vcard, "sharedresource");
				echo '</p>';
			}
		}
		if($islist){ //is the field is a list, we have to display an add button
			if(strpos($fieldnum,'_') != false){
				$search = ':'.substr($newkey['occ'], 0, strrpos($newkey['occ'],'_'));
			} else {
				$search = ':';
			}
			$maxoccur =  metadata_find_max_occurrence($fieldnum, $search, $mtdstandard, $sharedresource_entry);
			$listchildren = implode(';', $listresult);
			if($maxoccur == 1){ //if there is only one occurrence of a field, we display the add button
				echo '<div id="add_'.$keyid.'">';
				echo '<input STYLE="margin-bottom: 20px;" type="button" class="addbutton" value="'.get_string('add', 'sharedresource') .' '.$fieldname.'" onClick="javascript:go(\''.$mtdstandard->pluginname.'\',\''.$fieldnum.'\',\''.$islist.'\',\''.$numoccur.'\',\''.$name.'\',\''.$fieldtype.'\',\''.$keyid.'\',\''.$listchildren.'\',\''.$capability.'\',\''.$realoccur.'\')"><br/>';
				echo '</div>';
				echo '<div id="zone_'.$name.'_'.$numoccur.'"></div>';
			} else {	// if there is more thant one occurence
				if($numoccur == 1){	// if we are treating the first occurence, we are going to display all other occurence and the add button at the end
					if(isset($exist) && $exist){ // if the category which has the number 1 has been displayed, we start at the number 2
						$realoccur = 2;
					} else {
						$realoccur = 1; // else (if the category was empty and not displayed), we start at the number 1 because nothing has been displayed yet
					}
					for ($i = $numoccur + 1; $i <= $maxoccur; $i++){ // we are displaying all occurrences of the field
						if ($fieldtype == 'category'){
							$listresult = metadata_get_children_nodes($mtdstandard, $fieldnum, $capability);
							$newkey = metadata_convert_key($name.'n'.$i);
							if(strpos($fieldnum,'_') != FALSE){
								$search = ':'.substr($newkey['occ'], 0, strrpos($newkey['occ'],'_'));
							} else {
								$search = ':';
							}
							$maxoccur =  metadata_find_max_occurrence($fieldnum, $search, $mtdstandard, $sharedresource_entry);
							if(!empty($listresult)){
								$isfill = metadata_check_subcats_filled($listresult, $newkey['occ'], $mtdstandard->pluginname, $sharedresource_entry);
							}
							if(!isset($isfill) || $isfill){
								if($realoccur != 1){
									echo '<br/>';
								}
								echo metadata_make_part_form($mtdstandard, $fieldnum, true, $i, $name, $capability, $realoccur);
								$realoccur ++;
							}
						} else {
							echo metadata_make_part_form($mtdstandard, $fieldnum, true, $i, $name, $capability);
						}
						if($i == $maxoccur){ // if it's the last occurence, we display the add button
							$keyid = substr($keyid, 0, -1).($maxoccur - 1);
							$numoccur = $maxoccur;
							echo '<div id="add_'.$keyid.'">';
							echo '<input style="margin-bottom: 20px;" type="button" class="addbutton" value="'.get_string('add', 'sharedresource').' '.$fieldname.'" onClick="javascript:go(\''.$mtdstandard->pluginname.'\',\''.$fieldnum.'\',\''.$islist.'\',\''.$numoccur.'\',\''.$name.'\',\''.$fieldtype.'\',\''.$keyid.'\',\''.$listchildren.'\',\''.$capability.'\',\''.$realoccur.'\')">';
							echo '</div>';
							echo '<div id="zone_'.$name.'_'.$numoccur.'"></div>';
							
						}
					}
				}
			}
		}
	}
}

/**
* This function is used to display the entire metadata notice. The parameter are in the correct order: 
* @param string $mtdstandard the instance of activated metadata plugin
* @param string $fieldnum the number of the field in the metadata tree
* @param boolean $islist 
* @param int $numoccur the number of occurence of the field displayed, 
* @param string $name the entire name of the field depending of the occurence of parents (1n1_2n2_3 for instance, which represents the field 1_2_3 and occurence 1 and 2 respectively for the fields 1 and 1_2), 
* @param string $capability tells if the field is visible or not depending of the role of the user regarding metadata
* @param boolean $realoccur is used only in the case of classification, when a classification is deleted by an admin and does not appear anymore on the metadata notice.
*/
function metadata_make_part_view(&$sharedresource_entry, &$mtdstandard, $fieldnum, $islist, $numoccur, $name, $capability, $realoccur = 0) {
	global $SESSION, $CFG;

	$lowername = strtolower($mtdstandard->METADATATREE[$fieldnum]['name']);
	$fieldname = get_string(clean_string_key($lowername), 'sharedresource');
	$fieldtype = $mtdstandard->METADATATREE[$fieldnum]['type'];
	$taxumarray = $mtdstandard->getTaxumpath();
	if(record_exists_select('config_plugins', "name LIKE 'config_{$mtdstandard->pluginname}_{$capability}_{$fieldnum}'") == true){
		$newkey = metadata_convert_key($name.'n'.$numoccur);
		$keyid =  $newkey['pos'].':'.$newkey['occ'];
		$listresult = array();
		if ($fieldtype == 'category'){
			//if the field concerns classification
			if($mtdstandard->METADATATREE[$fieldnum]['name'] == $taxumarray['main']){ 
				echo '<tr>';
				if($numoccur == 1){
					echo '<td class="mtdnum '.$keyid.'">'.$fieldnum.'</td><td class="mtdfield '.$keyid.'">'.$fieldname.'</td>';
				} else {
					echo '<td class="mtdnum '.$keyid.'">'.$fieldnum.'</td><td class="mtdfield '.$keyid.'">'.$fieldname.' '.$numoccur.'</td>';
				}
				$classifarray = unserialize(@$CFG->classifarray);
				$fill = metadata_get_stored_value($newkey, $fieldtype, $islist, $mtdstandard, $sharedresource_entry); //we check if there is metadata saved for this field
				echo '<td class="mtdvalue">';
				echo metadata_print_classification_value($classifarray, $fill); //the second parameter will make the correct option selected
				echo '</td>';
				echo '</tr>';
				$fieldtype = 'select'; // the type of the field is change for the verification in javascript
			} else {
				if($islist){ //if the category is a list, we have to check the number of occurrence of the field
					if(strpos($fieldnum,'_') != FALSE){
						$search = ':'.substr($newkey['occ'], 0, strrpos($newkey['occ'], '_'));
					} else {
						$search = ':';
					}
					$maxoccur =  metadata_find_max_occurrence($fieldnum, $search, $mtdstandard, $sharedresource_entry);
					$listresult = metadata_get_children_nodes($mtdstandard, $fieldnum, $capability);
					if(!empty($listresult)){ //we verify if all children of this category have been filled
						$isfill = metadata_check_subcats_filled($listresult, $newkey['occ'], $mtdstandard->pluginname, $sharedresource_entry);
					}
				}
				if(!isset($isfill) || $isfill || $maxoccur == 1){ // it's ok and we display the category, then display children recursively
					echo '<tr><td colspan="3">';
					echo '<fieldset class="subbranch">';
					if($numoccur == 1){
						echo '<legend>'.$fieldnum.' '.$fieldname.'</legend>';
					} elseif($realoccur != 0){
						if($realoccur == 1){
							echo '<legend>'.$fieldnum.' '.$fieldname.'</legend>';
						} else {
							echo '<legend>'.$fieldnum.' '.$fieldname.' '.$realoccur.'</legend>';
						}
					} else {
						echo '<legend>'.$fieldnum.' '.$fieldname.' '.$numoccur.'</legend>';
					}
					$nbrfils = count($mtdstandard->METADATATREE[$fieldnum]['childs']);
					for ($i = 1; $i <= $nbrfils; $i++) {
						$currentfield = $fieldnum.'_'.$i;
						echo '<table width="100%">';
						if($mtdstandard->METADATATREE[$fieldnum]['childs'][$currentfield] == 'list'){
							echo metadata_make_part_view($sharedresource_entry, $mtdstandard, $currentfield, true, 1, $name.'n'.$numoccur.'_'.$i, $capability);
						} else {
							echo metadata_make_part_view($sharedresource_entry, $mtdstandard, $currentfield, false, 1, $name.'n'.$numoccur.'_'.$i, $capability);
						}
						echo '</table>';
					}
					echo '</fieldset>';
					echo '</td></tr>';
					$exist = true;
				} else { // in the case we have a category which is empty, so we don't display it
					$exist = false;
				}
			}
		} else {
			if ($fieldtype == 'text' || $fieldtype == 'codetext'){
			echo '<tr>';
			if($numoccur == 1){
				echo '<td class="mtdnum '.$keyid.'">'.$fieldnum.'</td><td class="mtdfield '.$keyid.'">'.$fieldname.'</td>';
			} else {
				echo '<td class="mtdnum '.$keyid.'">'.$fieldnum.'</td><td class="mtdfield '.$keyid.'">'.$fieldname.' '.$numoccur.'</td>';
			}
			$fill = metadata_get_stored_value($newkey, $fieldtype, $islist, $mtdstandard, $sharedresource_entry);
			echo '<td class="mtdvalue">';
			if($fieldnum == $mtdstandard->getTitleElement()->name){
				echo $sharedresource_entry->title;
			} elseif ($fill != '') {
				echo $fill;
			}
			echo '</td>';
			echo '</tr>';
			} elseif ($fieldtype == 'select'){
				echo '<tr>';
				if($numoccur == 1){
					echo '<td class="mtdnum '.$keyid.'">'.$fieldnum.'</td><td class="mtdfield '.$keyid.'">'.$fieldname.'</label>';
				} else {
					echo '<td class="mtdnum '.$keyid.'">'.$fieldnum.'</td><td class="mtdfield '.$keyid.'">'.$fieldname.' '.$numoccur.'</label>';
				}
				$fill = metadata_get_stored_value($newkey, $fieldtype, $islist, $mtdstandard, $sharedresource_entry);
				echo '<td class="mtdvalue">';
				if($fill != ''){
					print_string(clean_string_key($fill), 'sharedresource');
				}
				echo '</td>';
				echo '</tr>';
			}
			elseif ($fieldtype == 'date'){
				echo '<tr>';
				if($numoccur == 1){
					echo '<td class="mtdnum '.$keyid.'">'.$fieldnum.'</td><td class="mtdfield '.$keyid.'_dateyear">'.$fieldname.'</td>';
				} else {
					echo '<td class="mtdnum '.$keyid.'">'.$fieldnum.'</td><td class="mtdfield '.$keyid.'_dateyear">'.$fieldname.' '.$numoccur.'</td>';
				}
				$fill = metadata_get_stored_value($newkey, $fieldtype, $islist, $mtdstandard, $sharedresource_entry);
				echo '<td class="mtdvalue">';
				if($fill != ''){
					$date = date("Y-m-d", $fill);
					echo $date;
				}
				echo '</td>';
				echo '</tr>';
			}
			elseif ($fieldtype == 'duration'){
				$fill = metadata_get_stored_value($newkey, $fieldtype, $islist, $mtdstandard, $sharedresource_entry);
				$duration = get_string('durationdescr', 'sharedresource');
				echo '<tr>';
				if($numoccur == 1){
					echo '<td class="mtdnum '.$keyid.'">'.$fieldnum.'</td><td class="mtdfield '.$keyid.'_Day">'.$fieldname.'</td>';
				} else {
					echo '<td class="mtdnum '.$keyid.'">'.$fieldnum.'</td><td class="mtdfield '.$keyid.'_Day">'.$fieldname.' '.$numoccur.'</td>';
				}
				echo '<td class="mtdvalue">';
				if($fill != ''){
					$time = metadata_build_time($fill);
					echo $time['day'].' '.get_string('days', 'sharedresource').' ';
					echo $time['hour'].' '.get_string('hours', 'sharedresource').' ';
					echo $time['minute'].' '.get_string('minutes', 'sharedresource'). ' ';
					echo $time['second'].' '.get_string('seconds', 'sharedresource');
				}
				helpbutton('durationdescr', $duration, 'sharedresource');
				echo '</td>';
				echo '</tr>';
			}
			elseif ($fieldtype == 'vcard'){
				$fill = metadata_get_stored_value($newkey, $fieldtype, $islist, $mtdstandard, $sharedresource_entry);
				echo '<tr>';
				$vcard = get_string('vcard', 'sharedresource');
				if($numoccur ==1){
					echo '<td class="mtdnum '.$keyid.'">'.$fieldnum.'</td><td class="mtdfield '.$keyid.'">'.$fieldname.'</td>';
				} else {
					echo '<td class="mtdnum '.$keyid.'">'.$fieldnum.'</td><td class="mtdfield '.$keyid.'">'.$fieldname.' '.$numoccur.'</td>';
				}
				echo '<td class="mtdvalue">';
				if (!empty($fill)){
					echo "<pre>";
					echo $fill;
					echo "</pre>";
				}
				helpbutton("vcard", $vcard, 'sharedresource');
				echo '</td>';
				echo '</tr>';
			}
		}
		if($islist){ //is the field is a list, we have to display subelements
			if(strpos($fieldnum, '_') != false){
				$search = ':'.substr($newkey['occ'], 0, strrpos($newkey['occ'],'_'));
			} else {
				$search = ':';
			}
			$maxoccur =  metadata_find_max_occurrence($fieldnum, $search, $mtdstandard, $sharedresource_entry);
			$listchildren = implode(';', $listresult);
			if($maxoccur > 1){ //if there is only one occurrence of a field, we display the add button
				if($numoccur == 1){	// if we are treating the first occurence, we are going to display all other occurence and the add button at the end
					if(isset($exist) && $exist){ // if the category which has the number 1 has been displayed, we start at the number 2
						$realoccur = 2;
					} else {
						$realoccur = 1; // else (if the category was empty and not displayed), we start at the number 1 because nothing has been displayed yet
					}
					for ($i = $numoccur + 1; $i <= $maxoccur; $i++){ // we are displaying all occurrences of the field
						if ($fieldtype == 'category'){
							$listresult = metadata_get_children_nodes($mtdstandard, $fieldnum, $capability);
							$newkey = metadata_convert_key($name.'n'.$i);
							if(strpos($fieldnum,'_') != FALSE){
								$search = ':'.substr($newkey['occ'], 0, strrpos($newkey['occ'],'_'));
							} else {
								$search = ':';
							}
							// $maxoccur =  metadata_find_max_occurrence($fieldnum, $search, $mtdstandard, $sharedresoruce_entry);
							if(!empty($listresult)){
								$isfill = metadata_check_subcats_filled($listresult, $newkey['occ'], $mtdstandard->pluginname, $sharedresource_entry);
							}
							if(!isset($isfill) || $isfill){
								if($realoccur != 1){
									echo '<br/>';
								}
								echo '<tr><td></td><td></td><td>';
								echo '<table width="100%">';
								echo metadata_make_part_view($sharedresource_entry, $mtdstandard, $fieldnum, true, $i, $name, $capability, $realoccur);
								echo '</table>';
								echo '</td></tr>';
								$realoccur ++;
							}
						} else {
								echo metadata_make_part_view($sharedresource_entry, $mtdstandard, $fieldnum, true, $i, $name, $capability);
						}
					}
				}
			}
		}
	}
}

/*
*	This function converts the key of the field to the correct form recorded in the database (for instance, 1n2_3n4 becomes 1_3:2_4)
*/
function metadata_convert_key($key){
	$Position = '';
	$Occur = '';
	while(strlen($key) != 0){
		if(strlen($Position) != 0 && strlen($Occur) != 0){
			$Position .= '_';
			$Occur .= '_';
		}
		for($i=0 ; $i < stripos($key, 'n') ; $i++){
			$Position .= $key[$i];
		}
		$temp = '';
		if(stripos($key,'_') != FALSE){
			for($i = stripos($key, 'n') + 1;$i < stripos($key, '_') ; $i++){
				$temp .= $key[$i];
			}
			$Occur .= $temp - 1;
			$key = substr(strstr($key,'_'), 1);
		} else {
			for($i = stripos($key, 'n') + 1 ; $i < strlen($key) ; $i++){
				$temp .= $key[$i];
			}
			$Occur .= $temp - 1;
			$key = '';
		}
	}	
	$newkey['pos'] = $Position;
	$newkey['occ'] = $Occur;
	return $newkey;
}

/*
	This function is used to fill fields which have already been completed (in case of an update)
*/
function metadata_get_stored_value($key, $type, $islist, &$mtdstandard, &$sharedresource_entry){
	global $SESSION;

	$taxumarray = $mtdstandard->getTaxumpath();
	$field = $key['pos'].':'.$key['occ'];
	if($mtdstandard->METADATATREE[$key['pos']]['name'] == $taxumarray['main']){
		$keysource = $taxumarray['source'];
		$sourcelength = strlen($keysource);
		$keysource .= ':'.$key['occ'];
		while(strlen($keysource) < (2 * $sourcelength) + 1){
			$keysource .= '_0';
		}
		$source = $sharedresource_entry->element($keysource, $mtdstandard->pluginname);
		$keyid = $taxumarray['id'];
		$idlength = strlen($keyid);
		$keyid .= ':'.$key['occ'];
		while(strlen($keyid) < (2 * $idlength) + 1){
			$keyid .= '_0';
		}
		$id = $sharedresource_entry->element($keyid, $mtdstandard->pluginname);
		$keyentry = $taxumarray['entry'];
		$entrylength = strlen($keyentry);
		$keyentry .= ':'.$key['occ'];
		while(strlen($keyentry)<(2*$entrylength)+1){
			$keyentry .= '_0';
		}
		$entry = $sharedresource_entry->element($keyentry, $mtdstandard->pluginname);
		return $source.':'.$id.':'.$entry;
	} else {
		$value = stripslashes(@$sharedresource_entry->element($field, $mtdstandard->pluginname));
		list ($fieldkey, $occurrence) = explode(':', $field);
		$default = $mtdstandard->defaultValue($fieldkey);
		if (empty($value) && isset($default)) $value = $default;
		switch($type){
			case 'text' :
				return $value;
			break;
			case 'codetext' :
				return $value;
			break;
			case 'select' :
				return $value;
			break;
			case 'date' :
				return $value;
			break;
			case 'duration' :
				return $value;
			break;
			case 'vcard' :
				return $value;
			break;
			default : 
			return '';
			break;
		}
	}
}

/**
*	This function is used to find the maximum number of occurrence of a field
*/
function metadata_find_max_occurrence($fieldnum, $search, &$mtdstandard, &$sharedresource_entry){
	global $SESSION;

	$maxoccur = 1;
	foreach($sharedresource_entry->metadata_elements as $cle => $metadata){
		if($metadata->namespace == $mtdstandard->pluginname){
			if(substr_compare($fieldnum, $metadata->element, 0, strlen($fieldnum)) == 0 && strpos($metadata->element, $search) != FALSE){
				$nbroccur = substr($metadata->element, stripos($metadata->element, ':') + 1);
				if(substr_count($fieldnum, '_') == 0){
					$nbroccur = substr($nbroccur, 0, 1);
				} else {
					for($i = 0 ; $i < substr_count($fieldnum, '_') ; $i++){
						$nbroccur = substr($nbroccur, stripos($metadata->element, '_') + 1);
					}
				}
				if($nbroccur + 1 > $maxoccur){
					$maxoccur = $nbroccur + 1;
				}
			}
		}
	}
	return $maxoccur;
}

/**
* returns an array which contains all children of a category (except those which are a category).
* It is used to check that at least one of the children has been filled before adding a new occurence of a category 
*/
function metadata_get_children_nodes(&$mtdstandard, $fieldnum, $capability, $listchildren = array()){
	$childcount = count($mtdstandard->METADATATREE[$fieldnum]['childs']);
	for ($i = 1; $i <= $childcount; $i++) {
		$currentfield = $fieldnum.'_'.$i;
		if(record_exists_select('config_plugins', "name LIKE 'config_{$mtdstandard->pluginname}_{$capability}_{$currentfield}'") == true){
			if($mtdstandard->METADATATREE[$currentfield]['type'] != 'category'){
				$size = count($listchildren);
				$listchildren[$size] = $currentfield;
			} else {
				$size = count($listchildren); // usefull ?
				$listchildren = metadata_get_children_nodes($mtdstandard, $currentfield, $capability, $listchildren);
			}
		}
	}
	return $listchildren;
}

/**
*	checks that children of a category have been filled 
* (in case of a suppression of a classification, because there can be empty categories).
*/
function metadata_check_subcats_filled($listresult, $numoccur, $pluginchoice, &$sharedresource_entry){
	GLOBAL $SESSION;

	$isfill = false;
	foreach($listresult as $key => $field){
		$listresult[$key] .= ':'.$numoccur;
	}

	foreach($sharedresource_entry->metadata_elements as $cle => $metadata){
		if($metadata->namespace == $pluginchoice){
			foreach($listresult as $key => $field){
				if(substr_compare($field, $metadata->element, 0, strlen($field)) == 0){
					$isfill = true;
				}
			}
		}
	}
	return $isfill;
}

/*
*	transforms a time in seconds to a time in days, hours, minutes and seconds.
*	Used to transform the duration in seconds.
*/
function metadata_build_time($time){
	$result = array();
    if ($time >= 86400){
		$result['day'] = floor($time / 86400);
		$reste = $time % 86400;
		$result['hour'] = floor($reste / 3600);
		$reste = $reste % 3600;
		$result['minute'] = floor($reste / 60);
		$result['second'] = $reste % 60;
    }
    elseif ($time < 86400 AND $time >= 3600){
		$result['day'] = '';
		$result['hour'] = floor($time / 3600);
		$reste = $time % 3600;
		$result['minute'] = floor($reste / 60);
		$result['second'] = $reste % 60;
    }
    elseif ($time < 3600 AND $time >= 60){
		$result['day'] = '';
		$result['hour'] = '';
		$result['minute'] = floor($time / 60);
		$result['second'] = $reste % 60;
    }
    elseif ($time < 60){
		$result['day'] = '';
		$result['hour'] = '';
		$result['minute'] = '';
		$result['second'] = $time;
    }
    return $result;
}

/**
*	checks if a entry is an integer
*/
function metadata_is_integer ($x){
	return (is_numeric($x)? intval($x) == $x : false);
}


/*
* used to display a part of the form. The parameter are in order: 
* @param string $mtdstandard the instance of activated metadata plugin, 
* @param string $fieldnum the number of the field in the metadata tree, 
* @param boolean $islist 
* @param integer $numoccur represents the number of occurence of the field displayed, 
* @param string $name gives the entire name of the field depending of the occurence of parents (1n1_2n2_3 for instance, which represents the field 1_2_3 and occurence 1 and 2 respectively for the fields 1 and 1_2), 
* @param string $capability decides if the field is visible or not depending of the category of the user, 
* @@param $realoccur used only in the case of classification, when a classification is deleted by an admin and does not appear anymore on the metadata form.
*
* It is slightly different from metadata_make_part_form because it does not check if a field is filled or not (indeed, 
* the field is added, so it is always empty).
*/
function metadata_make_part_form2(&$mtdstandard, $fieldnum, $islist, $numoccur, $name, $capability, $realoccur = 0) {
	$lowername = strtolower($mtdstandard->METADATATREE[$fieldnum]['name']);
	$fieldname = get_string(clean_string_key($lowername), 'sharedresource');
	$fieldtype = $mtdstandard->METADATATREE[$fieldnum]['type'];	
	$taxumarray = $mtdstandard->getTaxumpath();
	$str = '';
	if(record_exists_select('config_plugins', "name LIKE 'config_{$mtdstandard->pluginname}_{$capability}_{$fieldnum}'")==true){
		$newkey = metadata_convert_key($name.'n'.$numoccur);
		$keyid =  $newkey['pos'].':'.$newkey['occ'];
		$listresult = array();
		if ($fieldtype == 'category'){
			if($mtdstandard->METADATATREE[$fieldnum]['name'] == $taxumarray['main']){
				$str .= '<p>';
				if($numoccur == 1){
					$str .= '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.'</label>';
				} else {
					$str .= '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.' '.$numoccur.'</label>';
				}
				$classifarray = unserialize(get_config(NULL,'classifarray'));
				$str .= '<select id="'.$keyid.'" name="'.$name.'n'.$numoccur.'">';
				$str .= '<option value="basicvalue"></option>';
				$str .= metadata_print_classification_options($classifarray);
				$str .= '</select>';
				$str .= '</p>';
				$fieldtype = 'select';
			} else {
				$str .= '<fieldset><br/>';
				if($islist){
					$listresult = metadata_get_children_nodes($mtdstandard, $fieldnum, $capability);
				}
				if($numoccur == 1){
					$str .= '<legend>'.$fieldnum.' '.$fieldname.'</legend>';
				}
				elseif($realoccur != 0){
						$str .= '<legend>'.$fieldnum.' '.$fieldname.' '.$realoccur.'</legend>';
					} else {
						$str .= '<legend>'.$fieldnum.' '.$fieldname.' '.$numoccur.'</legend>';
					}
				$nbrfils = count($mtdstandard->METADATATREE[$fieldnum]['childs']);
				for ($i = 1; $i <= $nbrfils; $i++) {
					$currentfield = $fieldnum.'_'.$i;
					if($mtdstandard->METADATATREE[$fieldnum]['childs'][$currentfield] == 'list'){
						$str .= metadata_make_part_form($mtdstandard, $currentfield, True, 1, $name.'n'.$numoccur.'_'.$i, $capability);
					} else {
						$str .= metadata_make_part_form($mtdstandard, $currentfield, False, 1, $name.'n'.$numoccur.'_'.$i, $capability);
					}
				}
				$str .= '</fieldset>';
			}
		} else {
			if ($fieldtype == 'text' || $fieldtype == 'codetext'){
			$str .= '<br/><p>';
			if($numoccur == 1){
				$str .= '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.'</label>';
			} else {
				$str .= '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.' '.$numoccur.'</label>';
			}
			$str .= '<input type="text" id="'.$keyid.'" name="'.$name.'n'.$numoccur.'" />';
			$str .= '</p>';
		}
		elseif ($fieldtype == 'select'){
			$str .= '<p>';
			if($numoccur ==1){
				$str .= '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.'</label>';
			} else {
				$str .= '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.' '.$numoccur.'</label>';
			}
			$str .= '<select id="'.$keyid.'" name="'.$name.'n'.$numoccur.'">';
			$str .= '<option selected value="basicvalue"></option>';
			foreach($mtdstandard->METADATATREE[$fieldnum]['values'] as $value) {
				$str .= '<option value="'.$value.'">'.get_string(clean_string_key($value), 'sharedresource').'</option>';
			}
			$str .= '</select>';
			$str .= '</p>';
		}
		elseif ($fieldtype == 'date'){
			$str .= '<br/><p>';
			if($numoccur == 1){
				$str .= '<label for="'.$keyid.'_dateyear">'.$fieldnum.' '.$fieldname.'</label>';
			} else {
				$str .= '<label for="'.$keyid.'_dateyear">'.$fieldnum.' '.$fieldname.' '.$numoccur.'</label>';
			}
			$str .= '<select class="form_input_year" id="'.$keyid.'_dateyear" name="'.$name.'n'.$numoccur.'_dateyear">';
			$str .= '<option value="-year-">';
			$str .= get_string('year','sharedresource'); 
			$str .= '</option>';
			for ($i = date('Y'); $i >= 1970 ; $i--){
				$str .= '<option value="'.$i.'">'.$i.'</option>';
			}
			$str .= '</select>';
			$str .= '<select class="form_input_month" id="'.$keyid.'_datemonth" name="'.$name.'n'.$numoccur.'_datemonth">';
			$str .= '<option value="-month-">';
			$str .= get_string('month','sharedresource'); 
			$str .= '</option>';
			for ($i = 1 ; $i <= 12; $i++){
				if($i < 10){
					$str .= '<option value="0'.$i.'">0'.$i.'</option>';
				} else {
					$str .= '<option value="'.$i.'">'.$i.'</option>';
				}
			}
			$str .= '</select>';
			$str .= '<select class="form_input_day" id="'.$keyid.'_dateday" name="'.$name.'n'.$numoccur.'_dateday">';
			$str .= '<option value="-day-">';
			$str .= get_string('day','sharedresource'); 
			$str .= '</option>';
			for ($i = 1; $i <= 31; $i++){
				if($i < 10){
					$str .= '<option value="0'.$i.'">0'.$i.'</option>';
				} else {
					$str .= '<option value="'.$i.'">'.$i.'</option>';
				}
			}
			$str .= '</select>';
			$str .= '</p>';
		}
		elseif ($fieldtype == 'duration'){
			$duration = get_string('durationdescr', 'sharedresource');
			$str .= '<br/><p>';
			if($numoccur == 1){
				$str .= '<label for="'.$keyid.'_Day">'.$fieldnum.' '.$fieldname.'</label>';
			} else{
				$str .= '<label for="'.$keyid.'_Day">'.$fieldnum.' '.$fieldname.' '.$numoccur.'</label>';
			}
			$str .= '<input class="form_input_duration" id="'.$keyid.'_Day" name="'.$name.'n'.$numoccur.'_Day"/> Day(s)';
			$str .= '<input class="form_input_duration" id="'.$keyid.'_Hou" name="'.$name.'n'.$numoccur.'_Hou"/> Hour(s)';
			$str .= '<input class="form_input_duration" id="'.$keyid.'_Min" name="'.$name.'n'.$numoccur.'_Min"/> Minute(s)';
			$str .= '<input class="form_input_duration" id="'.$keyid.'_Sec" name="'.$name.'n'.$numoccur.'_Sec"/> Second(s) ';
			$str .= helpbutton("durationdescr", $duration, 'sharedresource', true, false, '', true);
			$str .= '</p>';
		}
			elseif ($fieldtype == 'vcard'){
				$str .= '<br/><p>';
				$vcard = get_string('vcard', 'sharedresource');
				if($numoccur ==1){
					$str .= '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.'</label>';
				} else {
					$str .= '<label for="'.$keyid.'">'.$fieldnum.' '.$fieldname.' '.$numoccur.'</label>';
				}
				$str .= '<textarea cols="40" rows="5" id="'.$keyid.'" name="'.$name.'n'.$numoccur.'">';
				$str .= "BEGIN:VCARD\nVERSION:\nFN:\nN:\nEND:VCARD";
				$str .= '</textarea>';
				$str .= helpbutton("vcard", $vcard, "sharedresource", true, false, '', true);
				$str .= '</p>';
			}
		}
		if($islist){
			$listchildren = implode(';',$listresult);
			$str .= '<div id="add_'.$keyid.'">';
			$str .= '<input STYLE="margin-bottom: 20px;" type="button" class="addbutton" value="'.get_string('add', 'sharedresource').' '.$fieldname.'" onClick="javascript:go(\''.$mtdstandard->pluginname.'\',\''.$fieldnum.'\',\''.$islist.'\',\''.$numoccur.'\',\''.$name.'\',\''.$fieldtype.'\',\''.$keyid.'\',\''.$listchildren.'\',\''.$capability.'\',\''.$realoccur.'\')"><br/>';
			$str .= '</div>';
			$str .= '<div id="zone_'.$name.'_'.$numoccur.'"></div>';
		}
	}
	
	echo $str;
}


/*
* Function which display and check the metadata submitted by the form
*/

function metadata_display_and_check(&$sharedresource_entry, $pluginchoice, $metadataentries){
	$object = 'sharedresource_plugin_'.$pluginchoice;
	$mtdstandard = new $object;
	$taxumarray = $mtdstandard->getTaxumpath();
	$keywordnum = $mtdstandard->getKeywordElement();
	$error = array();
	$display = '<table border="1" width="70%"><tr><td align="center" width="25%">Nom du champ</td><td align="center" width="25%">Id du champ</td><td align="center">Valeur du champ</td></tr>';
	foreach($metadataentries as $key => $value){
		// we check if the field have been filled for the vcard, select and date
		if(preg_replace('/[[:space:]]/','',$value) != 'BEGIN:VCARDVERSION:FN:N:END:VCARD' && $value != 'basicvalue' && $value != '-year-' && $value != '-month-' && $value != '-day-' && substr($key,-9)!= 'datemonth' && substr($key,-7)!= 'dateday' && substr($key,-3)!= 'Hou' && substr($key,-3)!= 'Min' && substr($key,-3)!= 'Sec'){
			$errortemp = '';
			// if the key is a date, we have to have a treatment on this key
			if(substr($key, -3) == 'Day'){				
				$keytemp = substr($key, 0, -4);	
				$temp = 0;
				if($_POST[$keytemp.'_Day'] != '' && !metadata_is_integer($_POST[$keytemp.'_Day'])){
					$errortemp .= get_string('integerday','sharedresource');
				}
				else if($_POST[$keytemp.'_Day'] != '' && metadata_is_integer($_POST[$keytemp.'_Day']) && $_POST[$keytemp.'_Day'] < 1){
					$errortemp .= get_string('incorrectday','sharedresource');
				}
				if($_POST[$keytemp.'_Day'] != '' && $_POST[$keytemp.'_Day'] != '0'){
					$temp = $_POST[$keytemp.'_Day'] * DAYSECS;
				}
				if($_POST[$keytemp.'_Hou'] != '' && !metadata_is_integer($_POST[$keytemp.'_Hou'])){
					$errortemp .= get_string('integerhour','sharedresource');
				}
				else if($_POST[$keytemp.'_Hou'] != '' && metadata_is_integer($_POST[$keytemp.'_Hou']) && $_POST[$keytemp.'_Hou'] < 1){
					$errortemp .= get_string('incorrecthour','sharedresource');
				}
				if($_POST[$keytemp.'_Hou'] != '' && $_POST[$keytemp.'_Hou'] != '0'){
					$temp += $_POST[$keytemp.'_Hou']*60*60;
				}
				if($_POST[$keytemp.'_Min'] != '' && !metadata_is_integer($_POST[$keytemp.'_Min'])){
					$errortemp .= get_string('integerminute','sharedresource');
				}
				else if($_POST[$keytemp.'_Min'] != '' && metadata_is_integer($_POST[$keytemp.'_Min']) && $_POST[$keytemp.'_Min'] < 1){
					$errortemp .= get_string('incorrectminute','sharedresource');
				}
				if($_POST[$keytemp.'_Min'] != '' && $_POST[$keytemp.'_Min'] != '0'){
					$temp += $_POST[$keytemp.'_Min'] * 60;
				}
				if($_POST[$keytemp.'_Sec'] != '' && $_POST[$keytemp.'_Sec'] < 1){
					$errortemp .= get_string('incorrectsecond','sharedresource');
				}
				if($_POST[$keytemp.'_Sec'] != '' && $_POST[$keytemp.'_Sec'] != '0'){
					$temp += $_POST[$keytemp.'_Sec'];
				}
				$key = $keytemp;
				if($temp != 'P'){
					$value = $temp;
				} else {
					$value = '';
				}
			}
			// if the key is a duration, we have to have a treat this key
			elseif(substr($key,-8) == 'dateyear'){
				$key = substr($key, 0, -9);
				if($_POST[$key.'_datemonth'] != '-month-'){
					$value .= '-'.$_POST[$key.'_datemonth'];
					if($_POST[$key.'_dateday'] != '-day-'){
						//si la date rentrée n'est pas valide (par exemple un 30 février)
						if(!checkdate($_POST[$key.'_datemonth'],$_POST[$key.'_dateday'],$_POST[$key.'_dateyear'])){
							$errortemp = get_string('incorrectdate','sharedresource');
							$value .= '-'.$_POST[$key.'_dateday'];
						} else {
							$value .= '-'.$_POST[$key.'_dateday'];
						}
					} else {
						$value .= '-01';
					}
				} else {
					$value .= '-01-01';
				}
				$value =  mktime(0, 0, 0, substr($value,5,2),  substr($value,8,2), substr($value,0,4));
			}
			$Position = '';
			$Occur = '';
			while(strlen($key) != 0){
				if(strlen($Position) != 0 && strlen($Occur) != 0){
					$Position .= '_';
					$Occur .= '_';
				}
				for($i = 0 ; $i < stripos($key, 'n') ; $i++){
					$Position .= $key[$i];
				}
				$temp = '';
				if(stripos($key, '_') != FALSE){
					for($i = stripos($key, 'n') + 1 ; $i < stripos($key, '_') ; $i++){
						$temp .= $key[$i];
					}
					$Occur .= $temp-1;
					$key = substr(strstr($key,'_'), 1);
				} else {
					for($i = stripos($key, 'n') + 1 ; $i < strlen($key) ; $i++){
						$temp .= $key[$i];
					}
					$Occur .= $temp - 1;
					$key = '';
				}
			}
			//in case of a keyword element, we have to check there is only one keyword, with no punctuation
			if($Position == $keywordnum->name){
				if (preg_match('/[[,;:.\/\\]]/', $value)) {
					$errortemp .= get_string('keywordpunct','sharedresource');
				}
				if (preg_match('/[[:space:]]/', $value)) {
					$errortemp .= get_string('onekeyword','sharedresource');
				}
			}
			//in case of a taxon path, we have to treat the result and divide it into three field : source, id and entry
			if($mtdstandard->METADATATREE[$Position]['name'] == $taxumarray['main']){
				$display .= '<tr><td align="center"><strong>'.$mtdstandard->METADATATREE[$taxumarray['source']]['name'].'</strong></td>';
				$display .= '<td align="center"><strong>';
				$source = $taxumarray['source'];
				$sourcelength = strlen($source);
				$source .= ':'.$Occur;
				while(strlen($source) < (2 * $sourcelength) + 1){
					$source .= '_0';
				}
				$display .= $source.'</strong></td><td align="center">';
				$display .= substr($value, 0, stripos($value, ':')).'</td></tr>';
				$sharedresource_entry->add_element($source, substr($value, 0, stripos($value, ':')), $_POST['pluginchoice']);
				$value = substr($value, stripos($value, ':') + 1);
				$display .= '<tr><td align="center"><strong>'.$mtdstandard->METADATATREE[$taxumarray['id']]['name'].'</strong></td>';
				$display .= '<td align="center"><strong>';
				$id = $taxumarray['id'];
				$idlength = strlen($id);
				$id .= ':'.$Occur;
				while(strlen($id) < (2 * $idlength)+1){
					$id .= '_0';
				}
				$display .= $id.'</strong></td><td align="center">';
				$display .= substr($value,0,stripos($value,':')).'</td></tr>';
				$sharedresource_entry->add_element($id, substr($value, 0, stripos($value,':')), $_POST['pluginchoice']);
				$value = substr($value, stripos($value, ':') + 1);
				$display .= '<tr><td align="center"><strong>'.$mtdstandard->METADATATREE[$taxumarray['entry']]['name'].'</strong></td>';
				$display .= '<td align="center"><strong>';
				$entry = $taxumarray['entry'];
				$entrylength = strlen($entry);
				$entry .= ':'.$Occur;
				while(strlen($entry) < (2 * $entrylength) + 1){
					$entry .= '_0';
				}
				$display .= $entry.'</strong></td><td align="center">';
				$display .= $value.'</td></tr>';
				$sharedresource_entry->add_element($entry, $value, $_POST['pluginchoice']);
				$value = '';
			}
			$key2= $Position.':'.$Occur;
			if($errortemp != ''){
				$error[$key2] = $errortemp;
			}
			if($value != ''){
				$name = $mtdstandard->METADATATREE[$Position]['name'];
				$display .= '<tr><td align="center"><strong>'.$name.'</strong></td>';
				$display .= '<td align="center"><strong>'.$key2. '</strong></td>';
				$display .= '<td align="center">'.$value.'</td></tr>';
				$sharedresource_entry->add_element($key2, $value, $_POST['pluginchoice']);
			}
		}
	}
	$display .= "</table>";
	$result['display'] = $display;
	$result['error'] = $error;
	return $result;
}

function clean_string_key($value){
	$value = str_replace(' ', '_', $value);
	$value = str_replace('\'', '_', $value);
	$value = str_replace('/', '_', $value);
	$value = str_replace("'", '_', $value);
	$value = str_replace('é', 'e', $value);
	$value = str_replace('è', 'e', $value);
	$value = str_replace('ë', 'e', $value);
	$value = str_replace('î', 'i', $value);
	$value = str_replace('û', 'u', $value);
	$value = str_replace('ô', 'o', $value);
	$value = str_replace('à', 'a', $value);
	$value = str_replace('â', 'a', $value);
	return $value;
}

?>