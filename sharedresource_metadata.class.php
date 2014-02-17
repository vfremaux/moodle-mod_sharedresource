<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */


/**
* sharedresource_metadata defines a sharedresource_metadata element
*
* This class provides all the functionality for a sharedresource_metadata
* You dont really need to be here, as this is managed through the 
* sharedresource_entry object.
*/
class sharedresource_metadata {

    var $element;
    var $namespace;
    var $value;
    var $entry_id;

    /**
    * Constructor for the sharedresource_metadata class
    */
    function sharedresource_metadata($entry_id, $element, $value, $namespace = '') {
        $this->entry_id = $entry_id;
        $this->element = $element;
        $this->namespace = $namespace;
        $this->value = $value;
    }

    function add_instance() {
        global $DB;

		if ($oldentry = $DB->get_record('sharedresource_metadata', array('entry_id' => $this->entry_id, 'element' => $this->element, 'namespace' => $this->namespace))){
			$this->id = $oldentry->id;
			return $DB->update_record('sharedresource_metadata', $this);
		}
        return $DB->insert_record('sharedresource_metadata', $this);
    }
}
?>