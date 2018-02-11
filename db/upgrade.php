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

        // Define field scoreview to be added to sharedresource_entry.
        $table = new xmldb_table('sharedresource_entry');

        $field = new xmldb_field('context', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '1', 'isvalid');

        // Conditionally launch add field scoreview.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('scoreview', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0', 'context');

        // Conditionally launch add field scoreview.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('scorelike', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0', 'scoreview');

        // Conditionally launch add field scoreview
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Sharedresource savepoint reached.
        upgrade_mod_savepoint(true, 2013032600, 'sharedresource');
    }

    if ($oldversion < 2017041701) {
        set_config('schema', $CFG->pluginchoice, 'sharedresource');

        // Sharedresource savepoint reached.
        upgrade_mod_savepoint(true, 2017041701, 'sharedresource');
    }

    if ($oldversion < 2017100800) {
        $table = new xmldb_table('sharedresource_entry');

        $field = new xmldb_field('accessctl', XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'scorelike');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Sharedresource savepoint reached.
        upgrade_mod_savepoint(true, 2017100800, 'sharedresource');
    }

    if ($oldversion < 2017120200) {

        // Define table sharedresource_classif to be created.
        $table = new xmldb_table('sharedresource_classif');

        // Adding fields to table sharedresource_classif.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('tablename', XMLDB_TYPE_CHAR, '60', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sqlid', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sqlparent', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sqllabel', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sqlsortorder', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sqlsortorderstart', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sqlrestriction', XMLDB_TYPE_CHAR, '32', null, null, null, null);
        $table->add_field('taxonselection', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('purpose', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table sharedresource_classif.
        $table->add_key('id_classif_pk', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for sharedresource_classif.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        sharedresource_transfer_classification_settings();

        // Sharedresource savepoint reached.
        upgrade_mod_savepoint(true, 2017120200, 'sharedresource');
    }

    if ($oldversion < 2017121800) {

        $table = new xmldb_table('sharedresource_classif');

        $field = new xmldb_field('shortname', XMLDB_TYPE_CHAR, 48, null, null, null, null, 'id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Sharedresource savepoint reached.
        upgrade_mod_savepoint(true, 2017121800, 'sharedresource');
    }

    if ($oldversion < 2018011800) {

        $table = new xmldb_table('sharedresource_taxonomy');

        $field = new xmldb_field('idnumber', XMLDB_TYPE_CHAR, 64, null, null, null, null, 'value');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Sharedresource savepoint reached.
        upgrade_mod_savepoint(true, 2018011800, 'sharedresource');
    }

    if ($oldversion < 2018011801) {

        $table = new xmldb_table('sharedresource_metadata');

        $field = new xmldb_field('entry_id');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'entryid', false);
        }

        $table = new xmldb_table('sharedresource_classif');

        $field = new xmldb_field('accessctl', XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'purpose');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('sharedresource_entry');

        $field = new xmldb_field('accessctl', XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'scorelike');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('userfield');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('userfieldvalues');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Sharedresource savepoint reached.
        upgrade_mod_savepoint(true, 2018011801, 'sharedresource');
    }

    return $return;
}

function sharedresource_transfer_classification_settings() {
    global $DB;

    $config = get_config('sharedresource');

    if (!empty($config->classifarray)) {
        $classifications = unserialize($config->classifarray);

        if (!empty($classifications)) {
            foreach ($classifications as $tablename => $classif) {
                $record = new StdClass;
                $record->purpose = '';
                $record->name = $classif['classname'];
                $record->enabled = $classif['select'];
                $record->tablename = $tablename;
                $record->sqlid = $classif['id'];
                $record->sqlparent = $classif['parent'];
                $record->sqllabel = $classif['label'];
                $record->sqlsortorder = $classif['ordering'];
                $record->sqlsortorderstart = $classif['orderingmin'];
                $record->sqlrestriction = $classif['restriction'];
                $record->taxonselection = implode(',', $classif['taxonselect']);

                $DB->insert_record('sharedresource_classif', $record);
            }
        }
        // Not yet.
        // set_config('classifarray', null, 'sharedresource');
    }
}
