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
 * @author  Piers Harding  piers@catalyst.net.nz
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 * @category mod
 */

function xmldb_sharedresource_upgrade($oldversion = 0) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    $return = true;

    if ($oldversion < 2013032600) {

        // Define field scoreview to be added to sharedresource_entry
        $table = new xmldb_table('sharedresource_entry');

        $field = new xmldb_field('context', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '1', 'isvalid');

        // Conditionally launch add field scoreview
        if (!$dbman->field_exists($table, $field)) {
            $return = $return && $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('scoreview', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0', 'context');

        // Conditionally launch add field scoreview
        if (!$dbman->field_exists($table, $field)) {
            $return = $return && $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('scorelike', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0', 'scoreview');

        // Conditionally launch add field scoreview
        if (!$dbman->field_exists($table, $field)) {
            $return = $return && $dbman->add_field($table, $field);
        }

        // sharedresource savepoint reached
        upgrade_mod_savepoint(true, 2013032600, 'sharedresource');
    }

    return $return;
}
