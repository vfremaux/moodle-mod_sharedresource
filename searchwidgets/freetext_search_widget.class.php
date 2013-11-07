
<?php
/**
 *
 * @author  Valery Fremaux
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package sharedresource
 *
 */
require_once $CFG->dirroot.'/mod/sharedresource/metadatalib.php';
require_once $CFG->dirroot.'/mod/sharedresource/search_widget.class.php';

/**
* search_widget defines a widget element for the search engine of metadata.
*/


class freetext_search_widget extends search_widget{

    /**
    * Constructor for the search_widget class
    */
    function freetext_search_widget($pluginchoice, $id, $label, $type) {
    	parent::search_widget($pluginchoice, $id, $label, $type);
    }

	/**
    * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
    */
    function print_search_widget($layout, $value = '') {
    	global $OUTPUT;

		$lowername = strtolower($this->label);
		$widgetname = get_string(str_replace(' ', '', $lowername), 'sharedresource');
		
		if (!empty($value)){
			preg_match('/^([^:]+):(.*)/', $value, $matches);
			$operator = $matches[1];
			$value = $matches[2];
		} else {
			$operator = '';
			$value = '';
		}
		
		$includesselected = ($operator == 'includes') ? 'selected="selected"' : '' ;
		$equalsselected = ($operator == 'equals') ? 'selected="selected"' : '' ;
		$beginswithselected = ($operator == 'beginswith') ? 'selected="selected"' : '' ;
		$endswithselected = ($operator == 'endswith') ? 'selected="selected"' : '' ;

		echo $OUTPUT->box($widgetname.' '.$OUTPUT->help_icon('textsearch', 'sharedresource', false), 'header');
		echo $OUTPUT->box_start('content');
		echo '<select name="'.$this->label.'_option">';
		echo "<option value=\"includes\" $includesselected >".get_string('contains', 'sharedresource').'</option>';
		echo "<option value=\"equals\" $equalsselected >".get_string('equalto', 'sharedresource').'</option>';
		echo "<option value=\"beginswith\" $beginswithselected >".get_string('startswith', 'sharedresource').'</option>';
		echo "<option value=\"endswith\" $endswithselected >".get_string('endswith', 'sharedresource').'</option>';
		echo '</select>';
		echo '<input type="text" name="'.$this->label.'" value="'.$value.'"/>';
		echo $OUTPUT->box_end();
    }
	
	/** 
	* catchs a value in session from CGI input
	* @return true if filter configuration has changed
	*/
	function catch_value(&$searchfields){
		global $SESSION;
		
		if (!isset($SESSION->searchbag)){
			$SESSION->searchbag = new StdClass();
		}
		
		$paramkey = str_replace(' ', '_', $this->label);
		$searchfields[$this->id] = @$SESSION->searchbag->$paramkey;
		if(isset($_GET[$paramkey])){
			if ($_GET[$paramkey] != ''){
				$searchfields[$this->id] = $_GET[$paramkey.'_option'].':'.$_GET[$paramkey];
				$SESSION->searchbag->$paramkey = $_GET[$paramkey.'_option'].':'.$_GET[$paramkey];
			} else {
				$searchfields[$this->id] = '';
				$SESSION->searchbag->$paramkey = '';
			}
			return true;
		}
		
		return false;
	}
}
?>