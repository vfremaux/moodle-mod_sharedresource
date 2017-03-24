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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_sharedresource_activity_task
 */

/**
 * Structure step to restore one sharedresource activity
 */
class restore_sharedresource_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {
        global $CFG;

        $paths = array();

        $paths[] = new restore_path_element('sharedresource', '/activity/sharedresource');
        if ($CFG->sharedresource_restore_index) {
            $paths[] = new restore_path_element('sharedresourceentry', '/activity/sharedresource/entry');
            $paths[] = new restore_path_element('datum', '/activity/sharedresource/metadata/datum');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_sharedresource($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record('sharedresource', $data);
        $this->apply_activity_instance($newitemid);
    }

    protected function process_sharedresourceentry($data) {
        global $DB, $CFG;

        $data = (object)$data;
        $oldid = $data->id;

        if ($oldres = $DB->get_record('sharedresource_entry', array('identifier' => $data->identifier))) {
            if (!empty($CFG->sharedresource_freeze_index)) {
                $newid = $DB->update_record('sharedresource_entry', $data);
                $this->set_mapping('sharedresource_entry', $oldid, $newid);
            }
        } else {
            $newid = $DB->insert_record('sharedresource_entry', $data);
            $this->set_mapping('sharedresource_entry', $oldid, $newid);
        }
    }

    protected function process_datum($data) {
        global $DB, $CFG;

        $data = (object)$data;
        $oldid = $data->id;
        $data->entry_id = $this->get_mappingid('sharedresource_entry', $data->entry_id);

        if ($oldres = $DB->get_record('sharedresource_metadata', array('entry_id' => $data->entry_id, 'namespace' => $data->namespace, 'element' => $data->element))) {
            if ($CFG->sharedresource_freeze_index) {
                $newid = $DB->update_record('sharedresource_metadata', $data);
            }
        } else {
            $newid = $DB->insert_record('sharedresource_metadata', $data);
        }
    }
    
    public function after_execute() {
        $courseid = $this->get_courseid();
        rebuild_course_cache($courseid, true);
    }
}
