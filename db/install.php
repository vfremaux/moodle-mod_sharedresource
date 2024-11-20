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
 * Post-install code for the sharedresource module.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/**
 * on the install we still need to index the text description
 * which the install.xml syntax does not let us do in a database
 * dependent fashion
 */
function xmldb_sharedresource_install() {
    global $CFG, $DB;

    $result = true;

    $dbman = $DB->get_manager();

    if (preg_match('/^postgres/', $CFG->dbtype)) {
        $idxfield = 'description';
    } else {
        $idxfield = 'description(250)';
    }

    $table = new xmldb_table('sharedresource_entry');
    $index = new xmldb_index('description');

    $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, [$idxfield]);

    if (!$dbman->index_exists($table, $index)) {
        $dbman->add_index($table, $index, false, false);
    }

    return $result;
}
