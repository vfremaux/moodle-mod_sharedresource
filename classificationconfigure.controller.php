<?php

	if (!defined('MOODLE_INTERNAL')) die('You cannot use this script this way');

	if($mode == 'delete' && !empty($target)){
		// when a restriction is deleted
		if(substr($target, 0, 8) == 'restrict' && !array_key_exists($target, $classifarray)){
			$classifarray[substr($target, 8)]['restriction'] = '';
		// when a classification is deleted
		} else {
			$plugins = get_list_of_plugins('mod/sharedresource/plugins');
			foreach($plugins as $num => $plugin){
				require_once 'plugins/'.$plugin.'/plugin.class.php';
				$object = 'sharedresource_plugin_'.$plugin;
				$norme = new $object;
				$numclassification = $norme->getClassification();
				$elementinstances = $DB->get_records_sql('SELECT * from {sharedresource_metadata} WHERE namespace = "'.$plugin.'" AND element LIKE "'.$numclassification.'%" and value = "'.$target.'"' );
				if(strpos($numclassification,'_') == FALSE){
					$classifdepth = 1;
				} else {
					$classifdepth = substr_count($numclassification,'_') + 1;
				}
				$tabinstance = array();
				// tabinstances contains the number of all ressources which have a taxon from the deleted classification
				if(!empty($elementinstances)){
					foreach($elementinstances as $key => $value){
						$chaine = '';
						$temp = $classifdepth;
						$occur = substr($value->element,strpos($value->element,':') + 1);
						while($temp > 0){
							if($chaine!=''){
								$chaine .= '_';
							}
							$chaine .= substr($occur, 0, strpos($occur,'_'));
							$occur = substr($occur, strpos($occur,'_') + 1);
							$temp--;
						}
						if(!in_array($chaine,$tabinstance)){
							array_push($tabinstance,$chaine);
						}
					}
					foreach($tabinstance as $key => $value){ // Metadata which contain elements of this classification are deleted
						$elementinstances2 = $DB->delete_records_select('sharedresource_metadata', "namespace = '{$plugin}' and element LIKE '{$numclassification}%:{$value}%'" );
					}
				}
			
			}
			unset($classifarray[$target]);
		}
		set_config('classifarray',serialize($classifarray));
	}
	elseif($mode == 'add'){
		if(!isset($table) || $table == ''){
			$recordclassif = false;
			$erroradd .= get_string('missingnametable', 'sharedresource');
		} else {
           
			$metatables = $DB->get_tables();
			$metatables = array_flip($metatables);
			$metatables = array_change_key_case($metatables, CASE_LOWER);
			if(strstr($table,$CFG->prefix) == false){
				$tablename =$table ;//$CFG->prefix.$table;
			} else {
				$tablename = $table;
				$table = substr($table,strlen($CFG->prefix));
			}
			if (!array_key_exists($tablename,  $metatables)) {
				$recordclassif = false;
				$erroradd .= get_string('missingtable', 'sharedresource');
			}
		}
		if($recordclassif){
			$listfield = array();
			foreach($DB->get_columns($tablename) as $key => $value){
				array_push($listfield,$value->name);
			}
			if(!isset($id) || $id == ''){
				$recordclassif = false;
				$erroradd .= get_string('missingnameid','sharedresource');
			}
			elseif(!in_array($id,$listfield)){
				$recordclassif = false;
				$erroradd .= get_string('missingid','sharedresource');
			}
			if(!isset($parent) || $parent == ''){
				$recordclassif = false;
				$erroradd .= get_string('missingnameid','sharedresource');
			}
			elseif(!in_array($parent,$listfield)){
				$recordclassif = false;
				$erroradd .= get_string('missingparent','sharedresource');
			}
			if(!isset($label) || $label == ''){
				$recordclassif = false;
				$erroradd .= get_string('missingnamelabel','sharedresource');
			}
			elseif(!in_array($label,$listfield)){
				$recordclassif = false;
				$erroradd .= get_string('missinglabel','sharedresource');
			}
			if(isset($ordering) && !in_array($ordering, $listfield)){
				$recordclassif = false;
				$erroradd .= get_string('missingordering','sharedresource');
			}
		}
		if($recordclassif){
			$newclassif['id'] = $id;
			$newclassif['classname'] = $classname;
			$newclassif['parent'] = $parent;
			$newclassif['label'] = $label;
			$newclassif['ordering'] = $ordering;
			$newclassif['orderingmin'] = $orderingmin;
			$newclassif['select'] = 0;
			$newclassif['restriction'] = '';
			$newclassif['taxonselect'] = array();
			if(!get_config(null, 'classifarray')){
				$classifarray[$table] = $newclassif;
			} else {
				$classifarray = unserialize($CFG->classifarray);
				$num = count($classifarray) + 1;
				$classifarray[$table] = $newclassif;
			}
			set_config('classifarray',serialize($classifarray));
		}
	}
	elseif($mode == 'select'){
		foreach($classifarray as $classif => $value){
			$classifarray[$classif]['select'] = optional_param($classif, 0, PARAM_BOOL);
		}
		set_config('classifarray', serialize($classifarray));
	}
?>