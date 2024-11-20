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
 * Define all the backup steps that will be used by the backup_sharedresource_activity_task
 *
 * @package     mod_sharedresource
 * @subpackage backup-moodle2
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/*
 * phpcs:disable moodle.Commenting.ValidTags.Invalid
 */

/**
 * Define the complete sharedresource structure for backup, with file and id annotations
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class backup_sharedresource_activity_structure_step extends backup_activity_structure_step {

    /**
     * Standard defines.
     */
    protected function define_structure() {

        // Define each element separated.

        $sharedresource = new backup_nested_element('sharedresource', ['id'], [
           'name', 'type', 'identifier', 'intro', 'introformat', 'alltext', 'popup', 'options', 'timemodified']);

        if (!empty($CFG->sharedresource_backup_index)) {
            $entry = new backup_nested_element('sharedresource_entry', ['id'], ['title', 'type', 'mimetype',
            'identifier', 'remoteid', 'file', 'url', 'lang', 'description', 'keywords', 'timemodified', 'provider',
            'isvalid', 'displayed', 'scoreview', 'scorelike', 'score',
            ]);

            $metadata = new backup_nested_element('metadata');

            $datum = new backup_nested_element('datum', ['entryid', 'element', 'namespace', 'value']);

            $metadata->add_child($datum);
            $entry->add_child($metadata);
            $sharedresource->add_child($entry);
        }

        // Define sources.

        $sharedresource->set_source_table('sharedresource', ['id' => backup::VAR_ACTIVITYID]);
        if (!empty($CFG->sharedresource_backup_index)) {
            $entry->set_source_table('sharedresource_entry', ['id' => backup::VAR_ACTIVITYID]);
            $datum->set_source_table('sharedresource_metadata', ['entryid' => backup::VAR_PARENTID]);
        }

        // Define id annotations.

        // Define file annotations.

        $sharedresource->annotate_files('mod_sharedresource', 'intro', null); // This file area hasn't itemid.

        // Return the root element (sharedresource), wrapped into standard activity structure.
        return $this->prepare_activity_structure($sharedresource);
    }
}
