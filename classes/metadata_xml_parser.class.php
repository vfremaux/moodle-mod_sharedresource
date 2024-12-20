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
 * Special class for parsing metadata XML documents
 *
 * @package     mod_sharedresource
 * @author      Frederic Guillou
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
namespace mod_sharedresource;

/**
 * Base XML Parser class
 */
abstract class metadata_xml_parser {

    /**
     *
     */
    abstract public function add_identifier(&$metadata, $catalog, $identifier, $entryid);

    /**
     * Get a value in the tree
     * @param string $path
     */
    public function get_metadata_value($path) {
        foreach ($this->metadata as $id => $elem) {
            if ($this->metadata[$id]->element == $path) {
                return $this->metadata[$id]->value;
            }
        }
    }
}
