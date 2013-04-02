
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


class numeric_search_widget extends search_widget{

    /**
    * Constructor for the search_widget class
    */
    function numeric_search_widget($pluginchoice, $id, $label, $type) {
    	parent::search_widget($pluginchoice, $id, $label, $type);
    }

	/**
    * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
    */
    function print_search_widget() {
    	echo $OUTPUT;

		$lowername = strtolower($this->label);
		$widgetname = get_string(str_replace(' ', '', $lowername), 'sharedresource');

		echo '<table class="widget" width="100%"><tr><td class="header" width="2px"></td>';
		echo '<td class="header">'.$widgetname.'</td>';
		echo '<td class="header" align="right">'.$OUTPUT->help_icon('numericsearch', 'sharedresource', false).'</td></tr>';
		echo '<tr><td colspan="3">';
		echo '<select name="'.$this->label.'_symbol">';
		echo '<option selected value="basicvalue"> </option>';
		echo '<option value="=">=</option>';
		echo '<option value="<>">?</option>';
		echo '<option value="<"><</option>';
		echo '<option value=">">></option>';
		echo '<option value="<=">=</option>';
		echo '<option value=">=">=</option>';
		echo '</select>';
		echo '<input type="text" name="'.$this->label.'"/>';
		echo '</td></tr></table>';
    }
	
	// catchs a value in session from CGI input
	function catch_value(&$searchfields){
		global $SESSION;

		if (!isset($SESSION->searchbag)){
			$SESSION->searchbag = new StdClass();
		}

		$paramkey = str_replace(' ', '_', $this->label);
		$searchfields[$this->id] = @$SESSION->searchbag->$paramkey;
		if(!empty($_GET[$paramkey])){
			$searchfields[$this->id] = $_GET[$paramkey.'_symbol'].':'.$_GET[$paramkey];
			$SESSION->searchbag->$paramkey = $_GET[$paramkey.'_symbol'].':'.$_GET[$paramkey];
		}
	}
}
?>