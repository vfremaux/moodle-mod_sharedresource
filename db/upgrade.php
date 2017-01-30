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
 * @subpackage mod_sharedresource
 * @category mod
 */

// This file keeps track of upgrades to 
// the resource module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_sharedresource_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

	$return = true;

/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

/// if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
///     $result = result of "/lib/ddllib.php" function calls
/// }

/*
    if($result && $oldversion < 2012010109){
    	// force conversion of all keywords to metadata
    	if ($sharedresources_entries = $DB->get_records('sharedresource_entry')){
    		require_once($CFG->dirroot.'/mod/sharedresource/sharedresouce_entry.class.php');
    		foreach($sharedresources_entries as $se){
    			$sharedresource_entry = sharedresource_entry::read($se->identifier);
    			$sharedresource_entry->after_update();
    		}
    	}
    }*/

//===== 1.9.0 upgrade line ======//

	if ($oldversion < 2013030800) {
	}
    
//===== 2.x upgrade line ======//

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

?>