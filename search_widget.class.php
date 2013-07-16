
<?php
/**
 *
 * @author  Frédéric Guillou
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package sharedresource
 *
 */
require_once $CFG->dirroot.'/mod/sharedresource/metadatalib.php';

/**
* search_widget defines a widget element for the search engine of metadata.
*/


abstract class search_widget {

	var $pluginchoice; // the plugin chosen for the metadata form (lom or lomfr for instance)
    var $id; // the field numero of the metadata tree in the plugin chosen by the admin
	var $label; // the name of the node which have this id.
    var $type; // the type of the widget. There are 6 types : numeric, freetext, select, selectmultiple, date and treeselect.

    /**
    * Constructor for the search_widget class
    */
    function search_widget($pluginchoice, $id, $label, $type) {
		$this->pluginchoice = $pluginchoice;
        $this->id = $id;
        $this->label = $label;
        $this->type = $type;
    }

	/**
    * print widget implementation for each widget style.
    * @param string $layout gives some indication about the surrounding layout and what glue is
    * to be added.
    * @param mixed $value the input or current value
    */
    abstract function print_search_widget($layout, $value = 0);

	/**
	* implements a value catcher from CGI input or retreives the session stored current value
	* @param array $searchfields a colelctor array that traverse all catch_value calls to collect field value for search query.
	*/
    abstract function catch_value(&$searchfields);
	
}
