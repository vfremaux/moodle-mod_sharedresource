
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


class select_search_widget extends search_widget{

    /**
    * Constructor for the search_widget class
    */
    function select_search_widget($pluginchoice, $id, $label, $type) {
    	parent::search_widget($pluginchoice, $id, $label, $type);
    }

	/**
    * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
    */
    function print_search_widget($layout, $value = 0) {
    	global $CFG, $OUTPUT;

		$lowername = strtolower($this->label);
		$widgetname = get_string(str_replace(' ', '', $lowername), 'sharedresource');

		require_once $CFG->dirroot.'/mod/sharedresource/plugins/'.$this->pluginchoice.'/plugin.class.php';
		$object = 'sharedresource_plugin_'.$this->pluginchoice;
		$mtdstandard = new $object;
		echo '<table class="widget"><tr><td class="widget-label">'.$widgetname.'</td><td class="widget-input">';
		echo $OUTPUT->help_icon('selectsearch', 'sharedresource', false);
		echo '</td><td>';
		echo '<select name="'.$this->label.'">';
		echo '<option selected value="defaultvalue"> </option>';
		foreach($mtdstandard->METADATATREE[$this->id]['values'] as $optvalue) {
			$selected = ($value == $optvalue) ? 'selected="selected"' : '' ;
			echo "<option value=\"$optvalue\" $selected >".get_string(clean_string_key($optvalue), 'sharedresource').'</option>';
		}
		echo '</select></td></tr></table>';
    }
	
	// catchs a value in session from CGI input
	function catch_value(&$searchfields){
		global $SESSION;

		if (!isset($SESSION->searchbag)){
			$SESSION->searchbag = new StdClass();
		}
		
		$paramkey = str_replace(' ', '_', $this->label);
		$searchfields[$this->id] = @$SESSION->searchbag->$paramkey;
		if(isset($_GET[$paramkey]) && $_GET[$paramkey] != 'defaultvalue'){
			$searchfields[$this->id] = $_GET[$paramkey];
			$SESSION->searchbag->$paramkey = $_GET[$paramkey];
		}
	}
}
?>