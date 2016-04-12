<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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

    public $element;
    public $namespace;
    public $value;
    public $entry_id;

    /**
     * Constructor for the sharedresource_metadata class
     */
    function __construct($entry_id, $element, $value, $namespace = '') {
        $this->entry_id = $entry_id;
        $this->element = $element;
        $this->namespace = $namespace;
        $this->value = $value;
    }

    function add_instance() {
        global $DB;

        $conditions = array('entry_id' => $this->entry_id, 'element' => $this->element, 'namespace' => $this->namespace);
        if ($oldentry = $DB->get_record('sharedresource_metadata', $conditions)) {
            $this->id = $oldentry->id;
            return $DB->update_record('sharedresource_metadata', $this);
        }
        return $DB->insert_record('sharedresource_metadata', $this);
    }
}
